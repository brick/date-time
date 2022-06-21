<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FunctionCommentSniff;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff;
use SlevomatCodingStandard\Sniffs\Whitespaces\DuplicateSpacesSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(__DIR__ . '/vendor/brick/coding-standard/ecs.php');

    $libRootPath = __DIR__ . '/../../';

    $ecsConfig->paths(
        [
            $libRootPath . '/src',
            $libRootPath . '/tests',
            __FILE__,
        ],
    );

    $ecsConfig->indentation('spaces');

    // PHP-CS-Fixer
    $ecsConfig->skip([
        // Allows tree building method chaining syntax for better readability
        MethodChainingIndentationFixer::class => [$libRootPath . '/src/Parser/IsoParsers.php'],

        // Allows microtime() to be called from class namespace so that it can be overridden
        // and its return value mocked in SystemClockTest
        ReferenceUsedNamesOnlySniff::class => [$libRootPath . '/src/Clock/SystemClock.php'],

        // Only interested in FunctionCommentSniff.ParamCommentFullStop, excludes the rest
        FunctionCommentSniff::class . '.Missing' => null,
        FunctionCommentSniff::class . '.MissingReturn' => null,
        FunctionCommentSniff::class . '.MissingParamTag' => null,
        FunctionCommentSniff::class . '.EmptyThrows' => null,
        FunctionCommentSniff::class . '.IncorrectParamVarName' => null,
        FunctionCommentSniff::class . '.IncorrectTypeHint' => null,
        FunctionCommentSniff::class . '.MissingParamComment' => null,
        FunctionCommentSniff::class . '.ParamNameNoMatch' => null,
        FunctionCommentSniff::class . '.InvalidReturn' => null,

        // Allows alignment in test providers
        DuplicateSpacesSniff::class => [$libRootPath . '/tests'],

        // Keep a line between same use types, spacing around uses is done in other fixers
        UseSpacingSniff::class . '.IncorrectLinesCountBeforeFirstUse' => null,
        UseSpacingSniff::class . '.IncorrectLinesCountAfterLastUse' => null,
    ]);
};
