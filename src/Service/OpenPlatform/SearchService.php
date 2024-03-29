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

    private readonly array $searchFields;
    private readonly string $searchURL;

    private readonly string $profile;

    /**
     * SearchService constructor.
     *
     * @param parameterBagInterface $params
     *   Access to environment variables
     * @param authenticationService $authenticationService
     *   The Open Platform authentication service
     * @param ClientInterface $guzzleClient
     *   Guzzle Client
     */
    public function __construct(
        ParameterBagInterface $params,
        private readonly AuthenticationService $authenticationService,
        private readonly ClientInterface $guzzleClient
    ) {
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
    public function query(string $query, $limit = null): array
    {
        return $this->recursiveQuery($query, $limit);
    }

    /**
     * Search by identifier of type.
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     * @throws PlatformAuthException
     */
    public function searchByIdentifier(string $identifier, string $type): array
    {
        $query = match ($type) {
            IdentifierType::PID => 'rec.id='.$identifier,
            IdentifierType::ISBN => 'term.isbn='.$identifier,
            IdentifierType::FAUST => 'dkcclterm.is='.$identifier,
            default => throw new \InvalidArgumentException('Unknown identifier type: '.$type),
        };

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
     * @param ?int $limit
     *   Limit the number of results to get
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
    private function recursiveQuery(string $query, ?int $limit = null, int $offset = 0, array &$results = []): array
    {
        $searchLimit = $limit < ($offset + self::SEARCH_LIMIT) ? self::SEARCH_LIMIT : $limit;

        $token = $this->authenticationService->getAccessToken();
        $response = $this->guzzleClient->request('POST', $this->searchURL, [
            RequestOptions::JSON => [
                'fields' => $this->searchFields,
                'access_token' => $token,
                'pretty' => false,
                'timings' => false,
                'q' => $query,
                'offset' => $offset,
                'limit' => $searchLimit,
                'profile' => $this->profile,
            ],
        ]);

        $content = $response->getBody()->getContents();
        $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (isset($json['data']) && !empty($json['data'])) {
            ArrayMerge::mergeArraysByReference($results, $json['data']);
        }

        // If there are more results get the next chunk.
        if (null === $limit || \count($results) < $limit) {
            if (isset($json['hitCount']) && $json['hitCount'] > $offset) {
                $this->recursiveQuery($query, $limit, $offset + self::SEARCH_LIMIT, $results);
            }
        }

        return $results;
    }
}
