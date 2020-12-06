<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Parser;

use Brick\DateTime\Parser\PatternParserBuilder;
use Brick\DateTime\Tests\AbstractTestCase;
use RuntimeException;

/**
 * Unit tests for class PatternParserBuilder.
 */
class PatternParserBuilderTest extends AbstractTestCase
{
    public function testEndOptionalShouldThrowRuntimeException()
    {
        $patternParserBuilder = new PatternParserBuilder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot call endOptional() without a call to startOptional() first.');

        $patternParserBuilder->endOptional();
    }

    public function testEndGroupShouldThrowRuntimeException()
    {
        $patternParserBuilder = new PatternParserBuilder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot call endGroup() without a call to startGroup() first.');

        $patternParserBuilder->endGroup();
    }

    public function testToParserWithNonEmptyStack()
    {
        $patternParserBuilder = new PatternParserBuilder();
        $patternParserBuilder->startGroup();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Builder misses call to endOptional() or endGroup().');

        $patternParserBuilder->toParser();
    }
}
