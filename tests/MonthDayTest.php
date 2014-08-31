<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\MonthDay;
use Brick\DateTime\TimeZone;

/**
 * Unit tests for class MonthDay.
 */
class MonthDayTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     *
     * @param integer $month
     * @param integer $day
     */
    public function testOf($month, $day)
    {
        $this->assertMonthDayEquals($month, $day, MonthDay::of($month, $day));
    }

    /**
     * @return array
     */
    public function providerOf()
    {
        return [
            [1, 1],
            [1, 31],
            [2, 29],
            [3, 31],
            [4, 30],
            [5, 31],
            [6, 30],
            [7, 31],
            [8, 31],
            [9, 30],
            [10, 31],
            [11, 30],
            [12, 31]
        ];
    }

    /**
     * @dataProvider providerOfThrowsExceptionOnInvalidMonthDay
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $month
     * @param integer $day
     */
    public function testOfThrowsExceptionOnInvalidMonthDay($month, $day)
    {
        MonthDay::of($month, $day);
    }

    /**
     * @return array
     */
    public function providerOfThrowsExceptionOnInvalidMonthDay()
    {
        return [
            [0, 1],
            [1, 0],
            [13, 1],
            [1, 32],
            [2, 30],
            [3, 32],
            [4, 31],
            [5, 32],
            [6, 31],
            [7, 32],
            [8, 32],
            [9, 31],
            [10, 32],
            [11, 32],
            [12, 32]
        ];
    }

    /**
     * @dataProvider providerParse
     *
     * @param string  $text  The text to parse.
     * @param integer $month The expected month.
     * @param integer $day   The expected day.
     */
    public function testParse($text, $month, $day)
    {
        $this->assertMonthDayEquals($month, $day, MonthDay::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['--01-01', 1, 1],
            ['--02-29', 2, 29],
            ['--12-31', 12, 31]
        ];
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsException($text)
    {
        MonthDay::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            ['01-01'],
            ['-01-01'],
            ['---01-01'],
            [' --01-01'],
            ['--01-01 '],
            ['--1-1'],
            ['--1-01'],
            ['--01-1'],
            ['--123-01'],
            ['--01-123'],
            ['--1X-01'],
            ['--01-1X']
        ];
    }

    /**
     * @dataProvider providerParseInvalidDateThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param string $text
     */
    public function testParseInvalidDateThrowsException($text)
    {
        MonthDay::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidDateThrowsException()
    {
        return [
            ['--00-01'],
            ['--01-00'],
            ['--01-32'],
            ['--02-30']
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param integer $epochSecond The epoch second.
     * @param string  $timeZone    The time-zone.
     * @param integer $month       The expected month.
     * @param integer $day         The expected day.
     */
    public function testNow($epochSecond, $timeZone, $month, $day)
    {
        $previousClock = Clock::setDefault(new FixedClock(Instant::of($epochSecond)));

        $this->assertMonthDayEquals($month, $day, MonthDay::now(TimeZone::parse($timeZone)));

        Clock::setDefault($previousClock);
    }

    /**
     * @return array
     */
    public function providerNow()
    {
        return [
            [946684799, '+00:00', 12, 31],
            [946684799, 'America/Los_Angeles', 12, 31],
            [946684799, '+01:00', 1, 1],
            [946684799, 'Europe/Paris', 1, 1],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $m1     The month of the base month-day.
     * @param integer $d1     The day of the base month-day.
     * @param integer $m2     The month of the month-day to compare to.
     * @param integer $d2     The day of the month-day to compare to.
     * @param integer $result The expected result.
     */
    public function testCompareTo($m1, $d1, $m2, $d2, $result)
    {
        $this->assertSame($result, MonthDay::of($m1, $d1)->compareTo(MonthDay::of($m2, $d2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $m1     The month of the base month-day.
     * @param integer $d1     The day of the base month-day.
     * @param integer $m2     The month of the month-day to compare to.
     * @param integer $d2     The day of the month-day to compare to.
     * @param integer $result The expected result.
     */
    public function testIsEqualTo($m1, $d1, $m2, $d2, $result)
    {
        $this->assertSame($result == 0, MonthDay::of($m1, $d1)->isEqualTo(MonthDay::of($m2, $d2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $m1     The month of the base month-day.
     * @param integer $d1     The day of the base month-day.
     * @param integer $m2     The month of the month-day to compare to.
     * @param integer $d2     The day of the month-day to compare to.
     * @param integer $result The expected result.
     */
    public function testIsBefore($m1, $d1, $m2, $d2, $result)
    {
        $this->assertSame($result == -1, MonthDay::of($m1, $d1)->isBefore(MonthDay::of($m2, $d2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $m1     The month of the base month-day.
     * @param integer $d1     The day of the base month-day.
     * @param integer $m2     The month of the month-day to compare to.
     * @param integer $d2     The day of the month-day to compare to.
     * @param integer $result The expected result.
     */
    public function testIsAfter($m1, $d1, $m2, $d2, $result)
    {
        $this->assertSame($result == 1, MonthDay::of($m1, $d1)->isAfter(MonthDay::of($m2, $d2)));
    }

    /**
     * @return array
     */
    public function providerCompareTo()
    {
        return [
            [1, 1, 1, 1,  0],
            [1, 1, 1, 2, -1],
            [1, 1, 2, 1, -1],
            [1, 1, 2, 2, -1],
            [1, 2, 1, 1,  1],
            [1, 2, 1, 2,  0],
            [1, 2, 2, 1, -1],
            [1, 2, 2, 2, -1],
            [2, 1, 1, 1,  1],
            [2, 1, 1, 2,  1],
            [2, 1, 2, 1,  0],
            [2, 1, 2, 2, -1],
            [2, 2, 1, 1,  1],
            [2, 2, 1, 2,  1],
            [2, 2, 2, 1,  1],
            [2, 2, 2, 2,  0],
        ];
    }

    /**
     * @dataProvider providerIsValidYear
     *
     * @param integer $month   The month of the month-day to test.
     * @param integer $day     The day of the month-day to test.
     * @param integer $year    The year to test against.
     * @param boolean $isValid The expected result.
     */
    public function testIsValidYear($month, $day, $year, $isValid)
    {
        $this->assertSame($isValid, MonthDay::of($month, $day)->isValidYear($year));
    }

    /**
     * @return array
     */
    public function providerIsValidYear()
    {
        return [
            [1, 1, 2000, true],
            [1, 31, 2000, true],
            [2, 1, 2000, true],
            [2, 28, 2000, true],
            [12, 1, 2000, true],
            [12, 31, 2000, true],
            [1, 1, 2001, true],
            [1, 31, 2001, true],
            [2, 1, 2001, true],
            [2, 28, 2001, true],
            [12, 1, 2001, true],
            [12, 31, 2001, true],
            [2, 29, 2000, true],
            [2, 29, 2001, false],
            [2, 29, 2002, false],
            [2, 29, 2003, false],
            [2, 29, 2004, true]
        ];
    }

    /**
     * @dataProvider providerWithMonth
     *
     * @param integer $month       The month of the base month-day to test.
     * @param integer $day         The day of base the month-day to test.
     * @param integer $newMonth    The new month to apply.
     * @param integer $expectedDay The expected day of the resulting month-day.
     */
    public function testWithMonth($month, $day, $newMonth, $expectedDay)
    {
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withMonth($newMonth);

        $this->assertMonthDayEquals($month, $day, $monthDay);
        $this->assertMonthDayEquals($newMonth, $expectedDay, $newMonthDay);
    }

    /**
     * @return array
     */
    public function providerWithMonth()
    {
        return [
            [1, 1, 1, 1],
            [1, 1, 12, 1],
            [1, 31, 12, 31],
            [1, 31, 11, 30],
            [1, 31, 10, 31],
            [1, 31, 9, 30],
            [1, 31, 2, 29],
            [1, 30, 2, 29],
            [1, 29, 2, 29],
            [1, 28, 2, 28],
            [2, 29, 2, 29],
            [2, 29, 3, 29],
            [2, 29, 4, 29],
            [11, 30, 11, 30],
            [11, 30, 12, 30],
            [11, 30, 2, 29]
        ];
    }

    /**
     * @dataProvider providerWithInvalidMonthThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $month    The month of the base month-day to test.
     * @param integer $day      The day of base the month-day to test.
     * @param integer $newMonth The new month to apply.
     */
    public function testWithInvalidMonthThrowsException($month, $day, $newMonth)
    {
        MonthDay::of($month, $day)->withMonth($newMonth);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMonthThrowsException()
    {
        return [
            [1, 1, 0],
            [12, 31, 13]
        ];
    }

    /**
     * @dataProvider providerWithDay
     *
     * @param integer $month  The month of the base month-day to test.
     * @param integer $day    The day of base the month-day to test.
     * @param integer $newDay The new day to apply.
     */
    public function testWithDay($month, $day, $newDay)
    {
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withDay($newDay);

        $this->assertMonthDayEquals($month, $day, $monthDay);
        $this->assertMonthDayEquals($month, $newDay, $newMonthDay);
    }

    /**
     * @return array
     */
    public function providerWithDay()
    {
        return [
            [1, 1, 31],
            [1, 31, 1],
            [12, 1, 31],
            [12, 31, 1],
            [2, 1, 29],
            [2, 29, 1]
        ];
    }

    /**
     * @dataProvider providerWithInvalidDayThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $month    The month of the base month-day to test.
     * @param integer $day      The day of base the month-day to test.
     * @param integer $newDay   The new day to apply.
     */
    public function testWithInvalidDayThrowsException($month, $day, $newDay)
    {
        MonthDay::of($month, $day)->withDay($newDay);
    }

    /**
     * @return array
     */
    public function providerWithInvalidDayThrowsException()
    {
        return [
            [12, 31, 32],
            [12, 1, 0],
            [11, 30, 31],
            [11, 1, 0],
            [10, 31, 32],
            [10, 1, 0],
            [9, 30, 31],
            [9, 1, 0],
            [8, 31, 32],
            [8, 1, 0],
            [7, 31, 32],
            [7, 1, 0],
            [6, 30, 31],
            [6, 1, 0],
            [5, 31, 32],
            [5, 1, 0],
            [4, 30, 31],
            [4, 1, 0],
            [3, 31, 32],
            [3, 1, 0],
            [2, 29, 30],
            [2, 1, 0],
            [1, 31, 32],
            [1, 1, 0],
        ];
    }

    /**
     * @dataProvider providerAtYear
     *
     * @param integer $month       The month of the base month-day.
     * @param integer $day         The day of the base month-day.
     * @param integer $year        The year to combine with the month-day.
     * @param integer $expectedDay The expected day of the resulting date.
     */
    public function testAtYear($month, $day, $year, $expectedDay)
    {
        $this->assertLocalDateEquals($year, $month, $expectedDay, MonthDay::of($month, $day)->atYear($year));
    }

    /**
     * @return array
     */
    public function providerAtYear()
    {
        return [
            [1, 31, 2000, 31],
            [4, 30, 2001, 30],
            [2, 28, 2000, 28],
            [2, 29, 2000, 29],
            [2, 28, 2001, 28],
            [2, 29, 2001, 28],
        ];
    }

    /**
     * @dataProvider providerAtInvalidYearThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $year
     */
    public function testAtInvalidYearThrowsException($year)
    {
        MonthDay::of(1, 1)->atYear($year);
    }

    /**
     * @return array
     */
    public function providerAtInvalidYearThrowsException()
    {
        return [
            [-1234567890],
            [1234567890]
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param integer $month  The month of the month-day to test.
     * @param integer $day    The day of the month-day to test.
     * @param string  $string The expected result string.
     */
    public function testToString($month, $day, $string)
    {
        $this->assertSame($string, (string) MonthDay::of($month, $day));
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [1, 1, '--01-01'],
            [1, 31, '--01-31'],
            [12, 1, '--12-01'],
            [12, 31, '--12-31']
        ];
    }
}
