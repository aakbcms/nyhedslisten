<?php


namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DdbUriService
{
    // Example url https://www.aakb.dk/ting/object/870970-basis:47791596
    private const URL_PATTERN = '%s/ting/object/%s';

    private $basePath;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->basePath = $parameterBag->get('ddbcms.base.url');
    }

    public function getUri(string $pid): string
    {
        return sprintf(self::URL_PATTERN, $this->basePath, $pid);
    }
}
