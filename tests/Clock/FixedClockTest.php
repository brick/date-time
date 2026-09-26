<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Clock;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\Tests\AbstractTestCase;
use Brick\DateTime\TimeZone;

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

    public function testNow(): void
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        $now = $clock->now();

        self::assertSame('123456789.987654', $now->format('U.u'));
        self::assertSame(0, $now->getOffset());
    }

    public function testNowWithTimeZone(): void
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321), TimeZone::parse('America/New_York'));
        $now = $clock->now();

        self::assertSame('1973-11-29T16:33:09.987654-05:00', $now->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('America/New_York', $now->getTimezone()->getName());
    }
}
