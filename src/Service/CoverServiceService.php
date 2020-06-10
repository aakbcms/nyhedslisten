<?php

/**
 * @file
 * Service to get covers from DDB cover service.
 */

namespace App\Service;

use App\Service\OpenPlatform\AuthenticationService;
use CoverService\Api\CoverApi;
use CoverService\Configuration;
use GuzzleHttp\Client;

/**
 * Class CoverServiceService.
 */
class CoverServiceService
{
    private $authenticationService;
    private $bindCoverServiceUrl;

    /**
     * CoverServiceService constructor.
     *
     * @param AuthenticationService $authenticationService
     * @param string $bindCoverServiceUrl
     */
    public function __construct(AuthenticationService $authenticationService, string $bindCoverServiceUrl)
    {
        // This reuse of the authentication service assumes that the token is an agency token (auth with an agency).
        $this->authenticationService = $authenticationService;
        $this->bindCoverServiceUrl = $bindCoverServiceUrl;
    }

    /**
     * Get covers for the identifiers given
     *
     * @param array $identifiers
     *   Material identifiers (PIDs).
     *
     * @return array
     *   URLs for covers for the ones found (indexed by pid)
     */
    public function getCovers(array $identifiers)
    {
        $covers = [];

        try {
            $config = $this->getConfig();
            $apiInstance = new CoverApi(
                new Client(),
                $config
            );
            $retrieved = $apiInstance->getCoverCollection('pid', $identifiers, ['original', 'small']);
        } catch (\Exception $e) {
            return $covers;
        }

        foreach ($retrieved as $cover) {
            $source_url = $source_fallback_url = null;
            $image_urls = $cover->getImageUrls();
            foreach ($image_urls as $image_url) {
                switch ($image_url->getSize()) {
                    case 'original':
                        $source_fallback_url = $image_url->getUrl();
                        break;

                    case 'small':
                        $source_url = $image_url->getUrl();
                        break;
                }
            }

            if (is_null($source_url)) {
                // The service will return null for a given image size if there is no
                // image that is large enough to scale down to that size. So we fallback
                // to original image from the service.
                $source_url = $source_fallback_url;
            }

            // Return the path to the cover.
            $covers[$cover->getId()] = $source_url;
        }

        return $covers;
    }

    /**
     * Get configuration for the CoverService client.
     *
     * @return Configuration
     *   The configuration,.
     *
     * @throws \App\Exception\PlatformAuthException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getConfig()
    {
        $config = Configuration::getDefaultConfiguration();

        // Get access token for the library.
        $token = $this->authenticationService->getAccessToken();
        $config->setAccessToken($token);

        $config->setHost($this->bindCoverServiceUrl);

        return $config;
    }
}