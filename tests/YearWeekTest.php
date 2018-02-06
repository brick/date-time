<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DayOfWeek;
use Brick\DateTime\YearWeek;

/**
 * Unit tests for class YearWeek.
 */
class YearWeekTest extends AbstractTestCase
{
    /**
     * @return array
     */
    public function provider53WeekYear() : array
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
     * @dataProvider provider53WeekYear
     *
     * @param int $year
     */
    public function testIs53WeekYear(int $year)
    {
        $yearWeek = YearWeek::of($year, 1);
        $this->assertTrue($yearWeek->is53WeekYear());
    }

    /**
     * @return array
     */
    public function providerAtDay() : array
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

    /**
     * @dataProvider providerAtDay
     *
     * @param int $weekBasedYear
     * @param int $weekOfWeekBasedYear
     * @param int $dayOfWeek
     * @param int $year
     * @param int $month
     * @param int $dayOfMonth
     */
    public function testAtDay(int $weekBasedYear, int $weekOfWeekBasedYear, int $dayOfWeek, int $year, int $month, int $dayOfMonth)
    {
        $yearWeek = YearWeek::of($weekBasedYear, $weekOfWeekBasedYear);
        $actual = $yearWeek->atDay($dayOfWeek);

        $this->assertLocalDateIs($year, $month, $dayOfMonth, $actual);
    }

    public function testCompareTo()
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
                            $this->assertCompareTo(-1, $a, $b);
                            $this->assertCompareTo(1, $b, $a);
                        } elseif ($year1 > $year2) {
                            $this->assertCompareTo(1, $a, $b);
                            $this->assertCompareTo(-1, $b, $a);
                        } elseif ($week1 < $week2) {
                            $this->assertCompareTo(-1, $a, $b);
                            $this->assertCompareTo(1, $b, $a);
                        } elseif ($week1 > $week2) {
                            $this->assertCompareTo(1, $a, $b);
                            $this->assertCompareTo(-1, $b, $a);
                        } else {
                            $this->assertCompareTo(0, $a, $b);
                            $this->assertCompareTo(0, $b, $a);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param int      $expected
     * @param YearWeek $a
     * @param YearWeek $b
     */
    private function assertCompareTo(int $expected, YearWeek $a, YearWeek $b)
    {
        $this->assertSame($expected, $a->compareTo($b));
        $this->assertSame($expected === -1 || $expected === 0, $a->isBeforeOrEqualTo($b));
        $this->assertSame($expected === -1, $a->isBefore($b));
        $this->assertSame($expected === 0, $a->isEqualTo($b));
        $this->assertSame($expected === 1, $a->isAfter($b));
        $this->assertSame($expected === 1 || $expected === 0, $a->isAfterOrEqualTo($b));
    }

    /**
     * @return array
     */
    public function providerWithYear() : array
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
     *
     * @param int $year
     * @param int $week
     * @param int $withYear
     * @param int $expectedYear
     * @param int $expectedWeek
     */
    public function testWithYear(int $year, int $week, int $withYear, int $expectedYear, int $expectedWeek)
    {
        $yearWeek = YearWeek::of($year, $week)->withYear($withYear);
        $this->assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @return array
     */
    public function providerWithWeek() : array
    {
        return [
            [2014,  1, 53, 2015,  1],
            [2014,  2,  2, 2014,  2],
            [2015,  1, 52, 2015, 52],
        ];
    }

    /**
     * @dataProvider providerWithWeek
     *
     * @param int $year
     * @param int $week
     * @param int $withWeek
     * @param int $expectedYear
     * @param int $expectedWeek
     */
    public function testWithWeek(int $year, int $week, int $withWeek, int $expectedYear, int $expectedWeek)
    {
        $yearWeek = YearWeek::of($year, $week)->withWeek($withWeek);
        $this->assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @return array
     */
    public function providerPlusYears() : array
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
     *
     * @param int $year
     * @param int $week
     * @param int $delta
     * @param int $expectedYear
     * @param int $expectedWeek
     */
    public function testPlusYears(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek)
    {
        $yearWeek = YearWeek::of($year, $week)->plusYears($delta);
        $this->assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @dataProvider providerPlusYears
     *
     * @param int $year
     * @param int $week
     * @param int $delta
     * @param int $expectedYear
     * @param int $expectedWeek
     */
    public function testMinusYears(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek)
    {
        $yearWeek = YearWeek::of($year, $week)->minusYears(-$delta);
        $this->assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @return array
     */
    public function providerPlusWeeks() : array
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
     *
     * @param int $year
     * @param int $week
     * @param int $delta
     * @param int $expectedYear
     * @param int $expectedWeek
     */
    public function testPlusWeeks(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek)
    {
        $yearWeek = YearWeek::of($year, $week)->plusWeeks($delta);
        $this->assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @dataProvider providerPlusWeeks
     *
     * @param int $year
     * @param int $week
     * @param int $delta
     * @param int $expectedYear
     * @param int $expectedWeek
     */
    public function testMinusWeeks(int $year, int $week, int $delta, int $expectedYear, int $expectedWeek)
    {
        $yearWeek = YearWeek::of($year, $week)->minusWeeks(-$delta);
        $this->assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @return array
     */
    public function providerToString() : array
    {
        return [
            [-12345,  1, '-12345-W01'],
            [ -1234, 12,  '-1234-W12'],
            [  -123,  3,  '-0123-W03'],
            [   -12, 10,  '-0012-W10'],
            [    -1,  9,  '-0001-W09'],
            [     0, 11,   '0000-W11'],
            [     1,  7,   '0001-W07'],
            [    12, 10,   '0012-W10'],
            [   123,  4,   '0123-W04'],
            [  1234, 12,   '1234-W12'],
            [ 12345,  8,  '12345-W08'],
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $year
     * @param int    $week
     * @param string $expected
     */
    public function testToString(int $year, int $week, string $expected)
    {
        $yearWeek = YearWeek::of($year, $week);
        $this->assertSame($expected, (string) $yearWeek);
    }
}
