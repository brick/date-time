<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;

/**
 * Unit tests for class DayOfWeek.
 */
class DayOfWeekTest extends AbstractTestCase
{
    /**
     * @dataProvider providerConstants
     *
     * @param int $expectedValue     The expected value of the constant.
     * @param int $dayOfWeekConstant The day-of-week constant.
     */
    public function testConstants(int $expectedValue, int $dayOfWeekConstant)
    {
        $this->assertSame($expectedValue, $dayOfWeekConstant);
    }

    /**
     * @return array
     */
    public function providerConstants() : array
    {
        return [
            [1, DayOfWeek::MONDAY],
            [2, DayOfWeek::TUESDAY],
            [3, DayOfWeek::WEDNESDAY],
            [4, DayOfWeek::THURSDAY],
            [5, DayOfWeek::FRIDAY],
            [6, DayOfWeek::SATURDAY],
            [7, DayOfWeek::SUNDAY]
        ];
    }

    public function testOf()
    {
        $this->assertDayOfWeekIs(5, DayOfWeek::of(5));
    }

    /**
     * @dataProvider providerOfInvalidDayOfWeekThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param int $dayOfWeek
     */
    public function testOfInvalidDayOfWeekThrowsException(int $dayOfWeek)
    {
        DayOfWeek::of($dayOfWeek);
    }

    /**
     * @return array
     */
    public function providerOfInvalidDayOfWeekThrowsException() : array
    {
        return [
            [-1],
            [0],
            [8]
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param int    $epochSecond       The epoch second to set the clock time to.
     * @param string $timeZone          The time-zone to get the current day-of-week in.
     * @param int    $expectedDayOfWeek The expected day-of-week, from 1 to 7.
     */
    public function testNow(int $epochSecond, string $timeZone, int $expectedDayOfWeek)
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        $this->assertDayOfWeekIs($expectedDayOfWeek, DayOfWeek::now(TimeZone::parse($timeZone), $clock));
    }

    /**
     * @return array
     */
    public function providerNow() : array
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

    public function testAll()
    {
        for ($day = DayOfWeek::MONDAY; $day <= DayOfWeek::SUNDAY; $day++) {
            $dayOfWeek = DayOfWeek::of($day);

            foreach (DayOfWeek::all($dayOfWeek) as $dow) {
                $this->assertTrue($dow->isEqualTo($dayOfWeek));
                $dayOfWeek = $dayOfWeek->plus(1);
            }
        }
    }

    public function testMonday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::MONDAY, DayOfWeek::monday());
    }

    public function testTuesday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::TUESDAY, DayOfWeek::tuesday());
    }

    public function testWednesday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::WEDNESDAY, DayOfWeek::wednesday());
    }

    public function testThursday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::THURSDAY, DayOfWeek::thursday());
    }

    public function testFriday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::FRIDAY, DayOfWeek::friday());
    }

    public function testSaturday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::SATURDAY, DayOfWeek::saturday());
    }

    public function testSunday()
    {
        $this->assertDayOfWeekIs(DayOfWeek::SUNDAY, DayOfWeek::sunday());
    }

    public function testIs()
    {
        for ($i = DayOfWeek::MONDAY; $i <= DayOfWeek::SUNDAY; $i++) {
            for ($j = DayOfWeek::MONDAY; $j <= DayOfWeek::SUNDAY; $j++) {
                $this->assertSame($i === $j, DayOfWeek::of($i)->is($j));
            }
        }
    }

    public function testIsEqualTo()
    {
        for ($i = DayOfWeek::MONDAY; $i <= DayOfWeek::SUNDAY; $i++) {
            for ($j = DayOfWeek::MONDAY; $j <= DayOfWeek::SUNDAY; $j++) {
                $this->assertSame($i === $j, DayOfWeek::of($i)->isEqualTo(DayOfWeek::of($j)));
            }
        }
    }

    /**
     * @dataProvider providerPlus
     *
     * @param int $dayOfWeek         The base day-of-week value.
     * @param int $plusDays          The number of days to add.
     * @param int $expectedDayOfWeek The expected day-of-week value, from 1 to 7.
     */
    public function testPlus(int $dayOfWeek, int $plusDays, int $expectedDayOfWeek)
    {
        $this->assertDayOfWeekIs($expectedDayOfWeek, DayOfWeek::of($dayOfWeek)->plus($plusDays));
    }

    /**
     * @dataProvider providerPlus
     *
     * @param int $dayOfWeek         The base day-of-week value.
     * @param int $plusDays          The number of days to add.
     * @param int $expectedDayOfWeek The expected day-of-week value, from 1 to 7.
     */
    public function testMinus(int $dayOfWeek, int $plusDays, int $expectedDayOfWeek)
    {
        $this->assertDayOfWeekIs($expectedDayOfWeek, DayOfWeek::of($dayOfWeek)->minus(-$plusDays));
    }

    /**
     * @return \Generator
     */
    public function providerPlus() : \Generator
    {
        for ($dayOfWeek = DayOfWeek::MONDAY; $dayOfWeek <= DayOfWeek::SUNDAY; $dayOfWeek++) {
            for ($plusDays = -15; $plusDays <= 15; $plusDays++) {
                $expectedDayOfWeek = $dayOfWeek + $plusDays;

                while ($expectedDayOfWeek < 1) {
                    $expectedDayOfWeek += 7;
                }
                while ($expectedDayOfWeek > 7) {
                    $expectedDayOfWeek -= 7;
                }

                yield [$dayOfWeek, $plusDays, $expectedDayOfWeek];
            }
        }
    }

    /**
     * @todo belongs to LocalDate tests
     *
     * @dataProvider providerGetDayOfWeekFromLocalDate
     *
     * @param string $localDate The local date to test, as a string.
     * @param int    $dayOfWeek The day-of-week number that matches the local date.
     */
    public function testGetDayOfWeekFromLocalDate(string $localDate, int $dayOfWeek)
    {
        $localDate = LocalDate::parse($localDate);
        $dayOfWeek = DayOfWeek::of($dayOfWeek);

        $this->assertTrue($localDate->getDayOfWeek()->isEqualTo($dayOfWeek));
    }

    /**
     * @return array
     */
    public function providerGetDayOfWeekFromLocalDate() : array
    {
        return [
            ['2000-01-01', DayOfWeek::SATURDAY],
            ['2001-01-01', DayOfWeek::MONDAY],
            ['2002-01-01', DayOfWeek::TUESDAY],
            ['2003-01-01', DayOfWeek::WEDNESDAY],
            ['2004-01-01', DayOfWeek::THURSDAY],
            ['2005-01-01', DayOfWeek::SATURDAY],
            ['2006-01-01', DayOfWeek::SUNDAY],
            ['2007-01-01', DayOfWeek::MONDAY],
            ['2008-01-01', DayOfWeek::TUESDAY],
            ['2009-01-01', DayOfWeek::THURSDAY],
            ['2010-01-01', DayOfWeek::FRIDAY],
            ['2011-01-01', DayOfWeek::SATURDAY],
            ['2012-01-01', DayOfWeek::SUNDAY],
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $dayOfWeek    The day-of-week value, from 1 to 7.
     * @param string $expectedName The expected name.
     */
    public function testToString(int $dayOfWeek, string $expectedName)
    {
        $this->assertSame($expectedName, (string) DayOfWeek::of($dayOfWeek));
    }

    /**
     * @return array
     */
    public function providerToString() : array
    {
        return [
            [DayOfWeek::MONDAY,    'Monday'],
            [DayOfWeek::TUESDAY,   'Tuesday'],
            [DayOfWeek::WEDNESDAY, 'Wednesday'],
            [DayOfWeek::THURSDAY,  'Thursday'],
            [DayOfWeek::FRIDAY,    'Friday'],
            [DayOfWeek::SATURDAY,  'Saturday'],
            [DayOfWeek::SUNDAY,    'Sunday']
        ];
    }
}
