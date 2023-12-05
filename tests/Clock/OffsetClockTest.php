<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Clock;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Clock\OffsetClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class OffsetClock.
 */
class OffsetClockTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOffsetClock
     *
     * @param int    $second         The epoch second to set the base clock to.
     * @param int    $nano           The nano to set the base clock to.
     * @param string $duration       A parsable duration string.
     * @param int    $expectedSecond The expected epoch second returned by the clock.
     * @param int    $expectedNano   The expected nano returned by the clock.
     */
    public function testOffsetClock(int $second, int $nano, string $duration, int $expectedSecond, int $expectedNano): void
    {
        $baseClock = new FixedClock(Instant::of($second, $nano));
        $clock = new OffsetClock($baseClock, Duration::parse($duration));

        self::assertInstantIs($expectedSecond, $expectedNano, $clock->getTime());
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
