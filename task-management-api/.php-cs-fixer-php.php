<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->exclude('bootstrap/cache') // Use exclude instead of notPath
    ->exclude('storage')
    ->exclude('vendor')
    ->in(__DIR__ . '/app')       // Specify the 'app' directory
    ->in(__DIR__ . '/config')    // Specify the 'config' directory
    ->in(__DIR__ . '/database')  // Specify the 'database' directory
    ->in(__DIR__ . '/routes')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        // ... other rules
    ])
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
