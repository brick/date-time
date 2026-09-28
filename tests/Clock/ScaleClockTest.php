<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Clock;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Clock\ScaleClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\Tests\AbstractTestCase;
use Brick\DateTime\TimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class ScaleClock.
 */
class ScaleClockTest extends AbstractTestCase
{
    /**
     * @param int    $second          The epoch second to set the base clock to.
     * @param int    $nano            The nano to set the base clock to.
     * @param string $duration        The time elapsed, as a parsable duration string.
     * @param int    $scale           The time scale.
     * @param string $expectedInstant The expected epoch second returned by the clock.
     */
    #[DataProvider('providerScaleClock')]
    public function testScaleClock(int $second, int $nano, string $duration, int $scale, string $expectedInstant): void
    {
        $baseInstant = Instant::of($second, $nano);

        $baseClock = new FixedClock($baseInstant);
        $scaleClock = new ScaleClock($baseClock, $scale);

        $baseClock->setTime($baseInstant->plus(Duration::parse($duration)));

        $actualTime = $scaleClock->getTime();

        self::assertInstanceOf(Instant::class, $actualTime);
        self::assertSame($expectedInstant, $actualTime->toDecimal());
    }

    public function testWithTimeZone(): void
    {
        $baseInstant = Instant::of(1000000, 123456789);

        $baseClock = new FixedClock($baseInstant);
        $scaleClock = new ScaleClock($baseClock, -11);

        $baseClock->setTime($baseInstant->plus(Duration::parse('PT5M30.9S')));

        $timeZone = TimeZone::parse('Asia/Tokyo');
        $zonedClock = $scaleClock->withTimeZone($timeZone);

        self::assertSame($scaleClock, $zonedClock->getClock());
        self::assertSame($timeZone, $zonedClock->getTimeZone());
        self::assertSame('1970-01-12T21:46:00.223456789+09:00[Asia/Tokyo]', $zonedClock->getCurrentZonedDateTime()->toISOString());
    }

    public static function providerScaleClock(): array
    {
        return [
            [1000, 0, 'PT0.5S', 50, '1025'],
            [1000, 0, 'PT-0.5S', 5, '997.5'],
            [1000000, 123456789, '-PT1H30M', 7, '962200.123456789'],
            [1000000, 123456789, 'PT5M30.9S', -11, '996360.223456789'],
        ];
    }
}
