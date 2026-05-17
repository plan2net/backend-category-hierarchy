<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
    ])
    ->exclude(['.phpunit.cache']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP82Migration' => true,
        '@PHP82Migration:risky' => true,
        'declare_strict_types' => true,
        'yoda_style' => false,
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder);
