<?php

namespace Brick\Tests\DateTime\Clock;

use Brick\Tests\DateTime\AbstractTestCase;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;

/**
 * Unit tests for class FixedClock.
 */
class FixedClockTest extends AbstractTestCase
{
    public function testFixedClock()
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        $this->assertReadableInstantEquals(123456789, 987654321, $clock->getTime());
    }
}
