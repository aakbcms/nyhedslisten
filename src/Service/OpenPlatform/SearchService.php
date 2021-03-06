<?php

/**
 * @file
 * Search data well.
 */

namespace App\Service\OpenPlatform;

use App\Exception\PlatformAuthException;
use App\Utils\ArrayMerge;
use App\Utils\Types\IdentifierType;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class SearchService.
 */
class SearchService
{
    private const SEARCH_LIMIT = 50;

    private $authenticationService;
    private $client;

    private $searchFields;
    private $searchURL;

    private $profile;

    /**
     * SearchService constructor.
     *
     * @param parameterBagInterface $params
     *   Access to environment variables
     * @param authenticationService $authenticationService
     *   The Open Platform authentication service
     * @param ClientInterface $httpClient
     *   Guzzle Client
     */
    public function __construct(ParameterBagInterface $params, AuthenticationService $authenticationService, ClientInterface $httpClient)
    {
        $this->authenticationService = $authenticationService;
        $this->client = $httpClient;

        $this->searchURL = $params->get('openPlatform.search.url');
        $this->searchFields = explode(',', $params->get('openPlatform.search.fields'));

        $this->profile = $params->get('datawell.vendor.profile');
    }

    /**
     * Query the data well through the open platform.
     *
     * @param string $query
     *   The CQL query to perform
     *
     * @return array
     *   The results returned
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws PlatformAuthException
     */
    public function query(string $query): array
    {
        return $this->recursiveQuery($query);
    }

    /**
     * Search by identifier of type.
     *
     * @param string $identifier
     * @param string $type
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws PlatformAuthException
     */
    public function searchByIdentifier(string $identifier, string $type): array
    {
        switch ($type) {
            case IdentifierType::PID:
                // If this is a search after a pid simply search for it and not in the search index.
                $query = 'rec.id='.$identifier;
                break;

            case IdentifierType::ISBN:
                $query = 'term.isbn='.$identifier;
                break;

            case IdentifierType::FAUST:
                $query = 'dkcclterm.is='.$identifier;
                break;

            default:
                throw new \InvalidArgumentException('Unknown identifier type: '.$type);
        }

        return $this->query($query);
    }

    /**
     * Recursive search until no more results exists for the query.
     *
     * This is needed as the open platform allows an max limit of 50 elements, so
     * if more results exists this calls it self to get all results.
     *
     * @param string $query
     *   The cql-query to execute against OpenPlatform
     * @param int $offset
     *   The offset to start getting results
     * @param array $results
     *   The current results array
     *
     * @return array
     *   The results currently found. If recursion is completed all the results
     *
     * @throws GuzzleException
     * @throws PlatformAuthException
     * @throws InvalidArgumentException
     */
    private function recursiveQuery(string $query, int $offset = 0, array &$results = []): array
    {
        $token = $this->authenticationService->getAccessToken();
        $response = $this->client->request('POST', $this->searchURL, [
            RequestOptions::JSON => [
                'fields' => $this->searchFields,
                'access_token' => $token,
                'pretty' => false,
                'timings' => false,
                'q' => $query,
                'offset' => $offset,
                'limit' => $this::SEARCH_LIMIT,
                'profile' => $this->profile,
            ],
        ]);

        $content = $response->getBody()->getContents();
        $json = json_decode($content, true);

        if (isset($json['data']) && !empty($json['data'])) {
            ArrayMerge::mergeArraysByReference($results, $json['data']);
        }

        // If there are more results get the next chunk.
        if (isset($json['hitCount']) && $json['hitCount'] > $offset) {
            $this->recursiveQuery($query, $offset + self::SEARCH_LIMIT, $results);
        }

        return $results;
    }
}
