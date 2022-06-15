<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Commenting\FunctionCommentSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\PsrAutoloadingFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\ModernizeTypesCastingFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Comment\CommentToPhpdocFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\FunctionNotation\FunctionTypehintSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToParamTypeFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToReturnTypeFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\ListNotation\ListSyntaxFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeIncrementFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAddMissingParamAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderByValueFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocReturnSelfReferenceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTagCasingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarAnnotationCorrectOrderFixer;
use PhpCsFixer\Fixer\PhpTag\LinebreakAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBeforeStatementFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\Namespaces\ReferenceUsedNamesOnlySniff;
use SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $libRootPath = __DIR__ . '/../../';

    $ecsConfig->paths(
        [
            $libRootPath . '/src',
            $libRootPath . '/tests',
            __FILE__
        ]
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

        // Keep a line between same use types, spacing around uses is done in other fixers
        UseSpacingSniff::class . '.IncorrectLinesCountBeforeFirstUse' => null,
        UseSpacingSniff::class . '.IncorrectLinesCountAfterLastUse' => null,
    ]);

    $ecsConfig->sets([
        SetList::PSR_12
    ]);

    $ecsConfig->rules(
        [
            NoUnusedImportsFixer::class,
            BlankLineBeforeStatementFixer::class,
            CastSpacesFixer::class,
            CommentToPhpdocFixer::class,
            DeclareStrictTypesFixer::class,
            FunctionTypehintSpaceFixer::class,
            LinebreakAfterOpeningTagFixer::class,
            LowercaseStaticReferenceFixer::class,
            LowercaseCastFixer::class,
            MethodChainingIndentationFixer::class,
            NativeFunctionCasingFixer::class,
            NativeConstantInvocationFixer::class,
            NewWithBracesFixer::class,
            ModernizeTypesCastingFixer::class,
            NoEmptyStatementFixer::class,
            NoExtraBlankLinesFixer::class,
            NoMultilineWhitespaceAroundDoubleArrowFixer::class,
            NoSinglelineWhitespaceBeforeSemicolonsFixer::class,
            ObjectOperatorWithoutWhitespaceFixer::class,
            PhpUnitDedicateAssertFixer::class,
            PhpUnitDedicateAssertInternalTypeFixer::class,
            PhpUnitExpectationFixer::class,
            NotOperatorWithSuccessorSpaceFixer::class,
            PhpUnitStrictFixer::class,
            PhpdocAddMissingParamAnnotationFixer::class,
            PhpdocToParamTypeFixer::class,
            PhpdocToPropertyTypeFixer::class,
            PhpdocToReturnTypeFixer::class,
            PhpdocAlignFixer::class,
            NoEmptyPhpdocFixer::class,
            PhpdocIndentFixer::class,
            TrimArraySpacesFixer::class,
            PhpdocNoEmptyReturnFixer::class,
            StandardizeIncrementFixer::class,
            IncludeFixer::class,
            PhpdocNoUselessInheritdocFixer::class,
            NoUnneededControlParenthesesFixer::class,
            NoLeadingImportSlashFixer::class,
            PhpdocOrderByValueFixer::class,
            PhpdocReturnSelfReferenceFixer::class,
            PhpdocScalarFixer::class,
            PhpdocSeparationFixer::class,
            PhpdocSingleLineVarSpacingFixer::class,
            PhpdocTagCasingFixer::class,
            PhpdocSummaryFixer::class,
            PhpdocTrimFixer::class,
            PhpdocTypesFixer::class,
            PhpdocVarAnnotationCorrectOrderFixer::class,
            BinaryOperatorSpacesFixer::class,
            SingleQuoteFixer::class,
            SemicolonAfterInstructionFixer::class,
            ReturnTypeDeclarationFixer::class,
            ShortScalarCastFixer::class,
            SingleBlankLineBeforeNamespaceFixer::class,
            SingleLineCommentStyleFixer::class,
            PsrAutoloadingFixer::class,
            SpaceAfterSemicolonFixer::class,
            NoWhitespaceInBlankLineFixer::class,
            StrictComparisonFixer::class,
            TernaryOperatorSpacesFixer::class,
            TernaryToNullCoalescingFixer::class,
            VoidReturnFixer::class,
            UnaryOperatorSpacesFixer::class,
            WhitespaceAfterCommaInArrayFixer::class,
            NoTrailingCommaInSinglelineArrayFixer::class,
        ]
    );

    $ecsConfig->ruleWithConfiguration(ListSyntaxFixer::class, ['syntax' => 'short']);
    $ecsConfig->ruleWithConfiguration(MethodArgumentSpaceFixer::class, ['on_multiline' => 'ensure_fully_multiline']);
    $ecsConfig->ruleWithConfiguration(OrderedClassElementsFixer::class, ['order' => ['use_trait', 'case', 'constant_public', 'constant_protected', 'constant_private', 'property_public', 'property_protected', 'property_private', 'construct', 'phpunit', 'method_public', 'magic', 'method_protected', 'method_private', 'destruct']]);
    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, ['syntax' => 'short']);
    $ecsConfig->ruleWithConfiguration(PhpUnitTestCaseStaticMethodCallsFixer::class, ['call_type' => 'this']);
    $ecsConfig->ruleWithConfiguration(PhpdocTypesOrderFixer::class, ['null_adjustment' => 'always_last']);
    $ecsConfig->ruleWithConfiguration(NoSuperfluousPhpdocTagsFixer::class, ['allow_mixed' => true]);
    $ecsConfig->ruleWithConfiguration(ClassAttributesSeparationFixer::class, ['elements' => ['method' => 'one', 'property' => 'one']]);

    // PHPCS

    $ecsConfig->rules(
        [
            FunctionCommentSniff::class,
        ]
    );

    $ecsConfig->ruleWithConfiguration(
        DocCommentSpacingSniff::class,
        [
        'linesCountBetweenAnnotationsGroups' => 1,
        'annotationsGroups' => [
            '@todo',
            '@internal,@deprecated',
            '@link,@see,@uses',
            '@dataProvider',
            '@param',
            '@return',
            '@throws'
            ]
        ]
    );

    $ecsConfig->ruleWithConfiguration(
        ReferenceUsedNamesOnlySniff::class,
        [
            'allowFallbackGlobalConstants' => false,
            'allowFallbackGlobalFunctions' => false,
            'allowFullyQualifiedGlobalClasses' => false,
            'allowFullyQualifiedGlobalConstants' => false,
            'allowFullyQualifiedGlobalFunctions' => false,
            'allowFullyQualifiedNameForCollidingClasses' => true,
            'allowFullyQualifiedNameForCollidingConstants' => true,
            'allowFullyQualifiedNameForCollidingFunctions' => true,
            'searchAnnotations' => true,
        ]
    );

    $ecsConfig->ruleWithConfiguration(
        UseSpacingSniff::class,
        [
            'linesCountAfterLastUse' => 0,
            'linesCountBetweenUseTypes' => 1,
            'linesCountBeforeFirstUse' => 0,
        ]
    );
};
