<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Parser;

use Brick\DateTime\Parser\PatternParserBuilder;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class PatternParserBuilder.
 */
class PatternParserBuilderTest extends AbstractTestCase
{
    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Cannot call endOptional() without a call to startOptional() first.
     */
    public function testEndOptionalShouldThrowRuntimeException()
    {
        $patternParserBuilder = new PatternParserBuilder();
        $patternParserBuilder->endOptional();
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Cannot call endGroup() without a call to startGroup() first.
     */
    public function testEndGroupShouldThrowRuntimeException()
    {
        $patternParserBuilder = new PatternParserBuilder();
        $patternParserBuilder->endGroup();
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Builder misses call to endOptional() or endGroup().
     */
    public function testToParserWithNonEmptyStack()
    {
        $patternParserBuilder = new PatternParserBuilder();
        $patternParserBuilder->startGroup();
        $patternParserBuilder->toParser();
    }
}
