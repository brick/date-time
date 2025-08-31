<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(__DIR__ . '/vendor/brick/coding-standard/ecs.php');

    $libRootPath = realpath(__DIR__ . '/../..');

    $ecsConfig->paths(
        [
            $libRootPath . '/src',
            $libRootPath . '/tests',
            __FILE__,
        ],
    );

    // PHP-CS-Fixer
    $ecsConfig->skip([
        // Allows tree building method chaining syntax for better readability
        MethodChainingIndentationFixer::class => [$libRootPath . '/src/Parser/IsoParsers.php'],

        // Allows microtime() to be called from class namespace so that it can be overridden
        // and its return value mocked in SystemClockTest
        ReferenceUsedNamesOnlySniff::class => [$libRootPath . '/src/Clock/SystemClock.php'],

        // Allows alignment in test providers
        DuplicateSpacesSniff::class => [$libRootPath . '/tests'],
    ]);
};
