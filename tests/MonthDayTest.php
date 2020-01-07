<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

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
     * @param int $month
     * @param int $day
     */
    public function testOf(int $month, int $day)
    {
        $this->assertMonthDayIs($month, $day, MonthDay::of($month, $day));
    }

    /**
     * @return array
     */
    public function providerOf() : array
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
     * @param int $month
     * @param int $day
     */
    public function testOfThrowsExceptionOnInvalidMonthDay(int $month, int $day)
    {
        MonthDay::of($month, $day);
    }

    /**
     * @return array
     */
    public function providerOfThrowsExceptionOnInvalidMonthDay() : array
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
     * @param string $text  The text to parse.
     * @param int    $month The expected month.
     * @param int    $day   The expected day.
     */
    public function testParse(string $text, int $month, int $day)
    {
        $this->assertMonthDayIs($month, $day, MonthDay::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse() : array
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
    public function testParseInvalidStringThrowsException(string $text)
    {
        MonthDay::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException() : array
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
    public function testParseInvalidDateThrowsException(string $text)
    {
        MonthDay::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidDateThrowsException() : array
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
     * @param int    $epochSecond The epoch second.
     * @param string $timeZone    The time-zone.
     * @param int    $month       The expected month.
     * @param int    $day         The expected day.
     */
    public function testNow(int $epochSecond, string $timeZone, int $month, int $day)
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        $this->assertMonthDayIs($month, $day, MonthDay::now(TimeZone::parse($timeZone), $clock));
    }

    /**
     * @return array
     */
    public function providerNow() : array
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
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    public function testCompareTo(int $m1, int $d1, int $m2, int $d2, int $result)
    {
        $this->assertSame($result, MonthDay::of($m1, $d1)->compareTo(MonthDay::of($m2, $d2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    public function testIsEqualTo(int $m1, int $d1, int $m2, int $d2, int $result)
    {
        $this->assertSame($result == 0, MonthDay::of($m1, $d1)->isEqualTo(MonthDay::of($m2, $d2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    public function testIsBefore(int $m1, int $d1, int $m2, int $d2, int $result)
    {
        $this->assertSame($result == -1, MonthDay::of($m1, $d1)->isBefore(MonthDay::of($m2, $d2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    public function testIsAfter(int $m1, int $d1, int $m2, int $d2, int $result)
    {
        $this->assertSame($result == 1, MonthDay::of($m1, $d1)->isAfter(MonthDay::of($m2, $d2)));
    }

    /**
     * @return array
     */
    public function providerCompareTo() : array
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
     * @param int  $month   The month of the month-day to test.
     * @param int  $day     The day of the month-day to test.
     * @param int  $year    The year to test against.
     * @param bool $isValid The expected result.
     */
    public function testIsValidYear(int $month, int $day, int $year, bool $isValid)
    {
        $this->assertSame($isValid, MonthDay::of($month, $day)->isValidYear($year));
    }

    /**
     * @return array
     */
    public function providerIsValidYear() : array
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
     * @param int $month       The month of the base month-day to test.
     * @param int $day         The day of base the month-day to test.
     * @param int $newMonth    The new month to apply.
     * @param int $expectedDay The expected day of the resulting month-day.
     */
    public function testWithMonth(int $month, int $day, int $newMonth, int $expectedDay)
    {
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withMonth($newMonth);

        $this->assertMonthDayIs($month, $day, $monthDay);
        $this->assertMonthDayIs($newMonth, $expectedDay, $newMonthDay);
    }

    /**
     * @return array
     */
    public function providerWithMonth() : array
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
     * @param int $month    The month of the base month-day to test.
     * @param int $day      The day of base the month-day to test.
     * @param int $newMonth The new month to apply.
     */
    public function testWithInvalidMonthThrowsException(int $month, int $day, int $newMonth)
    {
        MonthDay::of($month, $day)->withMonth($newMonth);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMonthThrowsException() : array
    {
        return [
            [1, 1, 0],
            [12, 31, 13]
        ];
    }

    public function testWithDayWithSameDay()
    {
        $month = 1;
        $day = 20;
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withDay($day);

        $this->assertMonthDayIs($month, $day, $monthDay);
        $this->assertMonthDayIs($month, $day, $newMonthDay);
    }

    /**
     * @dataProvider providerWithDay
     *
     * @param int $month  The month of the base month-day to test.
     * @param int $day    The day of base the month-day to test.
     * @param int $newDay The new day to apply.
     */
    public function testWithDay(int $month, int $day, int $newDay)
    {
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withDay($newDay);

        $this->assertMonthDayIs($month, $day, $monthDay);
        $this->assertMonthDayIs($month, $newDay, $newMonthDay);
    }

    /**
     * @return array
     */
    public function providerWithDay() : array
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
     * @param int $month    The month of the base month-day to test.
     * @param int $day      The day of base the month-day to test.
     * @param int $newDay   The new day to apply.
     */
    public function testWithInvalidDayThrowsException(int $month, int $day, int $newDay)
    {
        MonthDay::of($month, $day)->withDay($newDay);
    }

    /**
     * @return array
     */
    public function providerWithInvalidDayThrowsException() : array
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
     * @param int $month       The month of the base month-day.
     * @param int $day         The day of the base month-day.
     * @param int $year        The year to combine with the month-day.
     * @param int $expectedDay The expected day of the resulting date.
     */
    public function testAtYear(int $month, int $day, int $year, int $expectedDay)
    {
        $this->assertLocalDateIs($year, $month, $expectedDay, MonthDay::of($month, $day)->atYear($year));
    }

    /**
     * @return array
     */
    public function providerAtYear() : array
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
     * @param int $year
     */
    public function testAtInvalidYearThrowsException(int $year)
    {
        MonthDay::of(1, 1)->atYear($year);
    }

    /**
     * @return array
     */
    public function providerAtInvalidYearThrowsException() : array
    {
        return [
            [-1234567890],
            [1234567890]
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $month  The month of the month-day to test.
     * @param int    $day    The day of the month-day to test.
     * @param string $string The expected result string.
     */
    public function testJsonSerialize(int $month, int $day, string $string)
    {
        $this->assertSame(json_encode($string), json_encode(MonthDay::of($month, $day)));
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $month  The month of the month-day to test.
     * @param int    $day    The day of the month-day to test.
     * @param string $string The expected result string.
     */
    public function testToString(int $month, int $day, string $string)
    {
        $this->assertSame($string, (string) MonthDay::of($month, $day));
    }

    /**
     * @return array
     */
    public function providerToString() : array
    {
        return [
            [1, 1, '--01-01'],
            [1, 31, '--01-31'],
            [12, 1, '--12-01'],
            [12, 31, '--12-31']
        ];
    }
}
