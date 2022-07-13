<?php

/**
 * @file
 * Authentication service with the openplatform.
 */

namespace App\Service\OpenPlatform;

use App\Exception\PlatformAuthException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Class AuthenticationService.
 */
class AuthenticationService
{
    // Used to give the token some grace-period so it will not expire will
    // being used. Currently the token is valid for 30 days. So we set the
    // limit to be 1 day, so it will be refresh before it expires.
    final public const TOKEN_EXPIRE_LIMIT = 86400;
    private string $accessToken = '';

    /**
     * Authentication constructor.
     *
     * @param parameterBagInterface $params
     *   Used to get parameters form the environment
     * @param adapterInterface $cache
     *   Cache to store access token
     * @param loggerInterface $statsLogger
     *   Logger object to send stats to ES
     * @param ClientInterface $guzzleClient
     *  Guzzle Client
     */
    public function __construct(private readonly ParameterBagInterface $params, private readonly CacheInterface $cache, private readonly LoggerInterface $statsLogger, private readonly ClientInterface $guzzleClient)
    {
    }

    /**
     * Get access token.
     *
     * If not in local cache an request to the open platform for a new token will
     * be executed.
     *
     * @param bool $refresh
     *   If TRUE refresh token. Default: FALSE.
     *
     * @return string
     *   The access token
     *
     * @throws PlatformAuthException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAccessToken($refresh = false)
    {
        if (empty($this->accessToken)) {
            $this->accessToken = $this->authenticate($refresh);
        }

        return $this->accessToken;
    }

    /**
     * Authenticate against open platform.
     *
     * @param bool $refresh
     *   If TRUE refresh token. Default: FALSE.
     *
     * @return string
     *   The token if successful else the empty string,
     *
     * @throws PlatformAuthException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function authenticate($refresh = false)
    {
        // Try getting item from cache.
        $item = $this->cache->getItem('openplatform.access_token');

        // Check if the access token is located in local file cache to speed up the
        // process.
        if ($item->isHit() && !$refresh) {
            $this->statsLogger->info('Access token requested', [
                'service' => 'AuthenticationService',
                'cache' => true,
            ]);

            return $item->get();
        } else {
            try {
                $response = $this->guzzleClient->request('POST', $this->params->get('openPlatform.auth.url'), [
                    'form_params' => [
                        'grant_type' => 'password',
                        'username' => '@'.$this->params->get('datawell.vendor.agency'),
                        'password' => '@'.$this->params->get('datawell.vendor.agency'),
                    ],
                    'auth' => [
                        $this->params->get('openPlatform.auth.id'),
                        $this->params->get('openPlatform.auth.secret'),
                    ],
                ]);
            } catch (RequestException $exception) {
                $this->statsLogger->error('Access token not acquired', [
                    'service' => 'AuthenticationService',
                    'cache' => false,
                    'message' => $exception->getMessage(),
                ]);

                throw new PlatformAuthException($exception->getMessage(), $exception->getCode());
            } catch (\Exception $exception) {
                $this->statsLogger->error('Unknown error in acquiring access token', [
                    'service' => 'AuthenticationService',
                    'message' => $exception->getMessage(),
                ]);

                throw new PlatformAuthException($exception->getMessage(), $exception->getCode());
            }

            // Get the content and parse json object as an array.
            $content = $response->getBody()->getContents();
            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $this->statsLogger->info('Access token acquired', [
                'service' => 'AuthenticationService',
                'cache' => false,
            ]);

            // Store access token in local cache.
            $item->expiresAfter($json['expires_in'] - $this::TOKEN_EXPIRE_LIMIT);
            $item->set($json['access_token']);
            $this->cache->save($item);

            return $json['access_token'];
        }
    }
}
