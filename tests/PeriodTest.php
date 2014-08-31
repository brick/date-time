<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Period;

/**
 * Unit tests for class Period.
 */
class PeriodTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertPeriodEquals(1, 2, 3, Period::of(1, 2, 3));
    }

    public function testOfYears()
    {
        $this->assertPeriodEquals(11, 0, 0, Period::ofYears(11));
    }

    public function testOfMonths()
    {
        $this->assertPeriodEquals(0, 11, 0, Period::ofMonths(11));
    }

    public function testOfWeeks()
    {
        $this->assertPeriodEquals(0, 0, 77, Period::ofWeeks(11));
    }

    public function testOfDays()
    {
        $this->assertPeriodEquals(0, 0, 11, Period::ofDays(11));
    }

    public function testZero()
    {
        $this->assertPeriodEquals(0, 0, 0, Period::zero());
    }

    /**
     * @dataProvider providerParse
     *
     * @param string  $text   The text to parse.
     * @param integer $years  The expected years in the period.
     * @param integer $months The expected months in the period.
     * @param integer $days   The expected days in the period.
     */
    public function testParse($text, $years, $months, $days)
    {
        $this->assertPeriodEquals($years, $months, $days, Period::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['P0Y', 0, 0, 0],
            ['P0M', 0, 0, 0],
            ['P0W', 0, 0, 0],
            ['P0D', 0, 0, 0],
            ['P1Y', 1, 0, 0],
            ['P1M', 0, 1, 0],
            ['P1W', 0, 0, 7],
            ['P1D', 0, 0, 1],
            ['P1Y2M3W4D', 1, 2, 25],
            ['P-1Y-2M-3W-4D', -1, -2, -25],
            ['P-1Y-2M-3W+4D', -1, -2, -17],
            ['-P-1Y-2M-3W4D', 1, 2, 17],
            ['+P-1Y-2M+3W-4D', -1, -2, 17],
            ['-P-1Y-2M+3W-4D', 1, 2, -17]
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
        Period::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            [' P0D'],
            ['P0D '],
            ['P0'],
            ['PD'],
            ['0D'],
            ['PXD'],
            ['PT1S'],
            ['P0D0D'],
            ['PT0D1S']
        ];
    }

    public function testPlusYears()
    {
        $this->assertPeriodEquals(11, 2, 3, Period::of(1, 2, 3)->plusYears(10));
    }

    public function testPlusMonths()
    {
        $this->assertPeriodEquals(1, 12, 3, Period::of(1, 2, 3)->plusMonths(10));
    }

    public function testPlusDays()
    {
        $this->assertPeriodEquals(1, 2, 13, Period::of(1, 2, 3)->plusDays(10));
    }

    public function testMinusYears()
    {
        $this->assertPeriodEquals(-1, 2, 3, Period::of(1, 2, 3)->minusYears(2));
    }

    public function testMinusMonths()
    {
        $this->assertPeriodEquals(1, -2, 3, Period::of(1, 2, 3)->minusMonths(4));
    }

    public function testMinusDays()
    {
        $this->assertPeriodEquals(1, 2, -3, Period::of(1, 2, 3)->minusDays(6));
    }

    public function testWithYears()
    {
        $this->assertPeriodEquals(9, 2, 3, Period::of(1, 2, 3)->withYears(9));
    }

    public function testWithMonths()
    {
        $this->assertPeriodEquals(1, 9, 3, Period::of(1, 2, 3)->withMonths(9));
    }

    public function testWithDays()
    {
        $this->assertPeriodEquals(1, 2, 9, Period::of(1, 2, 3)->withDays(9));
    }

    public function testMultipliedBy()
    {
        $this->assertPeriodEquals(-2, -4, -6, Period::of(1, 2, 3)->multipliedBy(-2));
    }

    public function testNegated()
    {
        $this->assertPeriodEquals(-7, -8, -9, Period::of(7, 8, 9)->negated());
    }

    /**
     * @dataProvider providerNormalized
     *
     * @param integer $y  The years of the period to normalize.
     * @param integer $m  The months of the period to normalize.
     * @param integer $d  The days of the period to normalize.
     * @param integer $ny The years of the normalized period.
     * @param integer $nm The months of the normalized period.
     */
    public function testNormalized($y, $m, $d, $ny, $nm)
    {
        $this->assertPeriodEquals($ny, $nm, $d, Period::of($y, $m, $d)->normalized());
    }

    /**
     * @return array
     */
    public function providerNormalized()
    {
        return [
            [1, 2, 3, 1, 2],
            [1, 12, 1, 2, 0],
            [1, 13, 2, 2, 1],
            [1, -12, 1, 0, 0],
            [1, -13, 0, 0, -1],
            [0, 14, 0, 1, 2],
            [0, -14, 0, -1, -2],
            [-2, 6, 7, -1, -6]
        ];
    }

    /**
     * @dataProvider providerIsZero
     *
     * @param integer $years  The number of years in the period.
     * @param integer $months The number of months in the period.
     * @param integer $days   The number of days in the period.
     * @param boolean $isZero The expected return value.
     */
    public function testIsZero($years, $months, $days, $isZero)
    {
        $this->assertSame($isZero, Period::of($years, $months, $days)->isZero());
    }

    /**
     * @return array
     */
    public function providerIsZero()
    {
        return [
            [0, 0, 0, true],
            [1, 0, 0, false],
            [0, 1, 0, false],
            [0, 0, 1, false]
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param integer $y1      The number of years in the 1st period.
     * @param integer $m1      The number of months in the 1st period.
     * @param integer $d1      The number of days in the 1st period.
     * @param integer $y2      The number of years in the 2nd period.
     * @param integer $m2      The number of months in the 2nd period.
     * @param integer $d2      The number of days in the 2nd period.
     * @param boolean $isEqual The expected return value.
     */
    public function testIsEqualTo($y1, $m1, $d1, $y2, $m2, $d2, $isEqual)
    {
        $p1 = Period::of($y1, $m1, $d1);
        $p2 = Period::of($y2, $m2, $d2);

        $this->assertSame($isEqual, $p1->isEqualTo($p2));
        $this->assertSame($isEqual, $p2->isEqualTo($p1));
    }

    /**
     * @return array
     */
    public function providerIsEqualTo()
    {
        return [
            [0, 0, 0, 0, 0, 0, true],
            [0, 0, 0, 0, 0, 1, false],
            [0, 0, 0, 0, 0, -1, false],
            [1, 1, 1, 1, 1, 1, true],
            [1, 1, 1, 1, 2, 1, false],
            [-1, -1, -1, -1, -1, -1, true],
            [-1, -1, -1, -1, -2, -1, false],
            [2, 2, 2, 2, 2, 2, true],
            [2, 2, 2, 3, 2, 2, false],
            [-2, -2, -2, -2, -2, -2, true],
            [-2, -2, -2, -3, -2, -2, false],
        ];
    }

    /**
     * @dataProvider providerToDateInterval
     *
     * @param integer $years
     * @param integer $months
     * @param integer $days
     */
    public function testToDateInterval($years, $months, $days)
    {
        $period = Period::of($years, $months, $days);
        $dateInterval = $period->toDateInterval();

        $this->assertSame($years, $dateInterval->y);
        $this->assertSame($months, $dateInterval->m);
        $this->assertSame($days, $dateInterval->d);
    }

    /**
     * @return array
     */
    public function providerToDateInterval()
    {
        return [
            [1, -2, 3],
            [-1, 2, -3]
        ];
    }

    /**
     * *@dataProvider providerToString
     *
     * @param integer $years    The number of years in the period.
     * @param integer $months   The number of months in the period.
     * @param integer $days     The number of days in the period.
     * @param string  $expected The expected string output.
     */
    public function testToString($years, $months, $days, $expected)
    {
        $this->assertSame($expected, (string) Period::of($years, $months, $days));
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [0, 0, 0, 'P0D'],
            [0, 0, 1, 'P1D'],
            [0, 1, 0, 'P1M'],
            [0, 1, 2, 'P1M2D'],
            [1, 0, 0, 'P1Y'],
            [1, 0, 2, 'P1Y2D'],
            [1, 2, 0, 'P1Y2M'],
            [1, 2, 3, 'P1Y2M3D'],

            [0, 0, -1, 'P-1D'],
            [0, -1, 0, 'P-1M'],
            [0, -1, -2, 'P-1M-2D'],
            [-1, 0, 0, 'P-1Y'],
            [-1, 0, -2, 'P-1Y-2D'],
            [-1, -2, 0, 'P-1Y-2M'],
            [-1, -2, -3, 'P-1Y-2M-3D'],
        ];
    }
}
