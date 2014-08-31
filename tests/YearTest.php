<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\MonthDay;
use Brick\DateTime\TimeZone;
use Brick\DateTime\Year;

/**
 * Unit tests for class Year.
 */
class YearTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertYearEquals(1987, Year::of(1987));
    }

    /**
     * @dataProvider providerOfInvalidYearThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidYear
     */
    public function testOfInvalidYearThrowsException($invalidYear)
    {
        Year::of($invalidYear);
    }

    /**
     * @return array
     */
    public function providerOfInvalidYearThrowsException()
    {
        return [
            [~PHP_INT_MAX],
            [PHP_INT_MAX],
            [-1000000000],
            [1000000000]
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param integer $epochSecond  The epoch second to set the clock time to.
     * @param string  $timeZone     The time-zone to get the current year in.
     * @param integer $expectedYear The expected year.
     */
    public function testNow($epochSecond, $timeZone, $expectedYear)
    {
        $this->setClockTime($epochSecond);
        $this->assertYearEquals($expectedYear, Year::now(TimeZone::parse($timeZone)));
    }

    /**
     * @return array
     */
    public function providerNow()
    {
        return [
            [1388534399, '-01:00', 2013],
            [1388534399, '+00:00', 2013],
            [1388534399, '+01:00', 2014],
            [1388534400, '-01:00', 2013],
            [1388534400, '+00:00', 2014],
            [1388534400, '+01:00', 2014],
        ];
    }

    /**
     * @dataProvider providerIsLeap
     *
     * @param integer $year   The year to test.
     * @param boolean $isLeap Whether the year is a leap year.
     */
    public function testIsLeap($year, $isLeap)
    {
        $this->assertSame($isLeap, Year::of($year)->isLeap());
    }

    /**
     * @return array
     */
    public function providerIsLeap()
    {
        return [
            [1595, false],
            [1596, true],
            [1597, false],
            [1598, false],
            [1599, false],
            [1600, true],
            [1601, false],
            [1602, false],
            [1603, false],
            [1604, true],
            [1605, false],
            [1695, false],
            [1696, true],
            [1697, false],
            [1698, false],
            [1699, false],
            [1700, false],
            [1701, false],
            [1702, false],
            [1703, false],
            [1704, true],
            [1705, false],
            [1795, false],
            [1796, true],
            [1797, false],
            [1798, false],
            [1799, false],
            [1800, false],
            [1801, false],
            [1802, false],
            [1803, false],
            [1804, true],
            [1805, false],
            [1895, false],
            [1896, true],
            [1897, false],
            [1898, false],
            [1899, false],
            [1900, false],
            [1901, false],
            [1902, false],
            [1903, false],
            [1904, true],
            [1905, false],
            [1995, false],
            [1996, true],
            [1997, false],
            [1998, false],
            [1999, false],
            [2000, true],
            [2001, false],
            [2002, false],
            [2003, false],
            [2004, true],
            [2005, false]
        ];
    }

    /**
     * @dataProvider providerIsValidMonthDay
     *
     * @param integer $year    The base year.
     * @param integer $month   The month of the month-day to test.
     * @param integer $day     The day-of-month of the month-day to test.
     * @param boolean $isValid Whether the month-day is expected to be valid.
     */
    public function testIsValidMonthDay($year, $month, $day, $isValid)
    {
        $this->assertSame($isValid, Year::of($year)->isValidMonthDay(MonthDay::of($month, $day)));
    }

    /**
     * @return array
     */
    public function providerIsValidMonthDay()
    {
        return [
            [1999, 1, 31, true],
            [2000, 1, 31, true],
            [2001, 1, 31, true],
            [1999, 2, 28, true],
            [2000, 2, 28, true],
            [2001, 2, 28, true],
            [1999, 2, 29, false],
            [2000, 2, 29, true],
            [2001, 2, 29, false]
        ];
    }

    /**
     * @dataProvider providerGetLength
     *
     * @param integer $year   The year to test.
     * @param integer $length The expected length of year in days.
     */
    public function testGetLength($year, $length)
    {
        $this->assertSame($length, Year::of($year)->getLength());
    }

    /**
     * @return array
     */
    public function providerGetLength()
    {
        return [
            [1595, 365],
            [1596, 366],
            [1597, 365],
            [1598, 365],
            [1599, 365],
            [1600, 366],
            [1601, 365],
            [1602, 365],
            [1603, 365],
            [1604, 366],
            [1605, 365],
            [1695, 365],
            [1696, 366],
            [1697, 365],
            [1698, 365],
            [1699, 365],
            [1700, 365],
            [1701, 365],
            [1702, 365],
            [1703, 365],
            [1704, 366],
            [1705, 365],
            [1795, 365],
            [1796, 366],
            [1797, 365],
            [1798, 365],
            [1799, 365],
            [1800, 365],
            [1801, 365],
            [1802, 365],
            [1803, 365],
            [1804, 366],
            [1805, 365],
            [1895, 365],
            [1896, 366],
            [1897, 365],
            [1898, 365],
            [1899, 365],
            [1900, 365],
            [1901, 365],
            [1902, 365],
            [1903, 365],
            [1904, 366],
            [1905, 365],
            [1995, 365],
            [1996, 366],
            [1997, 365],
            [1998, 365],
            [1999, 365],
            [2000, 366],
            [2001, 365],
            [2002, 365],
            [2003, 365],
            [2004, 366],
            [2005, 365]
        ];
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $year
     * @param integer $plusYears
     * @param integer $expectedYear
     */
    public function testPlus($year, $plusYears, $expectedYear)
    {
        $this->assertYearEquals($expectedYear, Year::of($year)->plus($plusYears));
    }

    /**
     * @return array
     */
    public function providerPlus()
    {
        return [
            [2014, 0, 2014],
            [2014, 16, 2030],
            [2014, -16, 1998]
        ];
    }

    /**
     * @dataProvider providerMinus
     *
     * @param integer $year
     * @param integer $minusYears
     * @param integer $expectedYear
     */
    public function testMinus($year, $minusYears, $expectedYear)
    {
        $this->assertYearEquals($expectedYear, Year::of($year)->minus($minusYears));
    }

    /**
     * @return array
     */
    public function providerMinus()
    {
        return [
            [2014, 0, 2014],
            [2014, 16, 1998],
            [2014, -16, 2030]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $year1 The base year.
     * @param integer $year2 The year to compare to.
     * @param integer $cmp   The comparison value.
     */
    public function testCompareTo($year1, $year2, $cmp)
    {
        $this->assertSame($cmp, Year::of($year1)->compareTo(Year::of($year2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $year1 The base year.
     * @param integer $year2 The year to compare to.
     * @param integer $cmp   The comparison value.
     */
    public function testIsEqualTo($year1, $year2, $cmp)
    {
        $this->assertSame($cmp === 0, Year::of($year1)->isEqualTo(Year::of($year2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $year1 The base year.
     * @param integer $year2 The year to compare to.
     * @param integer $cmp   The comparison value.
     */
    public function testIsAfter($year1, $year2, $cmp)
    {
        $this->assertSame($cmp === 1, Year::of($year1)->isAfter(Year::of($year2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $year1 The base year.
     * @param integer $year2 The year to compare to.
     * @param integer $cmp   The comparison value.
     */
    public function testIsBefore($year1, $year2, $cmp)
    {
        $this->assertSame($cmp === -1, Year::of($year1)->isBefore(Year::of($year2)));
    }

    /**
     * @return array
     */
    public function providerCompareTo()
    {
        return [
            [-1999, -1999, 0],
            [-1999, -1, -1],
            [-1999, 0, -1],
            [-1999, 1, -1],
            [-1999, 1999, -1],
            [-1, -1999, 1],
            [-1, -1, 0],
            [-1, 0, -1],
            [-1, 1, -1],
            [-1, 1999, -1],
            [0, -1999, 1],
            [0, -1, 1],
            [0, 0, 0],
            [0, 1, -1],
            [0, 1999, -1],
            [1, -1999, 1],
            [1, -1, 1],
            [1, 0, 1],
            [1, 1, 0],
            [1, 1999, -1],
            [1999, -1999, 1],
            [1999, -1, 1],
            [1999, 0, 1],
            [1999, 1, 1],
            [1999, 1999, 0],
        ];
    }

    /**
     * @dataProvider providerAtDay
     *
     * @param integer $year      The base year.
     * @param integer $dayOfYear The day-of-year to apply.
     * @param integer $month     The expected month of the resulting date.
     * @param integer $day       The expected day-of-month of the resulting date.
     */
    public function testAtDay($year, $dayOfYear, $month, $day)
    {
        $this->assertLocalDateEquals($year, $month, $day, Year::of($year)->atDay($dayOfYear));
    }

    /**
     * @return array
     */
    public function providerAtDay()
    {
        return [
            [2007, 59, 2, 28],
            [2007, 60, 3, 1],
            [2007, 365, 12, 31],
            [2008, 59, 2, 28],
            [2008, 60, 2, 29],
            [2008, 61, 3, 1],
            [2008, 365, 12, 30],
            [2008, 366, 12, 31],
        ];
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testAtInvalidDayThrowsException()
    {
        Year::of(2007)->atDay(366);
    }

    public function testAtMonth()
    {
        $this->assertYearMonthEquals(2014, 7, Year::of(2014)->atMonth(7));
    }

    /**
     * @dataProvider providerAtInvalidMonthThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidMonth
     */
    public function testAtInvalidMonthThrowsException($invalidMonth)
    {
        Year::of(2000)->atMonth($invalidMonth);
    }

    /**
     * @return array
     */
    public function providerAtInvalidMonthThrowsException()
    {
        return [
            [-1],
            [0],
            [13]
        ];
    }

    /**
     * @dataProvider providerAtMonthDay
     *
     * @param integer $year        The base year.
     * @param integer $month       The month-of-year of the month-day to apply.
     * @param integer $day         The day-of-month of the month-day to apply.
     * @param integer $expectedDay The expected day of the resulting date.
     */
    public function testAtMonthDay($year, $month, $day, $expectedDay)
    {
        $monthDay = MonthDay::of($month, $day);
        $this->assertLocalDateEquals($year, $month, $expectedDay, Year::of($year)->atMonthDay($monthDay));
    }

    /**
     * @return array
     */
    public function providerAtMonthDay()
    {
        return [
            [2007, 2, 28, 28],
            [2007, 2, 29, 28],
            [2008, 2, 28, 28],
            [2008, 2, 29, 29],
            [2009, 2, 28, 28],
            [2009, 2, 29, 28],
            [2010, 1, 31, 31]
        ];
    }

    public function testToString()
    {
        $this->assertSame('1987', (string) Year::of(1987));
    }
}
