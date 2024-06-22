<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * Unit tests for class DefaultClock.
 */
class DefaultClockTest extends AbstractTestCase
{
    public function tearDown(): void
    {
        DefaultClock::reset();
    }

    public function testFreeze(): void
    {
        DefaultClock::freeze(Instant::of(123456, 5000));
        self::assertInstantIs(123456, 5000, Instant::now());
    }

    public function testTravelTo(): void
    {
        $fixedClock = new FixedClock(Instant::of(1000, 0));
        DefaultClock::set($fixedClock);
        self::assertInstantIs(1000, 0, Instant::now());

        DefaultClock::travelTo(Instant::of(-1000));
        self::assertInstantIs(-1000, 0, Instant::now());
    }

    public function testTravelBy(): void
    {
        $fixedClock = new FixedClock(Instant::of(1000, 0));
        DefaultClock::set($fixedClock);
        self::assertInstantIs(1000, 0, Instant::now());

        // Travel forward
        DefaultClock::travelBy(Duration::ofSeconds(1000));
        self::assertInstantIs(2000, 0, Instant::now());

        // Travel backward
        DefaultClock::travelBy(Duration::ofSeconds(-1000));
        self::assertInstantIs(1000, 0, Instant::now());

        $fixedClock->move(2);
        self::assertInstantIs(1002, 0, Instant::now());
    }

    public function testScale(): void
    {
        $fixedClock = new FixedClock(Instant::of(1000, 0));
        DefaultClock::set($fixedClock);
        self::assertInstantIs(1000, 0, Instant::now());

        DefaultClock::scale(60);
        self::assertInstantIs(1000, 0, Instant::now());

        $fixedClock->move(2);
        self::assertInstantIs(1120, 0, Instant::now());
    }
}
