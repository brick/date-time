<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;
use Brick\DateTime\TimeZone;
use Brick\DateTime\YearWeek;

use function json_encode;

/**
 * Unit tests for class YearWeek.
 */
class YearWeekTest extends AbstractTestCase
{
    /**
     * @dataProvider provider53WeekYear
     */
    public function testIs53WeekYear(int $year): void
    {
        $yearWeek = YearWeek::of($year, 1);
        self::assertTrue($yearWeek->is53WeekYear());
    }

    public function provider53WeekYear(): array
    {
        return [
            [4],
            [9],
            [15],
            [20],
            [26],
            [32],
            [37],
            [43],
            [48],
            [54],
            [60],
            [65],
            [71],
            [76],
            [82],
            [88],
            [93],
            [99],
            [105],
            [111],
            [116],
            [122],
            [128],
            [133],
            [139],
            [144],
            [150],
            [156],
            [161],
            [167],
            [172],
            [178],
            [184],
            [189],
            [195],
            [201],
            [207],
            [212],
            [218],
            [224],
            [229],
            [235],
            [240],
            [246],
            [252],
            [257],
            [263],
            [268],
            [274],
            [280],
            [285],
            [291],
            [296],
            [303],
            [308],
            [314],
            [320],
            [325],
            [331],
            [336],
            [342],
            [348],
            [353],
            [359],
            [364],
            [370],
            [376],
            [381],
            [387],
            [392],
            [398],
        ];
    }

    /**
     * @dataProvider providerAtDay
     */
    public function testAtDay(int $weekBasedYear, int $weekOfWeekBasedYear, DayOfWeek $dayOfWeek, int $year, int $month, int $dayOfMonth): void
    {
        $yearWeek = YearWeek::of($weekBasedYear, $weekOfWeekBasedYear);

        $actual = $yearWeek->atDay($dayOfWeek);
        self::assertLocalDateIs($year, $month, $dayOfMonth, $actual);

        $actual = $yearWeek->atDay($dayOfWeek->value);
        self::assertLocalDateIs($year, $month, $dayOfMonth, $actual);
    }

    public function providerAtDay(): array
    {
        return [
            [2014, 52, DayOfWeek::MONDAY,    2014, 12, 22],
            [2014, 52, DayOfWeek::TUESDAY,   2014, 12, 23],
            [2014, 52, DayOfWeek::WEDNESDAY, 2014, 12, 24],
            [2014, 52, DayOfWeek::THURSDAY,  2014, 12, 25],
            [2014, 52, DayOfWeek::FRIDAY,    2014, 12, 26],
            [2014, 52, DayOfWeek::SATURDAY,  2014, 12, 27],
            [2014, 52, DayOfWeek::SUNDAY,    2014, 12, 28],
            [2015,  1, DayOfWeek::MONDAY,    2014, 12, 29],
            [2015,  1, DayOfWeek::TUESDAY,   2014, 12, 30],
            [2015,  1, DayOfWeek::WEDNESDAY, 2014, 12, 31],
            [2015,  1, DayOfWeek::THURSDAY,  2015,  1,  1],
            [2015,  1, DayOfWeek::FRIDAY,    2015,  1,  2],
            [2015,  1, DayOfWeek::SATURDAY,  2015,  1,  3],
            [2015,  1, DayOfWeek::SUNDAY,    2015,  1,  4],
            [2015, 53, DayOfWeek::FRIDAY,    2016,  1,  1],
            [2015, 53, DayOfWeek::SATURDAY,  2016,  1,  2],
            [2015, 53, DayOfWeek::SUNDAY,    2016,  1,  3],
            [2016,  1, DayOfWeek::MONDAY,    2016,  1,  4],
            [2016, 52, DayOfWeek::SUNDAY,    2017,  1,  1],
            [2017,  1, DayOfWeek::MONDAY,    2017,  1,  2],
            [2017,  1, DayOfWeek::TUESDAY,   2017,  1,  3],
            [2017,  1, DayOfWeek::WEDNESDAY, 2017,  1,  4],
            [2017,  1, DayOfWeek::THURSDAY,  2017,  1,  5],
            [2017,  1, DayOfWeek::FRIDAY,    2017,  1,  6],
            [2017,  1, DayOfWeek::SATURDAY,  2017,  1,  7],
            [2017,  1, DayOfWeek::SUNDAY,    2017,  1,  8],
            [2025,  1, DayOfWeek::MONDAY,    2024, 12, 30],
        ];
    }

    public function testAtDayWithInvalidDay(): void
    {
        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage('Invalid day-of-week: 0 is not in the range 1 to 7.');

        YearWeek::of(2000, 1)->atDay(0);
    }

    public function testCompareTo(): void
    {
        $years = [-3, -2, 1, 0, 1, 2, 3];
        $weeks = [1, 2, 3, 50, 51, 52];

        foreach ($years as $year1) {
            foreach ($weeks as $week1) {
                $a = YearWeek::of($year1, $week1);

                foreach ($years as $year2) {
                    foreach ($weeks as $week2) {
                        $b = YearWeek::of($year2, $week2);

                        if ($year1 < $year2) {
                            self::assertCompareTo(-1, $a, $b);
                            self::assertCompareTo(1, $b, $a);
                        } elseif ($year1 > $year2) {
                            self::assertCompareTo(1, $a, $b);
                            self::assertCompareTo(-1, $b, $a);
                        } elseif ($week1 < $week2) {
                            self::assertCompareTo(-1, $a, $b);
                            self::assertCompareTo(1, $b, $a);
                        } elseif ($week1 > $week2) {
                            self::assertCompareTo(1, $a, $b);
                            self::assertCompareTo(-1, $b, $a);
                        } else {
                            self::assertCompareTo(0, $a, $b);
                            self::assertCompareTo(0, $b, $a);
                        }
                    }
                }
            }
        }
    }

    public function providerWithYear(): array
    {
        return [
            [2015,  1, 2015, 2015,  1],
            [2015,  1, 2014, 2014,  1],
            [2015, 53, 2009, 2009, 53],
            [2015, 53, 2014, 2014, 52],
        ];
    }

    /**
     * @dataProvider providerWithYear
     */
    public function testWithYear(int $year, int $week, int $withYear, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::of($year, $week)->withYear($withYear);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    public function providerWithWeek(): array
    {
        return [
            [2014,  1, 53, 2015,  1],
            [2014,  2,  2, 2014,  2],
            [2015,  1, 52, 2015, 52],
        ];
    }

    /**
     * @dataProvider providerWithWeek
     */
    public function testWithWeek(int $year, int $week, int $withWeek, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::of($year, $week)->withWeek($withWeek);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    public function providerPlusYears(): array
    {
        return [
            [2015, 1, -2, 2013, 1],
            [2015, 1, -1, 2014, 1],
            [2015, 1,  0, 2015, 1],
            [2015, 1,  1, 2016, 1],
            [2015, 1,  2, 2017, 1],

            [2015, 53, -1, 2014, 52],
            [2015, 53,  0, 2015, 53],
            [2015, 53,  1, 2016, 52],
            [2015, 53,  5, 2020, 53],
        ];
    }

    /**
     * @dataProvider providerPlusYears
     */
    public function testPlusYears(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::of($year, $week)->plusYears($delta);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @dataProvider providerPlusYears
     */
    public function testMinusYears(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::of($year, $week)->minusYears(-$delta);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    public function providerPlusWeeks(): array
    {
        return [
            [2015, 1, -261, 2009, 53],
            [2015, 1,  -53, 2013, 52],
            [2015, 1,  -52, 2014,  1],
            [2015, 1,  -51, 2014,  2],
            [2015, 1,   -2, 2014, 51],
            [2015, 1,   -1, 2014, 52],
            [2015, 1,    0, 2015,  1],
            [2015, 1,    1, 2015,  2],
            [2015, 1,    2, 2015,  3],
            [2015, 1,   51, 2015, 52],
            [2015, 1,   52, 2015, 53],
            [2015, 1,   53, 2016,  1],
            [2015, 1,  314, 2021,  1],
        ];
    }

    /**
     * @dataProvider providerPlusWeeks
     */
    public function testPlusWeeks(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::of($year, $week)->plusWeeks($delta);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @dataProvider providerPlusWeeks
     */
    public function testMinusWeeks(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::of($year, $week)->minusWeeks(-$delta);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    public function providerToString(): array
    {
        return [
            [-12345,  1, '-12345-W01'],
            [-1234, 12,  '-1234-W12'],
            [-123,  3,  '-0123-W03'],
            [-12, 10,  '-0012-W10'],
            [-1,  9,  '-0001-W09'],
            [0, 11,   '0000-W11'],
            [1,  7,   '0001-W07'],
            [12, 10,   '0012-W10'],
            [123,  4,   '0123-W04'],
            [1234, 12,   '1234-W12'],
            [12345,  8,  '12345-W08'],
        ];
    }

    /**
     * @dataProvider providerToString
     */
    public function testJsonSerialize(int $year, int $week, string $expected): void
    {
        $yearWeek = YearWeek::of($year, $week);
        self::assertSame(json_encode($expected), json_encode($yearWeek));
    }

    /**
     * @dataProvider providerToString
     */
    public function testToISOString(int $year, int $week, string $expected): void
    {
        $yearWeek = YearWeek::of($year, $week);
        self::assertSame($expected, $yearWeek->toISOString());
    }

    /**
     * @dataProvider providerToString
     */
    public function testToString(int $year, int $week, string $expected): void
    {
        $yearWeek = YearWeek::of($year, $week);
        self::assertSame($expected, (string) $yearWeek);
    }

    /**
     * @dataProvider providerParse
     */
    public function testParse(string $string, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = YearWeek::parse($string);
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    public function providerParse(): array
    {
        return [
            ['-2000-W12', -2000, 12],
            ['-0100-W01', -100, 1],
            ['2015-W01', 2015, 1],
            ['2015-W48', 2015, 48],
            ['2026-W53', 2026, 53],
            ['120195-W23', 120195, 23],
        ];
    }

    /**
     * @dataProvider providerParseInvalidYearWeekThrowsException
     */
    public function testParseInvalidYearWeekThrowsException(string $invalidValue, ?string $error = null): void
    {
        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage($error ?? 'Failed to parse "' . $invalidValue . '"');

        YearWeek::parse($invalidValue);
    }

    public function providerParseInvalidYearWeekThrowsException(): array
    {
        return [
            [''],
            ['+2000-W01'],
            ['2000W01'],
            ['2000-W54', 'Invalid week-of-year: 54 is not in the range 1 to 53.'],
            ['2025-W53', 'Year 2025 does not have 53 weeks'],
        ];
    }

    public function testNow(): void
    {
        $now = new FixedClock(Instant::of(2000000000));
        $timeZone = TimeZone::parse('Asia/Taipei');
        $yearWeek = YearWeek::now($timeZone, $now);

        self::assertYearWeekIs(2033, 20, $yearWeek);
    }

    /**
     * @dataProvider providerGetFirstLastDay
     *
     * @param int    $year     The year.
     * @param int    $week     The week-of-year.
     * @param string $firstDay The expected first day of the week.
     * @param string $lastDay  The expected last day of the week.
     */
    public function testGetFirstLastDay(int $year, int $week, string $firstDay, string $lastDay): void
    {
        $yearWeek = YearWeek::of($year, $week);

        self::assertIs(LocalDate::class, $firstDay, $yearWeek->getFirstDay());
        self::assertIs(LocalDate::class, $lastDay, $yearWeek->getLastDay());
    }

    /**
     * @dataProvider providerGetFirstLastDay
     */
    public function testToLocalDateRange(int $year, int $week, string $firstDay, string $lastDay): void
    {
        $yearWeek = YearWeek::of($year, $week);
        $expectedDateRange = (string) LocalDateRange::parse($firstDay . '/' . $lastDay);

        self::assertSame($expectedDateRange, (string) $yearWeek->toLocalDateRange());
    }

    public function providerGetFirstLastDay(): array
    {
        return [
            [2000,  1, '2000-01-03', '2000-01-09'],
            [2000,  2, '2000-01-10', '2000-01-16'],
            [2000, 51, '2000-12-18', '2000-12-24'],
            [2000, 52, '2000-12-25', '2000-12-31'],
            [2001,  1, '2001-01-01', '2001-01-07'],
            [2001,  2, '2001-01-08', '2001-01-14'],
            [2001, 51, '2001-12-17', '2001-12-23'],
            [2001, 52, '2001-12-24', '2001-12-30'],
            [2002,  1, '2001-12-31', '2002-01-06'],
            [2002,  2, '2002-01-07', '2002-01-13'],
            [2002, 51, '2002-12-16', '2002-12-22'],
            [2002, 52, '2002-12-23', '2002-12-29'],
            [2003,  1, '2002-12-30', '2003-01-05'],
            [2003,  2, '2003-01-06', '2003-01-12'],
            [2003, 51, '2003-12-15', '2003-12-21'],
            [2003, 52, '2003-12-22', '2003-12-28'],
            [2004,  1, '2003-12-29', '2004-01-04'],
            [2004,  2, '2004-01-05', '2004-01-11'],
            [2004, 52, '2004-12-20', '2004-12-26'],
            [2004, 53, '2004-12-27', '2005-01-02'],
            [2005,  1, '2005-01-03', '2005-01-09'],
            [2005,  2, '2005-01-10', '2005-01-16'],
            [2005, 51, '2005-12-19', '2005-12-25'],
            [2005, 52, '2005-12-26', '2006-01-01'],
            [2006,  1, '2006-01-02', '2006-01-08'],
            [2006,  2, '2006-01-09', '2006-01-15'],
            [2006, 51, '2006-12-18', '2006-12-24'],
            [2006, 52, '2006-12-25', '2006-12-31'],
            [2007,  1, '2007-01-01', '2007-01-07'],
            [2007,  2, '2007-01-08', '2007-01-14'],
            [2007, 51, '2007-12-17', '2007-12-23'],
            [2007, 52, '2007-12-24', '2007-12-30'],
            [2008,  1, '2007-12-31', '2008-01-06'],
            [2008,  2, '2008-01-07', '2008-01-13'],
            [2008, 51, '2008-12-15', '2008-12-21'],
            [2008, 52, '2008-12-22', '2008-12-28'],
            [2009,  1, '2008-12-29', '2009-01-04'],
            [2009,  2, '2009-01-05', '2009-01-11'],
            [2009, 52, '2009-12-21', '2009-12-27'],
            [2009, 53, '2009-12-28', '2010-01-03'],
            [2010,  1, '2010-01-04', '2010-01-10'],
            [2010,  2, '2010-01-11', '2010-01-17'],
            [2010, 51, '2010-12-20', '2010-12-26'],
            [2010, 52, '2010-12-27', '2011-01-02'],
            [2011,  1, '2011-01-03', '2011-01-09'],
            [2011,  2, '2011-01-10', '2011-01-16'],
            [2011, 51, '2011-12-19', '2011-12-25'],
            [2011, 52, '2011-12-26', '2012-01-01'],
            [2012,  1, '2012-01-02', '2012-01-08'],
            [2012,  2, '2012-01-09', '2012-01-15'],
            [2012, 51, '2012-12-17', '2012-12-23'],
            [2012, 52, '2012-12-24', '2012-12-30'],
            [2013,  1, '2012-12-31', '2013-01-06'],
            [2013,  2, '2013-01-07', '2013-01-13'],
            [2013, 51, '2013-12-16', '2013-12-22'],
            [2013, 52, '2013-12-23', '2013-12-29'],
            [2014,  1, '2013-12-30', '2014-01-05'],
            [2014,  2, '2014-01-06', '2014-01-12'],
            [2014, 51, '2014-12-15', '2014-12-21'],
            [2014, 52, '2014-12-22', '2014-12-28'],
            [2015,  1, '2014-12-29', '2015-01-04'],
            [2015,  2, '2015-01-05', '2015-01-11'],
            [2015, 52, '2015-12-21', '2015-12-27'],
            [2015, 53, '2015-12-28', '2016-01-03'],
            [2016,  1, '2016-01-04', '2016-01-10'],
            [2016,  2, '2016-01-11', '2016-01-17'],
            [2016, 51, '2016-12-19', '2016-12-25'],
            [2016, 52, '2016-12-26', '2017-01-01'],
            [2017,  1, '2017-01-02', '2017-01-08'],
            [2017,  2, '2017-01-09', '2017-01-15'],
            [2017, 51, '2017-12-18', '2017-12-24'],
            [2017, 52, '2017-12-25', '2017-12-31'],
            [2018,  1, '2018-01-01', '2018-01-07'],
            [2018,  2, '2018-01-08', '2018-01-14'],
            [2018, 51, '2018-12-17', '2018-12-23'],
            [2018, 52, '2018-12-24', '2018-12-30'],
            [2019,  1, '2018-12-31', '2019-01-06'],
            [2019,  2, '2019-01-07', '2019-01-13'],
            [2019, 51, '2019-12-16', '2019-12-22'],
            [2019, 52, '2019-12-23', '2019-12-29'],
            [2020,  1, '2019-12-30', '2020-01-05'],
            [2020,  2, '2020-01-06', '2020-01-12'],
            [2020, 52, '2020-12-21', '2020-12-27'],
            [2020, 53, '2020-12-28', '2021-01-03'],
        ];
    }

    private function assertCompareTo(int $expected, YearWeek $a, YearWeek $b): void
    {
        self::assertSame($expected, $a->compareTo($b));
        self::assertSame($expected === -1 || $expected === 0, $a->isBeforeOrEqualTo($b));
        self::assertSame($expected === -1, $a->isBefore($b));
        self::assertSame($expected === 0, $a->isEqualTo($b));
        self::assertSame($expected === 1, $a->isAfter($b));
        self::assertSame($expected === 1 || $expected === 0, $a->isAfterOrEqualTo($b));
    }
}
