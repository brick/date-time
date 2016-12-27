<?php

namespace Brick\DateTime\Tests;

use Brick\DateTime\Instant;
use Brick\DateTime\Interval;

/**
 * Unit tests for class Interval.
 */
class IntervalTest extends AbstractTestCase
{
    public function testToString()
    {
        $interval = new Interval(
            Instant::of(1000000000),
            Instant::of(2000000000)
        );

        $this->assertSame('2001-09-09T01:46:40Z/2033-05-18T03:33:20Z', (string) $interval);
    }
}
