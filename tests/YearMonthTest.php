<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use Brick\DateTime\YearMonth;

/**
 * Unit tests for class YearMonth.
 */
class YearMonthTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertYearMonthEquals(2007, 7, YearMonth::of(2007, 7));
    }

    /**
     * @dataProvider providerParse
     *
     * @param string  $text  The text to parse.
     * @param integer $year  The expected year.
     * @param integer $month The expected month.
     */
    public function testParse($text, $year, $month)
    {
        $this->assertYearMonthEquals($year, $month, YearMonth::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['2011-02', 2011, 02],
            ['0908-11', 908, 11],
            ['-0050-01', -50, 1],
            ['-12345-02', -12345, 2],
            ['12345-03', 12345, 3]
        ];
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param string $text The text to parse.
     */
    public function testParseInvalidStringThrowsException($text)
    {
        YearMonth::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            ['999-01'],
            ['-999-01'],
            ['2010-01-01'],
            [' 2010-10'],
            ['2010-10 '],
            ['2010.10']
        ];
    }

    /**
     * @dataProvider providerParseInvalidYearMonthThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param string $text The text to parse.
     */
    public function testParseInvalidYearMonthThrowsException($text)
    {
        YearMonth::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidYearMonthThrowsException()
    {
        return [
            ['2000-00'],
            ['2000-13']
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param integer $epochSecond The epoch second.
     * @param string  $timeZone    The time-zone.
     * @param integer $year        The expected year.
     * @param integer $month       The expected month.
     */
    public function testNow($epochSecond, $timeZone, $year, $month)
    {
        $previousClock = Clock::setDefault(new FixedClock(Instant::of($epochSecond)));

        $this->assertYearMonthEquals($year, $month, YearMonth::now(TimeZone::parse($timeZone)));

        Clock::setDefault($previousClock);
    }

    /**
     * @return array
     */
    public function providerNow()
    {
        return [
            [946684799, '+00:00', 1999, 12],
            [946684799, 'America/Los_Angeles', 1999, 12],
            [946684799, '+01:00', 2000, 1],
            [946684799, 'Europe/Paris', 2000, 1],
        ];
    }

    /**
     * @dataProvider providerIsLeapYear
     *
     * @param integer $year   The year to test.
     * @param integer $month  The month to test.
     * @param boolean $isLeap The expected result.
     */
    public function testIsLeapYear($year, $month, $isLeap)
    {
        $this->assertSame($isLeap, YearMonth::of($year, $month)->isLeapYear());
    }

    /**
     * @return array
     */
    public function providerIsLeapYear()
    {
        return [
            [1999, 1, false],
            [2000, 2, true],
            [2001, 3, false],
            [2002, 4, false],
            [2003, 5, false],
            [2004, 6, true]
        ];
    }

    /**
     * @dataProvider providerGetLengthOfMonth
     *
     * @param integer $year   The year.
     * @param integer $month  The month.
     * @param integer $length The expected length of month.
     */
    public function testGetLengthOfMonth($year, $month, $length)
    {
        $this->assertSame($length, YearMonth::of($year, $month)->getLengthOfMonth());
    }

    /**
     * @return array
     */
    public function providerGetLengthOfMonth()
    {
        return [
            [1999, 1, 31],
            [2000, 1, 31],
            [1999, 2, 28],
            [2000, 2, 29],
            [1999, 3, 31],
            [2000, 3, 31],
            [1999, 4, 30],
            [2000, 4, 30],
            [1999, 5, 31],
            [2000, 5, 31],
            [1999, 6, 30],
            [2000, 6, 30],
            [1999, 7, 31],
            [2000, 7, 31],
            [1999, 8, 31],
            [2000, 8, 31],
            [1999, 9, 30],
            [2000, 9, 30],
            [1999, 10, 31],
            [2000, 10, 31],
            [1999, 11, 30],
            [2000, 11, 30],
            [1999, 12, 31],
            [2000, 12, 31]
        ];
    }

    /**
     * @dataProvider providerGetLengthOfYear
     *
     * @param integer $year   The year.
     * @param integer $month  The month.
     * @param integer $length The expected length of year.
     */
    public function testGetLengthOfYear($year, $month, $length)
    {
        $this->assertSame($length, YearMonth::of($year, $month)->getLengthOfYear());
    }

    /**
     * @return array
     */
    public function providerGetLengthOfYear()
    {
        return [
            [1999, 1, 365],
            [2000, 1, 366],
            [2001, 1, 365]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $y1     The year of the base year-month.
     * @param integer $m1     The month of the base year-month.
     * @param integer $y2     The year of the year-month to compare to.
     * @param integer $m2     The month of the year-month to compare to.
     * @param integer $result The expected result.
     */
    public function testCompareTo($y1, $m1, $y2, $m2, $result)
    {
        $this->assertSame($result, YearMonth::of($y1, $m1)->compareTo(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $y1     The year of the base year-month.
     * @param integer $m1     The month of the base year-month.
     * @param integer $y2     The year of the year-month to compare to.
     * @param integer $m2     The month of the year-month to compare to.
     * @param integer $result The comparison result.
     */
    public function testIsEqualTo($y1, $m1, $y2, $m2, $result)
    {
        $this->assertSame($result == 0, YearMonth::of($y1, $m1)->isEqualTo(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $y1     The year of the base year-month.
     * @param integer $m1     The month of the base year-month.
     * @param integer $y2     The year of the year-month to compare to.
     * @param integer $m2     The month of the year-month to compare to.
     * @param integer $result The comparison result.
     */
    public function testIsBefore($y1, $m1, $y2, $m2, $result)
    {
        $this->assertSame($result == -1, YearMonth::of($y1, $m1)->isBefore(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $y1     The year of the base year-month.
     * @param integer $m1     The month of the base year-month.
     * @param integer $y2     The year of the year-month to compare to.
     * @param integer $m2     The month of the year-month to compare to.
     * @param integer $result The comparison result.
     */
    public function testIsAfter($y1, $m1, $y2, $m2, $result)
    {
        $this->assertSame($result == 1, YearMonth::of($y1, $m1)->isAfter(YearMonth::of($y2, $m2)));
    }

    /**
     * @return array
     */
    public function providerCompareTo()
    {
        return [
            [2001, 1, 2001, 1,  0],
            [2001, 1, 2001, 2, -1],
            [2001, 1, 2002, 1, -1],
            [2001, 1, 2002, 2, -1],
            [2001, 2, 2001, 1,  1],
            [2001, 2, 2001, 2,  0],
            [2001, 2, 2002, 1, -1],
            [2001, 2, 2002, 2, -1],
            [2002, 1, 2001, 1,  1],
            [2002, 1, 2001, 2,  1],
            [2002, 1, 2002, 1,  0],
            [2002, 1, 2002, 2, -1],
            [2002, 2, 2001, 1,  1],
            [2002, 2, 2001, 2,  1],
            [2002, 2, 2002, 1,  1],
            [2002, 2, 2002, 2,  0],
        ];
    }

    public function testWithYear()
    {
        $this->assertYearMonthEquals(2001, 5, YearMonth::of(2000, 5)->withYear(2001));
    }

    public function testWithMonth()
    {
        $this->assertYearMonthEquals(2000, 12, YearMonth::of(2000, 1)->withMonth(12));
    }

    public function testAtDay()
    {
        $this->assertLocalDateEquals(2001, 2, 3, YearMonth::of(2001, 02)->atDay(3));
    }

    public function testToString()
    {
        $this->assertSame('2013-09', (string) YearMonth::of(2013, 9));
    }
}
