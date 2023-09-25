<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\LocalDate;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Period;
use DateInterval;
use DateTimeImmutable;
use Generator;

use function json_encode;

/**
 * Unit tests for class Period.
 */
class PeriodTest extends AbstractTestCase
{
    public function testOf(): void
    {
        self::assertPeriodIs(1, 2, 3, Period::of(1, 2, 3));
    }

    public function testOfYears(): void
    {
        self::assertPeriodIs(11, 0, 0, Period::ofYears(11));
    }

    public function testOfMonths(): void
    {
        self::assertPeriodIs(0, 11, 0, Period::ofMonths(11));
    }

    public function testOfWeeks(): void
    {
        self::assertPeriodIs(0, 0, 77, Period::ofWeeks(11));
    }

    public function testOfDays(): void
    {
        self::assertPeriodIs(0, 0, 11, Period::ofDays(11));
    }

    public function testZero(): void
    {
        $zero = Period::zero();

        self::assertPeriodIs(0, 0, 0, $zero);
        self::assertSame($zero, Period::zero());
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text   The text to parse.
     * @param int    $years  The expected years in the period.
     * @param int    $months The expected months in the period.
     * @param int    $days   The expected days in the period.
     */
    public function testParse(string $text, int $years, int $months, int $days): void
    {
        self::assertPeriodIs($years, $months, $days, Period::parse($text));
    }

    public function providerParse(): array
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
            ['-P-1Y-2M+3W-4D', 1, 2, -17],
        ];
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     */
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        Period::parse($text);
    }

    public function providerParseInvalidStringThrowsException(): array
    {
        return [
            [' P0D'],
            ['P0D '],
            ['P'],
            ['-P'],
            ['+P'],
            ['P0'],
            ['PD'],
            ['0D'],
            ['PXD'],
            ['PT1S'],
            ['P0D0D'],
            ['PT0D1S'],
        ];
    }

    /**
     * @dataProvider providerFromNativeDateInterval
     */
    public function testFromNativeDateInterval(DateInterval $dateInterval, int $years, int $months, int $days): void
    {
        $period = Period::fromNativeDateInterval($dateInterval);
        self::assertPeriodIs($years, $months, $days, $period);
    }

    public function providerFromNativeDateInterval(): Generator
    {
        $withConstructor = [
            ['P0Y', 0, 0, 0],
            ['P1Y', 1, 0, 0],
            ['P2M', 0, 2, 0],
            ['P3D', 0, 0, 3],
            ['P1Y3D', 1, 0, 3],
            ['P1M2D', 0, 1, 2],
            ['P1Y2M', 1, 2, 0],
            ['P1Y2M3D', 1, 2, 3],
        ];

        foreach ($withConstructor as [$dateIntervalAsString, $years, $months, $days]) {
            $dateInterval = new DateInterval($dateIntervalAsString);

            yield [$dateInterval, $years, $months, $days];
        }

        $withDateTimeDiff = [
            ['2020-12-01', '2020-12-01', 0, 0, 0],
            ['2020-12-01', '2020-12-02', 0, 0, 1],
            ['2020-12-01', '2020-12-03', 0, 0, 2],
            ['2020-12-01', '2021-01-01', 0, 1, 0],
            ['2020-12-01', '2021-01-03', 0, 1, 2],
            ['2020-12-01', '2024-02-02', 3, 2, 1],
        ];

        foreach ($withDateTimeDiff as [$dateTime1, $dateTime2, $years, $months, $days]) {
            $dateTime1 = new DateTimeImmutable($dateTime1);
            $dateTime2 = new DateTimeImmutable($dateTime2);

            yield [$dateTime1->diff($dateTime2), $years, $months, $days];
            yield [$dateTime2->diff($dateTime1), -$years, -$months, -$days];
        }
    }

    /**
     * @dataProvider providerFromInvalidNativeDateInterval
     */
    public function testFromInvalidNativeDateInterval(string $dateTime1, string $dateTime2, string $expectedMessage): void
    {
        $dateTime1 = new DateTimeImmutable($dateTime1);
        $dateTime2 = new DateTimeImmutable($dateTime2);

        $dateInterval = $dateTime1->diff($dateTime2);

        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        Period::fromNativeDateInterval($dateInterval);
    }

    public function providerFromInvalidNativeDateInterval(): array
    {
        return [
            ['2020-01-01 00:00:00', '2020-01-01 01:00:00', 'Cannot create a Period from a DateInterval with a non-zero hour.'],
            ['2020-01-01 00:00:00', '2020-01-01 00:01:00', 'Cannot create a Period from a DateInterval with a non-zero minute.'],
            ['2020-01-01 00:00:00', '2020-01-01 00:00:01', 'Cannot create a Period from a DateInterval with a non-zero second.'],
            ['2020-01-01 00:00:00', '2020-01-01 00:00:00.001', 'Cannot create a Period from a DateInterval with a non-zero microsecond.'],
        ];
    }

    /**
     * Extensive testing is done in LocalDate::until().
     */
    public function testBetween(): void
    {
        $period = Period::between(LocalDate::of(2010, 1, 15), LocalDate::of(2011, 3, 18));
        self::assertPeriodIs(1, 2, 3, $period);
    }

    public function testWithYears(): void
    {
        self::assertPeriodIs(9, 2, 3, Period::of(1, 2, 3)->withYears(9));
    }

    public function testWithMonths(): void
    {
        self::assertPeriodIs(1, 9, 3, Period::of(1, 2, 3)->withMonths(9));
    }

    public function testWithDays(): void
    {
        self::assertPeriodIs(1, 2, 9, Period::of(1, 2, 3)->withDays(9));
    }

    public function testWithSameValuesReturnsThis(): void
    {
        $period = Period::of(1, 2, 3);

        self::assertSame($period, $period->withYears(1));
        self::assertSame($period, $period->withMonths(2));
        self::assertSame($period, $period->withDays(3));
    }

    public function testPlusYears(): void
    {
        self::assertPeriodIs(11, 2, 3, Period::of(1, 2, 3)->plusYears(10));
    }

    public function testPlusMonths(): void
    {
        self::assertPeriodIs(1, 12, 3, Period::of(1, 2, 3)->plusMonths(10));
    }

    public function testPlusDays(): void
    {
        self::assertPeriodIs(1, 2, 13, Period::of(1, 2, 3)->plusDays(10));
    }

    public function testPlusZeroReturnsThis(): void
    {
        $period = Period::of(1, 2, 3);

        self::assertSame($period, $period->plusYears(0));
        self::assertSame($period, $period->plusMonths(0));
        self::assertSame($period, $period->plusDays(0));
    }

    public function testMinusYears(): void
    {
        self::assertPeriodIs(-1, 2, 3, Period::of(1, 2, 3)->minusYears(2));
    }

    public function testMinusMonths(): void
    {
        self::assertPeriodIs(1, -2, 3, Period::of(1, 2, 3)->minusMonths(4));
    }

    public function testMinusDays(): void
    {
        self::assertPeriodIs(1, 2, -3, Period::of(1, 2, 3)->minusDays(6));
    }

    public function testMinusZeroReturnsThis(): void
    {
        $period = Period::of(1, 2, 3);

        self::assertSame($period, $period->minusYears(0));
        self::assertSame($period, $period->minusMonths(0));
        self::assertSame($period, $period->minusDays(0));
    }

    public function testMultipliedBy(): void
    {
        self::assertPeriodIs(-2, -4, -6, Period::of(1, 2, 3)->multipliedBy(-2));
    }

    public function testMultipliedByOneReturnsThis(): void
    {
        $period = Period::of(1, 2, 3);
        self::assertSame($period, $period->multipliedBy(1));
    }

    public function testNegated(): void
    {
        self::assertPeriodIs(-7, -8, -9, Period::of(7, 8, 9)->negated());
    }

    public function testZeroNegatedReturnsThis(): void
    {
        $period = Period::zero();
        self::assertSame($period, $period->negated());
    }

    /**
     * @dataProvider providerNormalized
     *
     * @param int $y  The years of the period to normalize.
     * @param int $m  The months of the period to normalize.
     * @param int $d  The days of the period to normalize.
     * @param int $ny The years of the normalized period.
     * @param int $nm The months of the normalized period.
     */
    public function testNormalized(int $y, int $m, int $d, int $ny, int $nm): void
    {
        self::assertPeriodIs($ny, $nm, $d, Period::of($y, $m, $d)->normalized());
    }

    public function providerNormalized(): array
    {
        return [
            [1, 2, 3, 1, 2],
            [1, 12, 1, 2, 0],
            [1, 13, 2, 2, 1],
            [1, -12, 1, 0, 0],
            [1, -13, 0, 0, -1],
            [0, 14, 0, 1, 2],
            [0, -14, 0, -1, -2],
            [-2, 6, 7, -1, -6],
        ];
    }

    /**
     * @dataProvider providerIsZero
     *
     * @param int  $years  The number of years in the period.
     * @param int  $months The number of months in the period.
     * @param int  $days   The number of days in the period.
     * @param bool $isZero The expected return value.
     */
    public function testIsZero(int $years, int $months, int $days, bool $isZero): void
    {
        self::assertSame($isZero, Period::of($years, $months, $days)->isZero());
    }

    public function providerIsZero(): array
    {
        return [
            [0, 0, 0, true],
            [1, 0, 0, false],
            [0, 1, 0, false],
            [0, 0, 1, false],
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param int  $y1      The number of years in the 1st period.
     * @param int  $m1      The number of months in the 1st period.
     * @param int  $d1      The number of days in the 1st period.
     * @param int  $y2      The number of years in the 2nd period.
     * @param int  $m2      The number of months in the 2nd period.
     * @param int  $d2      The number of days in the 2nd period.
     * @param bool $isEqual The expected return value.
     */
    public function testIsEqualTo(int $y1, int $m1, int $d1, int $y2, int $m2, int $d2, bool $isEqual): void
    {
        $p1 = Period::of($y1, $m1, $d1);
        $p2 = Period::of($y2, $m2, $d2);

        self::assertSame($isEqual, $p1->isEqualTo($p2));
        self::assertSame($isEqual, $p2->isEqualTo($p1));
    }

    public function providerIsEqualTo(): array
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
     * @dataProvider providerToNativeDateInterval
     */
    public function testToNativeDateInterval(int $years, int $months, int $days): void
    {
        $period = Period::of($years, $months, $days);
        $dateInterval = $period->toNativeDateInterval();

        self::assertSame($years, $dateInterval->y);
        self::assertSame($months, $dateInterval->m);
        self::assertSame($days, $dateInterval->d);
    }

    public function providerToNativeDateInterval(): array
    {
        return [
            [1, -2, 3],
            [-1, 2, -3],
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $years    The number of years in the period.
     * @param int    $months   The number of months in the period.
     * @param int    $days     The number of days in the period.
     * @param string $expected The expected string output.
     */
    public function testJsonSerialize(int $years, int $months, int $days, string $expected): void
    {
        self::assertSame(json_encode($expected), json_encode(Period::of($years, $months, $days)));
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $years    The number of years in the period.
     * @param int    $months   The number of months in the period.
     * @param int    $days     The number of days in the period.
     * @param string $expected The expected string output.
     */
    public function testToString(int $years, int $months, int $days, string $expected): void
    {
        self::assertSame($expected, (string) Period::of($years, $months, $days));
    }

    public function providerToString(): array
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
