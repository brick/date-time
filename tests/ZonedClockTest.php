<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use Brick\DateTime\ZonedClock;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Clock\ClockInterface;

/**
 * Unit tests for class ZonedClock.
 */
class ZonedClockTest extends AbstractTestCase
{
    public function testGetClockAndTimeZone(): void
    {
        $clock = new FixedClock(Instant::of(1000000000));
        $timeZone = TimeZone::parse('Europe/Paris');
        $zonedClock = new ZonedClock($clock, $timeZone);

        self::assertSame($clock, $zonedClock->getClock());
        self::assertSame($timeZone, $zonedClock->getTimeZone());
    }

    public function testGetTimeReadsTheUnderlyingClock(): void
    {
        $clock = new FixedClock(Instant::of(1000000000, 123456789));
        $zonedClock = new ZonedClock($clock, TimeZone::parse('Asia/Tokyo'));

        self::assertInstantIs(1000000000, 123456789, $zonedClock->getTime());

        $clock->move(60);

        self::assertInstantIs(1000000060, 123456789, $zonedClock->getTime());
    }

    public function testWithTimeZone(): void
    {
        $clock = new FixedClock(Instant::of(1000000000));
        $paris = TimeZone::parse('Europe/Paris');
        $tokyo = TimeZone::parse('Asia/Tokyo');

        $zonedClock = new ZonedClock($clock, $paris);
        $newZonedClock = $zonedClock->withTimeZone($tokyo);

        self::assertNotSame($zonedClock, $newZonedClock);
        self::assertSame($clock, $newZonedClock->getClock());
        self::assertSame($tokyo, $newZonedClock->getTimeZone());
        self::assertSame($paris, $zonedClock->getTimeZone());
    }

    public function testIsPsrClock(): void
    {
        $zonedClock = new ZonedClock(new FixedClock(Instant::of(1000000000)), TimeZone::utc());

        self::assertInstanceOf(ClockInterface::class, $zonedClock);
    }

    public function testNow(): void
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        $zonedClock = new ZonedClock($clock, TimeZone::parse('America/New_York'));
        $now = $zonedClock->now();

        self::assertSame('1973-11-29T16:33:09.987654-05:00', $now->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('America/New_York', $now->getTimezone()->getName());
    }

    public function testNowWithOffsetTimeZone(): void
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        $zonedClock = new ZonedClock($clock, TimeZone::parse('+05:30'));
        $now = $zonedClock->now();

        self::assertSame('1973-11-30T03:03:09.987654+05:30', $now->format('Y-m-d\TH:i:s.uP'));
    }

    #[DataProvider('providerGetCurrent')]
    public function testGetCurrent(int $epochSecond, string $timeZone, string $expectedZonedDateTime, string $expectedLocalDateTime, string $expectedLocalDate, string $expectedLocalTime): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        $zonedClock = new ZonedClock($clock, TimeZone::parse($timeZone));

        self::assertSame($expectedZonedDateTime, $zonedClock->getCurrentZonedDateTime()->toISOString());
        self::assertSame($expectedLocalDateTime, $zonedClock->getCurrentLocalDateTime()->toISOString());
        self::assertSame($expectedLocalDate, $zonedClock->getCurrentLocalDate()->toISOString());
        self::assertSame($expectedLocalTime, $zonedClock->getCurrentLocalTime()->toISOString());
    }

    public static function providerGetCurrent(): array
    {
        return [
            [1000000000, 'UTC', '2001-09-09T01:46:40Z[UTC]', '2001-09-09T01:46:40', '2001-09-09', '01:46:40'],
            [1000000000, 'Asia/Tokyo', '2001-09-09T10:46:40+09:00[Asia/Tokyo]', '2001-09-09T10:46:40', '2001-09-09', '10:46:40'],
            [1000000000, 'America/Los_Angeles', '2001-09-08T18:46:40-07:00[America/Los_Angeles]', '2001-09-08T18:46:40', '2001-09-08', '18:46:40'],

            // Europe/London moves from GMT to BST at 2026-03-29T01:00:00Z
            [1774745999, 'Europe/London', '2026-03-29T00:59:59Z[Europe/London]', '2026-03-29T00:59:59', '2026-03-29', '00:59:59'],
            [1774746000, 'Europe/London', '2026-03-29T02:00+01:00[Europe/London]', '2026-03-29T02:00', '2026-03-29', '02:00'],
        ];
    }
}
