<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Rector\Config\RectorConfig;

$root = dirname(__DIR__, 2);

return RectorConfig::configure()
    ->withPaths([
        $root . '/src',
        $root . '/tests',
        $root . '/tools',
    ])
    ->withSkip([
        $root . '/tools/ecs/vendor',
        $root . '/tools/rector/vendor',

        // This one does not really match with the project's code style
        SimplifyUselessVariableRector::class,
    ])
    ->withPhpSets()
    ->withImportNames(importNames: false, removeUnusedImports: true);
