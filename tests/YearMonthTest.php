<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

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
        $this->assertYearMonthIs(2007, 7, YearMonth::of(2007, 7));
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text  The text to parse.
     * @param int    $year  The expected year.
     * @param int    $month The expected month.
     */
    public function testParse(string $text, int $year, int $month)
    {
        $this->assertYearMonthIs($year, $month, YearMonth::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse() : array
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
    public function testParseInvalidStringThrowsException(string $text)
    {
        YearMonth::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException() : array
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
    public function testParseInvalidYearMonthThrowsException(string $text)
    {
        YearMonth::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidYearMonthThrowsException() : array
    {
        return [
            ['2000-00'],
            ['2000-13']
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param int    $epochSecond The epoch second.
     * @param string $timeZone    The time-zone.
     * @param int    $year        The expected year.
     * @param int    $month       The expected month.
     */
    public function testNow(int $epochSecond, string $timeZone, int $year, int $month)
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        $this->assertYearMonthIs($year, $month, YearMonth::now(TimeZone::parse($timeZone), $clock));
    }

    /**
     * @return array
     */
    public function providerNow() : array
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
     * @param int  $year   The year to test.
     * @param int  $month  The month to test.
     * @param bool $isLeap The expected result.
     */
    public function testIsLeapYear(int $year, int $month, bool $isLeap)
    {
        $this->assertSame($isLeap, YearMonth::of($year, $month)->isLeapYear());
    }

    /**
     * @return array
     */
    public function providerIsLeapYear() : array
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
     * @param int $year   The year.
     * @param int $month  The month.
     * @param int $length The expected length of month.
     */
    public function testGetLengthOfMonth(int $year, int $month, int $length)
    {
        $this->assertSame($length, YearMonth::of($year, $month)->getLengthOfMonth());
    }

    /**
     * @return array
     */
    public function providerGetLengthOfMonth() : array
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
     * @param int $year   The year.
     * @param int $month  The month.
     * @param int $length The expected length of year.
     */
    public function testGetLengthOfYear(int $year, int $month, int $length)
    {
        $this->assertSame($length, YearMonth::of($year, $month)->getLengthOfYear());
    }

    /**
     * @return array
     */
    public function providerGetLengthOfYear() : array
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
     * @param int $y1     The year of the base year-month.
     * @param int $m1     The month of the base year-month.
     * @param int $y2     The year of the year-month to compare to.
     * @param int $m2     The month of the year-month to compare to.
     * @param int $result The expected result.
     */
    public function testCompareTo(int $y1, int $m1, int $y2, int $m2, int $result)
    {
        $this->assertSame($result, YearMonth::of($y1, $m1)->compareTo(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $y1     The year of the base year-month.
     * @param int $m1     The month of the base year-month.
     * @param int $y2     The year of the year-month to compare to.
     * @param int $m2     The month of the year-month to compare to.
     * @param int $result The comparison result.
     */
    public function testIsEqualTo(int $y1, int $m1, int $y2, int $m2, int $result)
    {
        $this->assertSame($result == 0, YearMonth::of($y1, $m1)->isEqualTo(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $y1     The year of the base year-month.
     * @param int $m1     The month of the base year-month.
     * @param int $y2     The year of the year-month to compare to.
     * @param int $m2     The month of the year-month to compare to.
     * @param int $result The comparison result.
     */
    public function testIsBefore(int $y1, int $m1, int $y2, int $m2, int $result)
    {
        $this->assertSame($result == -1, YearMonth::of($y1, $m1)->isBefore(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $y1     The year of the base year-month.
     * @param int $m1     The month of the base year-month.
     * @param int $y2     The year of the year-month to compare to.
     * @param int $m2     The month of the year-month to compare to.
     * @param int $result The comparison result.
     */
    public function testIsBeforeOrEqualTo(int $y1, int $m1, int $y2, int $m2, int $result)
    {
        $this->assertSame($result <= 0, YearMonth::of($y1, $m1)->isBeforeOrEqualTo(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $y1     The year of the base year-month.
     * @param int $m1     The month of the base year-month.
     * @param int $y2     The year of the year-month to compare to.
     * @param int $m2     The month of the year-month to compare to.
     * @param int $result The comparison result.
     */
    public function testIsAfter(int $y1, int $m1, int $y2, int $m2, int $result)
    {
        $this->assertSame($result == 1, YearMonth::of($y1, $m1)->isAfter(YearMonth::of($y2, $m2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $y1     The year of the base year-month.
     * @param int $m1     The month of the base year-month.
     * @param int $y2     The year of the year-month to compare to.
     * @param int $m2     The month of the year-month to compare to.
     * @param int $result The comparison result.
     */
    public function testIsAfterOrEqualTo(int $y1, int $m1, int $y2, int $m2, int $result)
    {
        $this->assertSame($result >= 0, YearMonth::of($y1, $m1)->isAfterOrEqualTo(YearMonth::of($y2, $m2)));
    }

    /**
     * @return array
     */
    public function providerCompareTo() : array
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
        $this->assertYearMonthIs(2001, 5, YearMonth::of(2000, 5)->withYear(2001));
    }

    public function testWithYearIsTheSameYear()
    {
        $year = (int)date('Y');
        $month = (int)date('m');

        $this->assertInstanceOf(YearMonth::class, YearMonth::of($year, $month)->withYear($year));
        $this->assertYearMonthIs($year, $month, YearMonth::of($year, $month)->withYear($year));
    }

    public function testWithMonth()
    {
        $this->assertYearMonthIs(2000, 12, YearMonth::of(2000, 1)->withMonth(12));
    }

    public function testWithMonthWithSameMonth()
    {
        $month = (int)date('m');

        $this->assertYearMonthIs(2000, $month, YearMonth::of(2000, $month)->withMonth($month));
    }

    public function testGetFirstDay()
    {
        $this->assertLocalDateIs(2023, 10, 1, YearMonth::of(2023, 10)->getFirstDay());
    }

    public function testGetLastDay()
    {
        $this->assertLocalDateIs(2023, 10, 31, YearMonth::of(2023, 10)->getLastDay());    
    }

    /**
     * @dataProvider providerGetLastDay
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function getGetLastDay(int $year, int $month, int $day)
    {
        $this->assertLocalDateIs($year, $month, $day, YearMonth::of($year, $month)->getLastDay());
    }

    /**
     * @return array
     */
    public function providerGetLastDay() : array
    {
        return [
            [2000, 1, 31],
            [2000, 2, 29],
            [2001, 2, 28],
            [2002, 3, 31],
            [2002, 4, 40],
        ];
    }

    public function testAtDay()
    {
        $this->assertLocalDateIs(2001, 2, 3, YearMonth::of(2001, 02)->atDay(3));
    }

    /**
     * @dataProvider providerPlusYears
     *
     * @param int $year
     * @param int $month
     * @param int $plusYears
     * @param int $expectedYear
     * @param int $expectedMonth
     */
    public function testPlusYears(int $year, int $month, int $plusYears, int $expectedYear, int $expectedMonth)
    {
        $yearMonth = YearMonth::of($year, $month);
        $this->assertYearMonthIs($expectedYear, $expectedMonth, $yearMonth->plusYears($plusYears));
    }

    public function testPlusYearsWithParameterIsZero()
    {
        $yearMonth = YearMonth::of(2005, 1);
        $this->assertYearMonthIs(2005, 1, $yearMonth->plusYears(0));
    }

    /**
     * @return array
     */
    public function providerPlusYears() : array
    {
        return [
            [2003, 11, 7, 2010, 11],
            [1999, 3, -99, 1900, 3],
        ];
    }

    /**
     * @dataProvider providerMinusYears
     *
     * @param int $year
     * @param int $month
     * @param int $plusYears
     * @param int $expectedYear
     * @param int $expectedMonth
     */
    public function testMinusYears(int $year, int $month, int $plusYears, int $expectedYear, int $expectedMonth)
    {
        $yearMonth = YearMonth::of($year, $month);
        $this->assertYearMonthIs($expectedYear, $expectedMonth, $yearMonth->minusYears($plusYears));
    }

    /**
     * @return array
     */
    public function providerMinusYears() : array
    {
        return [
            [2003, 11, 7, 1996, 11],
            [1999, 3, -99, 2098, 3],
        ];
    }

    /**
     * @dataProvider providerPlusMonths
     *
     * @param int $year
     * @param int $month
     * @param int $plusMonths
     * @param int $expectedYear
     * @param int $expectedMonth
     */
    public function testPlusMonths(int $year, int $month, int $plusMonths, int $expectedYear, int $expectedMonth)
    {
        $yearMonth = YearMonth::of($year, $month);
        $this->assertYearMonthIs($expectedYear, $expectedMonth, $yearMonth->plusMonths($plusMonths));
    }

    /**
     * @return array
     */
    public function providerPlusMonths() : array
    {
        return [
            [2015, 11, -12, 2014, 11],
            [2015, 11, -11, 2014, 12],
            [2015, 11, -10, 2015, 1],
            [2015, 11, -1, 2015, 10],
            [2015, 11, 0, 2015, 11],
            [2015, 11, 1, 2015, 12],
            [2015, 11, 2, 2016, 1],
            [1963, 1, -4813, 1561, 12],
            [1789, 10, 7939, 2451, 5],
        ];
    }

    /**
     * @dataProvider providerMinusMonths
     *
     * @param int $year
     * @param int $month
     * @param int $plusMonths
     * @param int $expectedYear
     * @param int $expectedMonth
     */
    public function testMinusMonths(int $year, int $month, int $plusMonths, int $expectedYear, int $expectedMonth)
    {
        $yearMonth = YearMonth::of($year, $month);
        $this->assertYearMonthIs($expectedYear, $expectedMonth, $yearMonth->minusMonths($plusMonths));
    }

    /**
     * @return array
     */
    public function providerMinusMonths() : array
    {
        return [
            [2015, 11, -2, 2016, 1],
            [2015, 11, -1, 2015, 12],
            [2015, 11, 0, 2015, 11],
            [2015, 11, 1, 2015, 10],
            [2015, 11, 10, 2015, 1],
            [2015, 11, 11, 2014, 12],
            [2015, 11, 12, 2014, 11],
            [1963, 1, -4813, 2364, 2],
            [1789, 10, 7939, 1128, 3],
        ];
    }

    public function testToString()
    {
        $this->assertSame('2013-09', (string) YearMonth::of(2013, 9));
    }
}
