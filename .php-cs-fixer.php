<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'        => true,
        '@PSR12'          => true,
        '@PSR12:risky'    => true,
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
