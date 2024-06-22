<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Month;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Unit tests for class Month.
 */
class MonthTest extends AbstractTestCase
{
    /**
     * @param int   $expectedValue The expected value of the constant.
     * @param Month $month         The month instance.
     */
    #[DataProvider('providerValues')]
    public function testValues(int $expectedValue, Month $month): void
    {
        self::assertSame($expectedValue, $month->value);
    }

    public static function providerValues(): array
    {
        return [
            [1, Month::JANUARY],
            [2, Month::FEBRUARY],
            [3, Month::MARCH],
            [4, Month::APRIL],
            [5, Month::MAY],
            [6, Month::JUNE],
            [7, Month::JULY],
            [8, Month::AUGUST],
            [9, Month::SEPTEMBER],
            [10, Month::OCTOBER],
            [11, Month::NOVEMBER],
            [12, Month::DECEMBER],
        ];
    }

    /**
     * @param int $month     The month value.
     * @param int $minLength The expected min length.
     */
    #[DataProvider('minLengthProvider')]
    public function testGetMinLength(int $month, int $minLength): void
    {
        self::assertSame($minLength, Month::from($month)->getMinLength());
    }

    public static function minLengthProvider(): array
    {
        return [
            [1, 31],
            [2, 28],
            [3, 31],
            [4, 30],
            [5, 31],
            [6, 30],
            [7, 31],
            [8, 31],
            [9, 30],
            [10, 31],
            [11, 30],
            [12, 31],
        ];
    }

    /**
     * @param int $month     The month value.
     * @param int $minLength The expected min length.
     */
    #[DataProvider('maxLengthProvider')]
    public function testGetMaxLength(int $month, int $minLength): void
    {
        self::assertSame($minLength, Month::from($month)->getMaxLength());
    }

    public static function maxLengthProvider(): array
    {
        return [
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
            [12, 31],
        ];
    }

    /**
     * @param int  $month          The month value, from 1 to 12.
     * @param bool $leapYear       Whether to test on a leap year.
     * @param int  $firstDayOfYear The expected first day of year.
     */
    #[DataProvider('providerFirstDayOfYear')]
    public function testFirstDayOfYear(int $month, bool $leapYear, int $firstDayOfYear): void
    {
        self::assertSame($firstDayOfYear, Month::from($month)->getFirstDayOfYear($leapYear));
    }

    public static function providerFirstDayOfYear(): array
    {
        return [
            [1, false, 1],
            [2, false, 1 + 31],
            [3, false, 1 + 31 + 28],
            [4, false, 1 + 31 + 28 + 31],
            [5, false, 1 + 31 + 28 + 31 + 30],
            [6, false, 1 + 31 + 28 + 31 + 30 + 31],
            [7, false, 1 + 31 + 28 + 31 + 30 + 31 + 30],
            [8, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31],
            [9, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31],
            [10, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30],
            [11, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31],
            [12, false, 1 + 31 + 28 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30],

            [1, true, 1],
            [2, true, 1 + 31],
            [3, true, 1 + 31 + 29],
            [4, true, 1 + 31 + 29 + 31],
            [5, true, 1 + 31 + 29 + 31 + 30],
            [6, true, 1 + 31 + 29 + 31 + 30 + 31],
            [7, true, 1 + 31 + 29 + 31 + 30 + 31 + 30],
            [8, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31],
            [9, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31],
            [10, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30],
            [11, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31],
            [12, true, 1 + 31 + 29 + 31 + 30 + 31 + 30 + 31 + 31 + 30 + 31 + 30],
        ];
    }

    /**
     * @param Month $month          The month to test.
     * @param bool  $leapYear       Whether to test on a leap year.
     * @param int   $expectedLength The expected month length.
     */
    #[DataProvider('providerGetLength')]
    public function testGetLength(Month $month, bool $leapYear, int $expectedLength): void
    {
        self::assertSame($expectedLength, $month->getLength($leapYear));
    }

    public static function providerGetLength(): array
    {
        return [
            [Month::JANUARY, false, 31],
            [Month::FEBRUARY, false, 28],
            [Month::MARCH, false, 31],
            [Month::APRIL, false, 30],
            [Month::MAY, false, 31],
            [Month::JUNE, false, 30],
            [Month::JULY, false, 31],
            [Month::AUGUST, false, 31],
            [Month::SEPTEMBER, false, 30],
            [Month::OCTOBER, false, 31],
            [Month::NOVEMBER, false, 30],
            [Month::DECEMBER, false, 31],

            [Month::JANUARY, true, 31],
            [Month::FEBRUARY, true, 29],
            [Month::MARCH, true, 31],
            [Month::APRIL, true, 30],
            [Month::MAY, true, 31],
            [Month::JUNE, true, 30],
            [Month::JULY, true, 31],
            [Month::AUGUST, true, 31],
            [Month::SEPTEMBER, true, 30],
            [Month::OCTOBER, true, 31],
            [Month::NOVEMBER, true, 30],
            [Month::DECEMBER, true, 31],
        ];
    }

    public function testPlusMinusEntireYears(): void
    {
        foreach (Month::cases() as $month) {
            foreach ([-24, -12, 0, 12, 24] as $monthsToAdd) {
                self::assertSame($month, $month->plus($monthsToAdd));
                self::assertSame($month, $month->minus($monthsToAdd));
            }
        }
    }

    /**
     * @param Month $month         The base month.
     * @param int   $plusMonths    The number of months to add.
     * @param Month $expectedMonth The expected.
     */
    #[DataProvider('providerPlus')]
    public function testPlus(Month $month, int $plusMonths, Month $expectedMonth): void
    {
        self::assertSame($expectedMonth, $month->plus($plusMonths));
    }

    /**
     * @param Month $month         The base month.
     * @param int   $plusMonths    The number of months to add.
     * @param Month $expectedMonth The expected month.
     */
    #[DataProvider('providerPlus')]
    public function testMinus(Month $month, int $plusMonths, Month $expectedMonth): void
    {
        self::assertSame($expectedMonth, $month->minus(-$plusMonths));
    }

    public static function providerPlus(): Generator
    {
        for ($month = Month::JANUARY->value; $month <= Month::DECEMBER->value; $month++) {
            for ($plusMonths = -25; $plusMonths <= 25; $plusMonths++) {
                $expectedMonth = $month + $plusMonths;

                while ($expectedMonth < 1) {
                    $expectedMonth += 12;
                }
                while ($expectedMonth > 12) {
                    $expectedMonth -= 12;
                }

                yield [Month::from($month), $plusMonths, Month::from($expectedMonth)];
            }
        }
    }

    /**
     * @param Month  $month        The month.
     * @param string $expectedName The expected month name.
     */
    #[DataProvider('providerToString')]
    public function testJsonSerialize(Month $month, string $expectedName): void
    {
        self::assertSame(json_encode($expectedName, JSON_THROW_ON_ERROR), json_encode($month, JSON_THROW_ON_ERROR));
    }

    /**
     * @param Month  $month        The month.
     * @param string $expectedName The expected month name.
     */
    #[DataProvider('providerToString')]
    public function testToString(Month $month, string $expectedName): void
    {
        self::assertSame($expectedName, $month->toString());
    }

    public static function providerToString(): array
    {
        return [
            [Month::JANUARY, 'January'],
            [Month::FEBRUARY, 'February'],
            [Month::MARCH, 'March'],
            [Month::APRIL, 'April'],
            [Month::MAY, 'May'],
            [Month::JUNE, 'June'],
            [Month::JULY, 'July'],
            [Month::AUGUST, 'August'],
            [Month::SEPTEMBER, 'September'],
            [Month::OCTOBER, 'October'],
            [Month::NOVEMBER, 'November'],
            [Month::DECEMBER, 'December'],
        ];
    }
}
