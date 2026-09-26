<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Clock;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Clock\OffsetClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\Tests\AbstractTestCase;
use Brick\DateTime\TimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class OffsetClock.
 */
class OffsetClockTest extends AbstractTestCase
{
    /**
     * @param int    $second         The epoch second to set the base clock to.
     * @param int    $nano           The nano to set the base clock to.
     * @param string $duration       A parsable duration string.
     * @param int    $expectedSecond The expected epoch second returned by the clock.
     * @param int    $expectedNano   The expected nano returned by the clock.
     */
    #[DataProvider('providerOffsetClock')]
    public function testOffsetClock(int $second, int $nano, string $duration, int $expectedSecond, int $expectedNano): void
    {
        $baseClock = new FixedClock(Instant::of($second, $nano));
        $clock = new OffsetClock($baseClock, Duration::parse($duration));

        self::assertInstantIs($expectedSecond, $expectedNano, $clock->getTime());
    }

    public function testNow(): void
    {
        $baseClock = new FixedClock(Instant::of(1000000, 123456789));
        $clock = new OffsetClock($baseClock, Duration::parse('PT5M30.9S'));
        $now = $clock->now();

        self::assertSame('1000331.023456', $now->format('U.u'));
        self::assertSame(0, $now->getOffset());
    }

    public function testNowUsesReferenceClockTimeZone(): void
    {
        $baseClock = new FixedClock(Instant::of(1000000, 123456789), TimeZone::parse('Europe/Paris'));
        $clock = new OffsetClock($baseClock, Duration::parse('PT5M30.9S'));
        $now = $clock->now();

        self::assertSame('1970-01-12T14:52:11.023456+01:00', $now->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('Europe/Paris', $now->getTimezone()->getName());
    }

    public static function providerOffsetClock(): array
    {
        return [
            [1000, 0, 'PT0.5S', 1000, 500000000],
            [1000, 0, 'PT-0.5S', 999, 500000000],
            [1000000, 123456789, '-PT1H30M', 994600, 123456789],
            [1000000, 123456789, 'PT5M30.9S', 1000331, 23456789],
        ];
    }
}
