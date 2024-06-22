<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Unit tests for class DayOfWeek.
 */
class DayOfWeekTest extends AbstractTestCase
{
    /**
     * @param int       $expectedValue The expected value of the constant.
     * @param DayOfWeek $dayOfWeek     The day-of-week instance.
     */
    #[DataProvider('providerValues')]
    public function testValues(int $expectedValue, DayOfWeek $dayOfWeek): void
    {
        self::assertSame($expectedValue, $dayOfWeek->value);
    }

    public static function providerValues(): array
    {
        return [
            [1, DayOfWeek::MONDAY],
            [2, DayOfWeek::TUESDAY],
            [3, DayOfWeek::WEDNESDAY],
            [4, DayOfWeek::THURSDAY],
            [5, DayOfWeek::FRIDAY],
            [6, DayOfWeek::SATURDAY],
            [7, DayOfWeek::SUNDAY],
        ];
    }

    /**
     * @param int       $epochSecond       The epoch second to set the clock time to.
     * @param string    $timeZone          The time-zone to get the current day-of-week in.
     * @param DayOfWeek $expectedDayOfWeek The expected day-of-week.
     */
    #[DataProvider('providerNow')]
    public function testNow(int $epochSecond, string $timeZone, DayOfWeek $expectedDayOfWeek): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        self::assertSame($expectedDayOfWeek, DayOfWeek::now(TimeZone::parse($timeZone), $clock));
    }

    public static function providerNow(): array
    {
        return [
            [1388534399, '-01:00', DayOfWeek::TUESDAY],
            [1388534399, '+00:00', DayOfWeek::TUESDAY],
            [1388534399, '+01:00', DayOfWeek::WEDNESDAY],
            [1388534400, '-01:00', DayOfWeek::TUESDAY],
            [1388534400, '+00:00', DayOfWeek::WEDNESDAY],
            [1388534400, '+01:00', DayOfWeek::WEDNESDAY],
        ];
    }

    public function testAll(): void
    {
        for ($day = DayOfWeek::MONDAY->value; $day <= DayOfWeek::SUNDAY->value; $day++) {
            $dayOfWeek = DayOfWeek::from($day);

            foreach (DayOfWeek::all($dayOfWeek) as $dow) {
                self::assertSame($dayOfWeek, $dow);
                $dayOfWeek = $dayOfWeek->plus(1);
            }
        }
    }

    #[DataProvider('providerIsWeekday')]
    public function testIsWeekday(DayOfWeek $dayOfWeek, bool $isWeekday): void
    {
        self::assertSame($isWeekday, $dayOfWeek->isWeekday());
    }

    public static function providerIsWeekday(): array
    {
        return [
            [DayOfWeek::MONDAY, true],
            [DayOfWeek::TUESDAY, true],
            [DayOfWeek::WEDNESDAY, true],
            [DayOfWeek::THURSDAY, true],
            [DayOfWeek::FRIDAY, true],
            [DayOfWeek::SATURDAY, false],
            [DayOfWeek::SUNDAY, false],
        ];
    }

    #[DataProvider('providerIsWeekend')]
    public function testIsWeekend(DayOfWeek $dayOfWeek, bool $isWeekend): void
    {
        self::assertSame($isWeekend, $dayOfWeek->isWeekend());
    }

    public static function providerIsWeekend(): array
    {
        return [
            [DayOfWeek::MONDAY, false],
            [DayOfWeek::TUESDAY, false],
            [DayOfWeek::WEDNESDAY, false],
            [DayOfWeek::THURSDAY, false],
            [DayOfWeek::FRIDAY, false],
            [DayOfWeek::SATURDAY, true],
            [DayOfWeek::SUNDAY, true],
        ];
    }

    /**
     * @param DayOfWeek $dayOfWeek         The base day-of-week.
     * @param int       $plusDays          The number of days to add.
     * @param DayOfWeek $expectedDayOfWeek The expected day-of-week.
     */
    #[DataProvider('providerPlus')]
    public function testPlus(DayOfWeek $dayOfWeek, int $plusDays, DayOfWeek $expectedDayOfWeek): void
    {
        self::assertSame($expectedDayOfWeek, $dayOfWeek->plus($plusDays));
    }

    /**
     * @param DayOfWeek $dayOfWeek         The base day-of-week.
     * @param int       $plusDays          The number of days to add.
     * @param DayOfWeek $expectedDayOfWeek The expected day-of-week.
     */
    #[DataProvider('providerPlus')]
    public function testMinus(DayOfWeek $dayOfWeek, int $plusDays, DayOfWeek $expectedDayOfWeek): void
    {
        self::assertSame($expectedDayOfWeek, $dayOfWeek->minus(-$plusDays));
    }

    public static function providerPlus(): Generator
    {
        for ($dayOfWeek = DayOfWeek::MONDAY->value; $dayOfWeek <= DayOfWeek::SUNDAY->value; $dayOfWeek++) {
            for ($plusDays = -15; $plusDays <= 15; $plusDays++) {
                $expectedDayOfWeek = $dayOfWeek + $plusDays;

                while ($expectedDayOfWeek < 1) {
                    $expectedDayOfWeek += 7;
                }
                while ($expectedDayOfWeek > 7) {
                    $expectedDayOfWeek -= 7;
                }

                yield [DayOfWeek::from($dayOfWeek), $plusDays, DayOfWeek::from($expectedDayOfWeek)];
            }
        }
    }

    /**
     * @param DayOfWeek $dayOfWeek    The day-of-week.
     * @param string    $expectedName The expected name.
     */
    #[DataProvider('providerToString')]
    public function testJsonSerialize(DayOfWeek $dayOfWeek, string $expectedName): void
    {
        self::assertSame(json_encode($expectedName, JSON_THROW_ON_ERROR), json_encode($dayOfWeek, JSON_THROW_ON_ERROR));
    }

    /**
     * @param DayOfWeek $dayOfWeek    The day-of-week.
     * @param string    $expectedName The expected name.
     */
    #[DataProvider('providerToString')]
    public function testToString(DayOfWeek $dayOfWeek, string $expectedName): void
    {
        self::assertSame($expectedName, $dayOfWeek->toString());
    }

    public static function providerToString(): array
    {
        return [
            [DayOfWeek::MONDAY,    'Monday'],
            [DayOfWeek::TUESDAY,   'Tuesday'],
            [DayOfWeek::WEDNESDAY, 'Wednesday'],
            [DayOfWeek::THURSDAY,  'Thursday'],
            [DayOfWeek::FRIDAY,    'Friday'],
            [DayOfWeek::SATURDAY,  'Saturday'],
            [DayOfWeek::SUNDAY,    'Sunday'],
        ];
    }
}
