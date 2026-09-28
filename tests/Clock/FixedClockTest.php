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

    public function testWithTimeZone(): void
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        $timeZone = TimeZone::parse('America/New_York');
        $zonedClock = $clock->withTimeZone($timeZone);

        self::assertSame($clock, $zonedClock->getClock());
        self::assertSame($timeZone, $zonedClock->getTimeZone());
        self::assertSame('1973-11-29T16:33:09.987654321-05:00[America/New_York]', $zonedClock->getCurrentZonedDateTime()->toISOString());
    }
}
