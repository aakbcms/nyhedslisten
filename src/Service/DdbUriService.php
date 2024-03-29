<?php

/**
 * @file
 * Build URI for a given data well post id.
 */

namespace App\Service;

/**
 * Class DdbUriService.
 */
class DdbUriService
{
    // Example url https://www.aakb.dk/ting/object/870970-basis:47791596
    private const URL_PATTERN = '%s/ting/object/%s';

    /**
     * DdbUriService constructor.
     *
     * @param string $basePath
     *   DDB CMS base URL from configuration
     */
    public function __construct(
        private readonly string $basePath
    ) {}

    /**
     * Get generated URL for a given PID.
     *
     * @param string $pid
     *   Data well post id
     *
     * @return string
     *   The generated URL
     */
    public function getUri(string $pid): string
    {
        return sprintf(self::URL_PATTERN, $this->basePath, $pid);
    }
}
