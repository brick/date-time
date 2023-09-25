<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Clock;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class FixedClock.
 */
class FixedClockTest extends AbstractTestCase
{
    public function testFixedClock(): void
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        self::assertInstantIs(123456789, 987654321, $clock->getTime());
    }
}
