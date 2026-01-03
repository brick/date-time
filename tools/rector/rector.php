<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

$root = dirname(__DIR__, 2);

return RectorConfig::configure()
    ->withPaths([
        $root . '/src',
        $root . '/tests',
        $root . '/tools',
    ])
    ->withSkip([
        $root . '/tools/ecs/vendor',
        $root . '/tools/psalm/vendor',
        $root . '/tools/rector/vendor',
    ])
    ->withPhpSets(php82: true)
    ->withSets([
        PHPUnitSetList::PHPUNIT_110,
    ])
    ->withImportNames(importNames: false, removeUnusedImports: true);
