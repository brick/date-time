<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\DayOfWeek;
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
     * @param integer $expectedValue     The expected value of the constant.
     * @param integer $dayOfWeekConstant The day-of-week constant.
     */
    public function testConstants($expectedValue, $dayOfWeekConstant)
    {
        $this->assertSame($expectedValue, $dayOfWeekConstant);
    }

    /**
     * @return array
     */
    public function providerConstants()
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
        $this->assertDayOfWeekEquals(5, DayOfWeek::of(5));
    }

    /**
     * @dataProvider providerOfInvalidDayOfWeekThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $dayOfWeek
     */
    public function testOfInvalidDayOfWeekThrowsException($dayOfWeek)
    {
        DayOfWeek::of($dayOfWeek);
    }

    /**
     * @return array
     */
    public function providerOfInvalidDayOfWeekThrowsException()
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
     * @param integer $epochSecond       The epoch second to set the clock time to.
     * @param string  $timeZone          The time-zone to get the current day-of-week in.
     * @param integer $expectedDayOfWeek The expected day-of-week, from 1 to 7.
     */
    public function testNow($epochSecond, $timeZone, $expectedDayOfWeek)
    {
        $this->setClockTime($epochSecond);
        $this->assertDayOfWeekEquals($expectedDayOfWeek, DayOfWeek::now(TimeZone::parse($timeZone)));
    }

    /**
     * @return array
     */
    public function providerNow()
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

    public function testGetAll()
    {
        for ($day = DayOfWeek::MONDAY; $day <= DayOfWeek::SUNDAY; $day++) {
            $dayOfWeek = DayOfWeek::of($day);

            foreach (DayOfWeek::getAll($dayOfWeek) as $dow) {
                $this->assertTrue($dow->isEqualTo($dayOfWeek));
                $dayOfWeek = $dayOfWeek->plus(1);
            }
        }
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
     * @param integer $dayOfWeek         The base day-of-week value.
     * @param integer $plusDays          The number of days to add.
     * @param integer $expectedDayOfWeek The expected day-of-week value, from 1 to 7.
     */
    public function testPlus($dayOfWeek, $plusDays, $expectedDayOfWeek)
    {
        $this->assertDayOfWeekEquals($expectedDayOfWeek, DayOfWeek::of($dayOfWeek)->plus($plusDays));
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $dayOfWeek         The base day-of-week value.
     * @param integer $plusDays          The number of days to add.
     * @param integer $expectedDayOfWeek The expected day-of-week value, from 1 to 7.
     */
    public function testMinus($dayOfWeek, $plusDays, $expectedDayOfWeek)
    {
        $this->assertDayOfWeekEquals($expectedDayOfWeek, DayOfWeek::of($dayOfWeek)->minus(-$plusDays));
    }

    /**
     * @return \Generator
     */
    public function providerPlus()
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
     * @param string  $localDate The local date to test, as a string.
     * @param integer $dayOfWeek The day-of-week number that matches the local date.
     */
    public function testGetDayOfWeekFromLocalDate($localDate, $dayOfWeek)
    {
        $localDate = LocalDate::parse($localDate);
        $dayOfWeek = DayOfWeek::of($dayOfWeek);

        $this->assertTrue($localDate->getDayOfWeek()->isEqualTo($dayOfWeek));
    }

    /**
     * @return array
     */
    public function providerGetDayOfWeekFromLocalDate()
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
     * @param integer $dayOfWeek    The day-of-week value, from 1 to 7.
     * @param string  $expectedName The expected name.
     */
    public function testToString($dayOfWeek, $expectedName)
    {
        $this->assertSame($expectedName, (string) DayOfWeek::of($dayOfWeek));
    }

    /**
     * @return array
     */
    public function providerToString()
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
