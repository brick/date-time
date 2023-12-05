<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\MonthDay;
use Brick\DateTime\TimeZone;
use Brick\DateTime\Year;

use function json_encode;

use const JSON_THROW_ON_ERROR;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Unit tests for class Year.
 */
class YearTest extends AbstractTestCase
{
    public function testOf(): void
    {
        self::assertYearIs(1987, Year::of(1987));
    }

    /**
     * @dataProvider providerOfInvalidYearThrowsException
     */
    public function testOfInvalidYearThrowsException(int $invalidYear): void
    {
        $this->expectException(DateTimeException::class);
        Year::of($invalidYear);
    }

    /**
     * @dataProvider providerParse
     */
    public function testParse(string $string, int $expectedYear): void
    {
        self::assertYearIs($expectedYear, Year::parse($string));
    }

    public function providerParse(): array
    {
        return [
            ['-2023', -2023],
            ['-0100', -100],
            ['1987', 1987],
            ['121241', 121241],
        ];
    }

    /**
     * @dataProvider providerParseInvalidYearThrowsException
     */
    public function testParseInvalidYearThrowsException(string $invalidValue): void
    {
        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage('Failed to parse "' . $invalidValue . '"');

        Year::parse($invalidValue);
    }

    public function providerParseInvalidYearThrowsException(): array
    {
        return [
            [''],
            ['+2000'],
            ['-100'],
            ['ABC'],
            ['9999999999'],
        ];
    }

    public function providerOfInvalidYearThrowsException(): array
    {
        return [
            [PHP_INT_MIN],
            [PHP_INT_MAX],
            [-1000000000],
            [1000000000],
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param int    $epochSecond  The epoch second to set the clock time to.
     * @param string $timeZone     The time-zone to get the current year in.
     * @param int    $expectedYear The expected year.
     */
    public function testNow(int $epochSecond, string $timeZone, int $expectedYear): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        self::assertYearIs($expectedYear, Year::now(TimeZone::parse($timeZone), $clock));
    }

    public function providerNow(): array
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
     * @param int  $year   The year to test.
     * @param bool $isLeap Whether the year is a leap year.
     */
    public function testIsLeap(int $year, bool $isLeap): void
    {
        self::assertSame($isLeap, Year::of($year)->isLeap());
    }

    public function providerIsLeap(): array
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
            [2005, false],
        ];
    }

    /**
     * @dataProvider providerIsValidMonthDay
     *
     * @param int  $year    The base year.
     * @param int  $month   The month of the month-day to test.
     * @param int  $day     The day-of-month of the month-day to test.
     * @param bool $isValid Whether the month-day is expected to be valid.
     */
    public function testIsValidMonthDay(int $year, int $month, int $day, bool $isValid): void
    {
        self::assertSame($isValid, Year::of($year)->isValidMonthDay(MonthDay::of($month, $day)));
    }

    public function providerIsValidMonthDay(): array
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
            [2001, 2, 29, false],
        ];
    }

    /**
     * @dataProvider providerGetLength
     *
     * @param int $year   The year to test.
     * @param int $length The expected length of year in days.
     */
    public function testGetLength(int $year, int $length): void
    {
        self::assertSame($length, Year::of($year)->getLength());
    }

    public function providerGetLength(): array
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
            [2005, 365],
        ];
    }

    /**
     * @dataProvider providerPlus
     */
    public function testPlus(int $year, int $plusYears, int $expectedYear): void
    {
        self::assertYearIs($expectedYear, Year::of($year)->plus($plusYears));
    }

    public function providerPlus(): array
    {
        return [
            [2014, 0, 2014],
            [2014, 16, 2030],
            [2014, -16, 1998],
        ];
    }

    /**
     * @dataProvider providerMinus
     */
    public function testMinus(int $year, int $minusYears, int $expectedYear): void
    {
        self::assertYearIs($expectedYear, Year::of($year)->minus($minusYears));
    }

    public function providerMinus(): array
    {
        return [
            [2014, 0, 2014],
            [2014, 16, 1998],
            [2014, -16, 2030],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $year1 The base year.
     * @param int $year2 The year to compare to.
     * @param int $cmp   The comparison value.
     */
    public function testCompareTo(int $year1, int $year2, int $cmp): void
    {
        self::assertSame($cmp, Year::of($year1)->compareTo(Year::of($year2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $year1 The base year.
     * @param int $year2 The year to compare to.
     * @param int $cmp   The comparison value.
     */
    public function testIsEqualTo(int $year1, int $year2, int $cmp): void
    {
        self::assertSame($cmp === 0, Year::of($year1)->isEqualTo(Year::of($year2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $year1 The base year.
     * @param int $year2 The year to compare to.
     * @param int $cmp   The comparison value.
     */
    public function testIsAfter(int $year1, int $year2, int $cmp): void
    {
        self::assertSame($cmp === 1, Year::of($year1)->isAfter(Year::of($year2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $year1 The base year.
     * @param int $year2 The year to compare to.
     * @param int $cmp   The comparison value.
     */
    public function testIsBefore(int $year1, int $year2, int $cmp): void
    {
        self::assertSame($cmp === -1, Year::of($year1)->isBefore(Year::of($year2)));
    }

    public function providerCompareTo(): array
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
     * @param int $year      The base year.
     * @param int $dayOfYear The day-of-year to apply.
     * @param int $month     The expected month of the resulting date.
     * @param int $day       The expected day-of-month of the resulting date.
     */
    public function testAtDay(int $year, int $dayOfYear, int $month, int $day): void
    {
        self::assertLocalDateIs($year, $month, $day, Year::of($year)->atDay($dayOfYear));
    }

    public function providerAtDay(): array
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

    public function testAtInvalidDayThrowsException(): void
    {
        $this->expectException(DateTimeException::class);
        Year::of(2007)->atDay(366);
    }

    public function testAtMonth(): void
    {
        self::assertYearMonthIs(2014, 7, Year::of(2014)->atMonth(7));
    }

    /**
     * @dataProvider providerAtInvalidMonthThrowsException
     */
    public function testAtInvalidMonthThrowsException(int $invalidMonth): void
    {
        $this->expectException(DateTimeException::class);
        Year::of(2000)->atMonth($invalidMonth);
    }

    public function providerAtInvalidMonthThrowsException(): array
    {
        return [
            [-1],
            [0],
            [13],
        ];
    }

    /**
     * @dataProvider providerAtMonthDay
     *
     * @param int $year        The base year.
     * @param int $month       The month-of-year of the month-day to apply.
     * @param int $day         The day-of-month of the month-day to apply.
     * @param int $expectedDay The expected day of the resulting date.
     */
    public function testAtMonthDay(int $year, int $month, int $day, int $expectedDay): void
    {
        $monthDay = MonthDay::of($month, $day);
        self::assertLocalDateIs($year, $month, $expectedDay, Year::of($year)->atMonthDay($monthDay));
    }

    public function providerAtMonthDay(): array
    {
        return [
            [2007, 2, 28, 28],
            [2007, 2, 29, 28],
            [2008, 2, 28, 28],
            [2008, 2, 29, 29],
            [2009, 2, 28, 28],
            [2009, 2, 29, 28],
            [2010, 1, 31, 31],
        ];
    }

    /**
     * @dataProvider providerToLocalDateRange
     */
    public function testToLocalDateRange(int $year, string $expectedRange): void
    {
        self::assertSame($expectedRange, (string) Year::of($year)->toLocalDateRange());
    }

    public function providerToLocalDateRange(): array
    {
        return [
            [1900, '1900-01-01/1900-12-31'],
            [2020, '2020-01-01/2020-12-31'],
            [3000, '3000-01-01/3000-12-31'],
        ];
    }

    /** @dataProvider providerToString */
    public function testJsonSerialize(int $year, string $expectedString): void
    {
        self::assertSame(json_encode($expectedString, JSON_THROW_ON_ERROR), json_encode(Year::of($year), JSON_THROW_ON_ERROR));
    }

    /** @dataProvider providerToString */
    public function testToISOString(int $year, string $expectedString): void
    {
        self::assertSame($expectedString, Year::of($year)->toISOString());
    }

    /** @dataProvider providerToString */
    public function testToString(int $year, string $expectedString): void
    {
        self::assertSame($expectedString, (string) Year::of($year));
    }

    public function providerToString(): array
    {
        return [
            [-105781, '-105781'],
            [-1001, '-1001'],
            [-1000, '-1000'],
            [-999, '-0999'],
            [-99, '-0099'],
            [-9, '-0009'],
            [-1, '-0001'],
            [0, '0000'],
            [1, '0001'],
            [9, '0009'],
            [99, '0099'],
            [999, '0999'],
            [1000, '1000'],
            [1001, '1001'],
            [1987, '1987'],
            [105781, '105781'],
        ];
    }
}
