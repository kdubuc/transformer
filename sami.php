<?php

require_once __DIR__.'/vendor/autoload.php';

use Sami\Sami;
use Symfony\Component\Finder\Finder;

// Get all PHP files to be documented
$finder = Finder::create()
    ->files()
    ->name('*.php')
    ->in('src')
;

// SAMI options
$options = [
    'title'     => 'API Transformer Docs',
    'build_dir' => __DIR__.'/docs',
    'cache_dir' => __DIR__.'/docs/cache',
];

return new Sami($finder, $options);
