<?php

namespace Brick\Tests\DateTime\Clock;

use Brick\Tests\DateTime\AbstractTestCase;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Clock\OffsetClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * Unit tests for class OffsetClock.
 */
class OffsetClockTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOffsetClock
     *
     * @param integer $second         The epoch second to set the base clock to.
     * @param integer $nano           The nano to set the base clock to.
     * @param string  $duration       A parsable duration string.
     * @param integer $expectedSecond The expected epoch second returned by the clock.
     * @param integer $expectedNano   The expected nano returned by the clock.
     */
    public function testOffsetClock($second, $nano, $duration, $expectedSecond, $expectedNano)
    {
        $baseClock = new FixedClock(Instant::of($second, $nano));
        $clock = new OffsetClock($baseClock, Duration::parse($duration));

        $this->assertReadableInstantEquals($expectedSecond, $expectedNano, $clock->getTime());
    }

    /**
     * @return array
     */
    public function providerOffsetClock()
    {
        return [
            [1000, 0, 'PT0.5S', 1000, 500000000],
            [1000, 0, 'PT-0.5S', 999, 500000000],
            [1000000, 123456789, '-PT1H30M', 994600, 123456789],
            [1000000, 123456789, 'PT5M30.9S', 1000331, 23456789]
        ];
    }
}
