<?php

declare(strict_types=1);

namespace Brick\DateTime\PHPStan;

use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

use function sprintf;

/**
 * Requires every public abstract or interface method to declare whether it is pure.
 *
 * This is MethodPurityRule for methods without a body, which PHPStan does not report to it. Without a body to infer
 * it from, the tag is not fixable: it is a promise every implementation must keep, and only a human can make it.
 *
 * @implements Rule<InClassMethodNode>
 */
final class AbstractMethodPurityRule implements Rule
{
    #[Override]
    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $method = $node->getMethodReflection();

        if ($node->getOriginalNode()->stmts !== null || ! $method->isPublic() || ! $method->isPure()->maybe()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Abstract method %s::%s() must declare its purity with @phpstan-pure or @phpstan-impure.',
                $node->getClassReflection()->getDisplayName(),
                $method->getName(),
            ))
                ->identifier('brick.methodPurity')
                ->tip('@phpstan-all-methods-pure or @phpstan-all-methods-impure on the class also does it, except for a void method in a pure class.')
                ->build(),
        ];
    }
}
