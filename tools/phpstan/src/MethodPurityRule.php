<?php

declare(strict_types=1);

namespace Brick\DateTime\PHPStan;

use Override;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodReturnStatementsNode;
use PHPStan\Reflection\Php\PhpMethodFromParserNodeReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use ReflectionProperty;

use function array_pop;
use function assert;
use function explode;
use function implode;
use function ltrim;
use function preg_match;
use function sprintf;
use function str_starts_with;
use function strtolower;
use function trim;

/**
 * Requires every public method, and every constructor, to declare whether it is pure.
 *
 * Constructors count whatever their visibility: a static factory calling a private constructor is only as pure as it.
 *
 * A method declares it with its own tag, `@phpstan-pure` or `@phpstan-impure`, or inherits it from its class,
 * tagged `@phpstan-all-methods-pure` or `@phpstan-all-methods-impure`. A pure class does not cover its void methods,
 * which PHPStan leaves undecided, so each of them needs its own tag.
 *
 * With --fix, the missing tag is inferred from what PHPStan found in the method body:
 *
 * - a certain side effect makes it `@phpstan-impure`;
 * - no side effect at all makes it `@phpstan-pure`, unless it returns void and declares no exception: a void method
 *   can only be pure as a validator;
 * - a possible side effect, such as a call to a method that does not declare its purity yet, is left for a human to
 *   decide, or for another --fix run once the methods it calls are tagged.
 *
 * @implements Rule<MethodReturnStatementsNode>
 */
final class MethodPurityRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return MethodReturnStatementsNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $method = $node->getMethodReflection();

        if (! $this->isRequired($method) || ! $method->isPure()->maybe()) {
            return [];
        }

        $error = RuleErrorBuilder::message(sprintf(
            'Method %s::%s() must declare its purity with @phpstan-pure or @phpstan-impure.',
            $node->getClassReflection()->getDisplayName(),
            $method->getName(),
        ))
            ->identifier('brick.methodPurity')
            ->tip('@phpstan-all-methods-pure or @phpstan-all-methods-impure on the class also does it, except for a void method in a pure class.');

        $classMethod = $this->getClassMethod($node);
        $tag = $this->inferTag($node, $classMethod);

        if ($tag === null) {
            return [$error->build()];
        }

        return [
            $error->fixNode($classMethod, function (ClassMethod $classMethod) use ($tag): ClassMethod {
                $this->addTag($classMethod, $tag);

                return $classMethod;
            })->build(),
        ];
    }

    private function isRequired(PhpMethodFromParserNodeReflection $method): bool
    {
        return $method->isPublic() || strtolower($method->getName()) === '__construct';
    }

    private function inferTag(MethodReturnStatementsNode $node, ClassMethod $classMethod): ?string
    {
        $impurePoints = $node->getImpurePoints();

        foreach ($impurePoints as $impurePoint) {
            if ($impurePoint->isCertain()) {
                return '@phpstan-impure';
            }
        }

        if ($impurePoints !== []) {
            return null;
        }

        $returnsVoid = $classMethod->returnType instanceof Node\Identifier
            && $classMethod->returnType->toLowerString() === 'void';

        if (! $returnsVoid || $classMethod->name->toLowerString() === '__construct') {
            return '@phpstan-pure';
        }

        // A void method without side effects can still validate its arguments, which PHPStan accepts as pure only when
        // it declares what it throws.
        $throwType = $node->getMethodReflection()->getThrowType();

        return $throwType !== null && ! $throwType->isVoid()->yes() ? '@phpstan-pure' : '@phpstan-impure';
    }

    /**
     * Adds the tag at the end of the doc comment, separated from what precedes it as the coding standard wants it.
     */
    private function addTag(ClassMethod $classMethod, string $tag): void
    {
        $doc = $classMethod->getDocComment();

        if ($doc === null) {
            $classMethod->setDocComment(new Doc("/**\n * " . $tag . "\n */"));

            return;
        }

        // The reformatted text has its indentation removed, and the printer indents it again.
        $text = $doc->getReformattedText();

        if (preg_match('/^\/\*\*(.*?)\*\/$/', $text, $matches) === 1) {
            $text = "/**\n * " . trim($matches[1]) . "\n */";
        }

        $lines = explode("\n", $text);
        $closing = array_pop($lines);

        $lastLine = '';

        foreach ($lines as $line) {
            $line = trim(ltrim(trim($line), '/*'));

            if ($line !== '') {
                $lastLine = $line;
            }
        }

        // The coding standard lists the @phpstan-assert tags as a group, but not the other @phpstan- tags, which it
        // keeps together with the purity tag.
        $joinsLastGroup = str_starts_with($lastLine, '@phpstan-') && ! str_starts_with($lastLine, '@phpstan-assert');

        if ($lastLine !== '' && ! $joinsLastGroup) {
            $lines[] = ' *';
        }

        $lines[] = ' * ' . $tag;
        $lines[] = $closing;

        $classMethod->setDocComment(new Doc(implode("\n", $lines)));
    }

    /**
     * Returns the method node that the fix must edit.
     *
     * MethodReturnStatementsNode carries the impure points, but keeps the method node it was built from private, and
     * a fix can only edit the node from the parsed file.
     */
    private function getClassMethod(MethodReturnStatementsNode $node): ClassMethod
    {
        $property = new ReflectionProperty(MethodReturnStatementsNode::class, 'classMethod'); // @phpstan-ignore phpstanApi.runtimeReflection (PHPStan's own node, not the analysed code)
        $classMethod = $property->getValue($node);
        assert($classMethod instanceof ClassMethod);

        return $classMethod;
    }
}
