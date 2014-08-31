<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Month;

/**
 * Unit tests for class Month.
 */
class MonthTest extends AbstractTestCase
{
    /**
     * @dataProvider providerConstants
     *
     * @param integer $expectedValue The expected value of the constant.
     * @param integer $monthConstant The month constant.
     */
    public function testConstants($expectedValue, $monthConstant)
    {
        $this->assertSame($expectedValue, $monthConstant);
    }

    /**
     * @return array
     */
    public function providerConstants()
    {
        return [
            [ 1, Month::JANUARY],
            [ 2, Month::FEBRUARY],
            [ 3, Month::MARCH],
            [ 4, Month::APRIL],
            [ 5, Month::MAY],
            [ 6, Month::JUNE],
            [ 7, Month::JULY],
            [ 8, Month::AUGUST],
            [ 9, Month::SEPTEMBER],
            [10, Month::OCTOBER],
            [11, Month::NOVEMBER],
            [12, Month::DECEMBER]
        ];
    }

    public function testOf()
    {
        $this->assertMonthEquals(8, Month::of(8));
    }

    /**
     * @dataProvider providerOfInvalidMonthThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidMonth
     */
    public function testOfInvalidMonthThrowsException($invalidMonth)
    {
        Month::of($invalidMonth);
    }

    /**
     * @return array
     */
    public function providerOfInvalidMonthThrowsException()
    {
        return [
            [-1],
            [0],
            [13]
        ];
    }

    public function testGetAll()
    {
        $currentMonth = Month::JANUARY;

        foreach (Month::getAll() as $month) {
            $this->assertMonthEquals($currentMonth, $month);
            $currentMonth++;
        }
    }

    public function testIs()
    {
        for ($i = Month::JANUARY; $i <= Month::DECEMBER; $i++) {
            for ($j = Month::JANUARY; $j <= Month::DECEMBER; $j++) {
                $this->assertSame($i === $j, Month::of($i)->is($j));
            }
        }
    }

    public function testIsEqualTo()
    {
        for ($i = Month::JANUARY; $i <= Month::DECEMBER; $i++) {
            for ($j = Month::JANUARY; $j <= Month::DECEMBER; $j++) {
                $this->assertSame($i === $j, Month::of($i)->isEqualTo(Month::of($j)));
            }
        }
    }

    /**
     * @dataProvider minLengthProvider
     *
     * @param integer $month     The month value.
     * @param integer $minLength The expected min length.
     */
    public function testGetMinLength($month, $minLength)
    {
        $this->assertSame($minLength, Month::of($month)->getMinLength());
    }

    /**
     * @return array
     */
    public function minLengthProvider()
    {
        return [
            [ 1, 31],
            [ 2, 28],
            [ 3, 31],
            [ 4, 30],
            [ 5, 31],
            [ 6, 30],
            [ 7, 31],
            [ 8, 31],
            [ 9, 30],
            [10, 31],
            [11, 30],
            [12, 31]
        ];
    }

    /**
     * @dataProvider maxLengthProvider
     *
     * @param integer $month     The month value.
     * @param integer $minLength The expected min length.
     */
    public function testGetMaxLength($month, $minLength)
    {
        $this->assertSame($minLength, Month::of($month)->getMaxLength());
    }

    /**
     * @return array
     */
    public function maxLengthProvider()
    {
        return [
            [ 1, 31],
            [ 2, 29],
            [ 3, 31],
            [ 4, 30],
            [ 5, 31],
            [ 6, 30],
            [ 7, 31],
            [ 8, 31],
            [ 9, 30],
            [10, 31],
            [11, 30],
            [12, 31]
        ];
    }

    /**
     * @dataProvider providerFirstDayOfYear
     *
     * @param integer $month          The month value, from 1 to 12.
     * @param boolean $leapYear       Whether to test on a leap year.
     * @param integer $firstDayOfYear The expected first day of year.
     */
    public function testFirstDayOfYear($month, $leapYear, $firstDayOfYear)
    {
        $this->assertSame($firstDayOfYear, Month::of($month)->getFirstDayOfYear($leapYear));
    }

    /**
     * @return array
     */
    public function providerFirstDayOfYear()
    {
        return [
            [ 1, false, 1],
            [ 2, false, 1 + 31],
            [ 3, false, 1 + 31 + 28],
            [ 4, false, 1 + 31 + 28 + 31],
            [ 5, false, 1 + 31 + 28 + 31 + 30],
            [ 6, false, 1 + 31 + 28 + 31 + 30 + 31],
            [ 7, false, 1 + 31 + 28 + 31 + 30 + 31 + 30],
            [ 8, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31],
            [ 9, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31],
            [10, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30],
            [11, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31],
            [12, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30],

            [ 1, true, 1],
            [ 2, true, 1 + 31],
            [ 3, true, 1 + 31 + 29],
            [ 4, true, 1 + 31 + 29 + 31],
            [ 5, true, 1 + 31 + 29 + 31 + 30],
            [ 6, true, 1 + 31 + 29 + 31 + 30 + 31],
            [ 7, true, 1 + 31 + 29 + 31 + 30 + 31 + 30],
            [ 8, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31],
            [ 9, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31],
            [10, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30],
            [11, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31],
            [12, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30],
        ];
    }

    /**
     * @dataProvider providerGetLength
     *
     * @param integer $month          The number of the month to test.
     * @param integer $leapYear       Whether to test on a leap year.
     * @param integer $expectedLength The expected month length.
     */
    public function testGetLength($month, $leapYear, $expectedLength)
    {
        $this->assertSame($expectedLength, Month::of($month)->getLength($leapYear));
    }

    /**
     * @return array
     */
    public function providerGetLength()
    {
        return [
            [ 1, false, 31],
            [ 2, false, 28],
            [ 3, false, 31],
            [ 4, false, 30],
            [ 5, false, 31],
            [ 6, false, 30],
            [ 7, false, 31],
            [ 8, false, 31],
            [ 9, false, 30],
            [10, false, 31],
            [11, false, 30],
            [12, false, 31],

            [ 1, true, 31],
            [ 2, true, 29],
            [ 3, true, 31],
            [ 4, true, 30],
            [ 5, true, 31],
            [ 6, true, 30],
            [ 7, true, 31],
            [ 8, true, 31],
            [ 9, true, 30],
            [10, true, 31],
            [11, true, 30],
            [12, true, 31],
        ];
    }

    public function testPlusMinusEntireYears()
    {
        foreach (Month::getAll() as $month) {
            foreach ([-24, -12, 0, 12, 24] as $monthsToAdd) {
                $this->assertTrue($month->plus($monthsToAdd)->isEqualTo($month));
                $this->assertTrue($month->minus($monthsToAdd)->isEqualTo($month));
            }
        }
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $month         The base month number.
     * @param integer $plusMonths    The number of months to add.
     * @param integer $expectedMonth The expected month number.
     */
    public function testPlus($month, $plusMonths, $expectedMonth)
    {
        $this->assertMonthEquals($expectedMonth, Month::of($month)->plus($plusMonths));
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $month         The base month number.
     * @param integer $plusMonths    The number of months to add.
     * @param integer $expectedMonth The expected month number.
     */
    public function testMinus($month, $plusMonths, $expectedMonth)
    {
        $this->assertMonthEquals($expectedMonth, Month::of($month)->minus(-$plusMonths));
    }

    /**
     * @return \Generator
     */
    public function providerPlus()
    {
        for ($month = Month::JANUARY; $month <= Month::DECEMBER; $month++) {
            for ($plusMonths = -25; $plusMonths <= 25; $plusMonths++) {
                $expectedMonth = $month + $plusMonths;

                while ($expectedMonth < 1) {
                    $expectedMonth += 12;
                }
                while ($expectedMonth > 12) {
                    $expectedMonth -= 12;
                }

                yield [$month, $plusMonths, $expectedMonth];
            }
        }
    }

    /**
     * @dataProvider providerToString
     *
     * @param string  $month        The month number.
     * @param integer $expectedName The expected month name.
     */
    public function testToString($month, $expectedName)
    {
        $this->assertSame($expectedName, (string) Month::of($month));
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [ 1, 'January'],
            [ 2, 'February'],
            [ 3, 'March'],
            [ 4, 'April'],
            [ 5, 'May'],
            [ 6, 'June'],
            [ 7, 'July'],
            [ 8, 'August'],
            [ 9, 'September'],
            [10, 'October'],
            [11, 'November'],
            [12, 'December']
        ];
    }
}
