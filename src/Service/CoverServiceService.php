<?php

/**
 * @file
 * Service to get covers from DDB cover service.
 */

namespace App\Service;

use App\Entity\Material;
use App\Exception\PlatformAuthException;
use App\Service\OpenPlatform\AuthenticationService;
use App\Utils\GenericBookCover\BookCover;
use CoverService\Api\CoverApi;
use CoverService\Configuration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;

/**
 * Class CoverServiceService.
 */
class CoverServiceService
{
    /**
     * CoverServiceService constructor.
     */
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly string $coverServiceUrl,
        private readonly string $coverServiceDefaultUrl,
        private readonly string $coverServiceGenerateDomain,
        private readonly string $projectDir
    ) {}

    /**
     * Get covers for the identifiers given.
     *
     * @param array $identifiers
     *   Material identifiers (PIDs)
     *
     * @return array
     *   URLs for covers for the ones found (indexed by pid)
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getCovers(array $identifiers): array
    {
        $covers = [];

        try {
            $config = $this->getConfig();
            $apiInstance = new CoverApi(
                new Client(),
                $config
            );
            $retrieved = $apiInstance->getCoverCollection('pid', $identifiers, ['original', 'small']);
        } catch (\Exception $exception) {
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
     * Default cover image url.
     *
     * @return string
     *   The URL to the default cover
     */
    public function getDefaultCoverUrl(): string
    {
        return $this->coverServiceDefaultUrl;
    }

    /**
     * Generate generic cover for the material.
     *
     * @param material $material
     *   The material to generate cover for
     *
     * @return string
     *   URL to the cover. Will fallback to default cover if generation fails.
     */
    public function getGenericCoverUrl(Material $material): string
    {
        $url = $this->getDefaultCoverUrl();
        $filename = $material->getPid().'.png';
        $file = '/public/covers/'.$filename;
        try {
            $cover = new BookCover();
            $cover->setTitle($material->getTitleFull() ?? '')
                ->setCreators($material->getCreatorFiltered() ?? '')
                ->setPublisher($material->getPublisher() ?? '')
                ->setDatePublished($material->getDate()->format('Y'))
                ->randomizeBackgroundColor()
                ->save($this->projectDir.$file, 350);

            $url = $this->coverServiceGenerateDomain.'/covers/'.$filename;
        } catch (\Exception) {
            // Don't do anything. Will fall back to default cover missing image.
        }

        return $url;
    }

    /**
     * Get configuration for the CoverService client.
     *
     * @return Configuration
     *   The configuration,
     *
     * @throws PlatformAuthException
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function getConfig(): Configuration
    {
        $config = Configuration::getDefaultConfiguration();

        // Get access token for the library.
        $token = $this->authenticationService->getAccessToken();
        $config->setAccessToken($token);

        $config->setHost($this->coverServiceUrl);

        return $config;
    }
}
