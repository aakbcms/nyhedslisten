<?php

use PhpCsFixerCustomFixers\Fixer\CommentSurroundedBySpacesFixer;
use PhpCsFixerCustomFixers\Fixer\ConstructorEmptyBracesFixer;
use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\NoDuplicatedImportsFixer;
use PhpCsFixerCustomFixers\Fixer\NoImportFromGlobalNamespaceFixer;
use PhpCsFixerCustomFixers\Fixer\StringableInterfaceFixer;

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->registerCustomFixers(new PhpCsFixerCustomFixers\Fixers())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => false,
        'phpdoc_align' => false,
        'no_superfluous_phpdoc_tags' => false,
        'array_syntax' => ['syntax' => 'short'],
        CommentSurroundedBySpacesFixer::name() => true,
        ConstructorEmptyBracesFixer::name() => true,
        MultilinePromotedPropertiesFixer::name() => true,
        NoDuplicatedImportsFixer::name() => true,
        NoImportFromGlobalNamespaceFixer::name() => true,
        StringableInterfaceFixer::name() => true,
    ])
    ->setFinder($finder)
    ;
