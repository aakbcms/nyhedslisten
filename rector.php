<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Rector\Class_\CommandDescriptionToPropertyRector;
use Rector\Symfony\Rector\Class_\CommandPropertyToAttributeRector;
use Rector\Symfony\Rector\ClassMethod\CommandConstantReturnCodeRector;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    $rectorConfig->rule(CommandConstantReturnCodeRector::class);
    $rectorConfig->rule(CommandDescriptionToPropertyRector::class);
    $rectorConfig->rule(CommandPropertyToAttributeRector::class);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        SymfonySetList::SYMFONY_62,

        DoctrineSetList::DOCTRINE_25,
        SensiolabsSetList::FRAMEWORK_EXTRA_61,
    ]);
};
