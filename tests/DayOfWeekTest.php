<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use Generator;

use function json_encode;

/**
 * Unit tests for class DayOfWeek.
 */
class DayOfWeekTest extends AbstractTestCase
{
    /**
     * @dataProvider providerValues
     *
     * @param int       $expectedValue The expected value of the constant.
     * @param DayOfWeek $dayOfWeek     The day-of-week instance.
     */
    public function testValues(int $expectedValue, DayOfWeek $dayOfWeek): void
    {
        self::assertSame($expectedValue, $dayOfWeek->value);
    }

    public function providerValues(): array
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

    public function testOf(): void
    {
        self::assertSame(DayOfWeek::FRIDAY, DayOfWeek::of(5));
        self::assertSame(DayOfWeek::FRIDAY, DayOfWeek::of(DayOfWeek::FRIDAY));
    }

    /**
     * @dataProvider providerOfInvalidDayOfWeekThrowsException
     */
    public function testOfInvalidDayOfWeekThrowsException(int $dayOfWeek): void
    {
        $this->expectException(DateTimeException::class);
        DayOfWeek::of($dayOfWeek);
    }

    public function providerOfInvalidDayOfWeekThrowsException(): array
    {
        return [
            [-1],
            [0],
            [8],
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param int       $epochSecond       The epoch second to set the clock time to.
     * @param string    $timeZone          The time-zone to get the current day-of-week in.
     * @param DayOfWeek $expectedDayOfWeek The expected day-of-week.
     */
    public function testNow(int $epochSecond, string $timeZone, DayOfWeek $expectedDayOfWeek): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        self::assertSame($expectedDayOfWeek, DayOfWeek::now(TimeZone::parse($timeZone), $clock));
    }

    public function providerNow(): array
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

    public function testMonday(): void
    {
        self::assertSame(DayOfWeek::MONDAY, DayOfWeek::monday());
    }

    public function testTuesday(): void
    {
        self::assertSame(DayOfWeek::TUESDAY, DayOfWeek::tuesday());
    }

    public function testWednesday(): void
    {
        self::assertSame(DayOfWeek::WEDNESDAY, DayOfWeek::wednesday());
    }

    public function testThursday(): void
    {
        self::assertSame(DayOfWeek::THURSDAY, DayOfWeek::thursday());
    }

    public function testFriday(): void
    {
        self::assertSame(DayOfWeek::FRIDAY, DayOfWeek::friday());
    }

    public function testSaturday(): void
    {
        self::assertSame(DayOfWeek::SATURDAY, DayOfWeek::saturday());
    }

    public function testSunday(): void
    {
        self::assertSame(DayOfWeek::SUNDAY, DayOfWeek::sunday());
    }

    public function testIs(): void
    {
        for ($i = DayOfWeek::MONDAY->value; $i <= DayOfWeek::SUNDAY->value; $i++) {
            for ($j = DayOfWeek::MONDAY->value; $j <= DayOfWeek::SUNDAY->value; $j++) {
                self::assertSame($i === $j, DayOfWeek::from($i)->is($j));
                self::assertSame($i === $j, DayOfWeek::from($i)->is(DayOfWeek::from($j)));
            }
        }
    }

    public function testIsEqualTo(): void
    {
        for ($i = DayOfWeek::MONDAY->value; $i <= DayOfWeek::SUNDAY->value; $i++) {
            for ($j = DayOfWeek::MONDAY->value; $j <= DayOfWeek::SUNDAY->value; $j++) {
                self::assertSame($i === $j, DayOfWeek::from($i)->isEqualTo(DayOfWeek::from($j)));
            }
        }
    }

    /**
     * @dataProvider providerIsWeekday
     */
    public function testIsWeekday(DayOfWeek $dayOfWeek, bool $isWeekday): void
    {
        self::assertSame($isWeekday, $dayOfWeek->isWeekday());
    }

    public function providerIsWeekday(): array
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

    /**
     * @dataProvider providerIsWeekend
     */
    public function testIsWeekend(DayOfWeek $dayOfWeek, bool $isWeekend): void
    {
        self::assertSame($isWeekend, $dayOfWeek->isWeekend());
    }

    public function providerIsWeekend(): array
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
     * @dataProvider providerPlus
     *
     * @param DayOfWeek $dayOfWeek         The base day-of-week.
     * @param int       $plusDays          The number of days to add.
     * @param DayOfWeek $expectedDayOfWeek The expected day-of-week.
     */
    public function testPlus(DayOfWeek $dayOfWeek, int $plusDays, DayOfWeek $expectedDayOfWeek): void
    {
        self::assertSame($expectedDayOfWeek, $dayOfWeek->plus($plusDays));
    }

    /**
     * @dataProvider providerPlus
     *
     * @param DayOfWeek $dayOfWeek         The base day-of-week.
     * @param int       $plusDays          The number of days to add.
     * @param DayOfWeek $expectedDayOfWeek The expected day-of-week.
     */
    public function testMinus(DayOfWeek $dayOfWeek, int $plusDays, DayOfWeek $expectedDayOfWeek): void
    {
        self::assertSame($expectedDayOfWeek, $dayOfWeek->minus(-$plusDays));
    }

    public function providerPlus(): Generator
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
     * @dataProvider providerToString
     *
     * @param DayOfWeek $dayOfWeek    The day-of-week.
     * @param string    $expectedName The expected name.
     */
    public function testJsonSerialize(DayOfWeek $dayOfWeek, string $expectedName): void
    {
        self::assertSame(json_encode($expectedName), json_encode($dayOfWeek));
    }

    /**
     * @dataProvider providerToString
     *
     * @param DayOfWeek $dayOfWeek    The day-of-week.
     * @param string    $expectedName The expected name.
     */
    public function testToString(DayOfWeek $dayOfWeek, string $expectedName): void
    {
        self::assertSame($expectedName, $dayOfWeek->toString());
    }

    public function providerToString(): array
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
