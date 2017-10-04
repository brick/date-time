<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;

/**
 * Unit tests for class DefaultClock.
 */
class DefaultClockTest extends AbstractTestCase
{
    public function tearDown()
    {
        DefaultClock::reset();
    }

    public function testFreeze()
    {
        DefaultClock::freeze(Instant::of(123456, 5000));
        $this->assertInstantIs(123456, 5000, Instant::now());
    }

    public function testTravel()
    {
        $fixedClock = new FixedClock(Instant::of(1000, 0));
        DefaultClock::set($fixedClock);
        $this->assertInstantIs(1000, 0, Instant::now());

        DefaultClock::travel(Instant::of(-1000));
        $this->assertInstantIs(-1000, 0, Instant::now());

        $fixedClock->move(2);
        $this->assertInstantIs(-998, 0, Instant::now());
    }

    public function testScale()
    {
        $fixedClock = new FixedClock(Instant::of(1000, 0));
        DefaultClock::set($fixedClock);
        $this->assertInstantIs(1000, 0, Instant::now());

        DefaultClock::scale(60);
        $this->assertInstantIs(1000, 0, Instant::now());

        $fixedClock->move(2);
        $this->assertInstantIs(1120, 0, Instant::now());
    }
}
