<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\YearMonth;
use Brick\DateTime\YearMonthRange;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Unit tests for class YearMonthRange.
 */
class YearMonthRangeTest extends AbstractTestCase
{
    public function testOf(): void
    {
        self::assertYearMonthRangeIs(2001, 2, 2004, 5, YearMonthRange::of(
            YearMonth::of(2001, 2),
            YearMonth::of(2004, 5),
        ));
    }

    public function testOfInvalidRangeThrowsException(): void
    {
        $this->expectException(DateTimeException::class);

        YearMonthRange::of(
            YearMonth::of(2001, 3),
            YearMonth::of(2001, 2),
        );
    }

    /**
     * @param string $text The text to parse.
     * @param int    $y1   The expected start year.
     * @param int    $m1   The expected start month.
     * @param int    $y2   The expected end year.
     * @param int    $m2   The expected end month.
     */
    #[DataProvider('providerParse')]
    public function testParse(string $text, int $y1, int $m1, int $y2, int $m2): void
    {
        self::assertYearMonthRangeIs($y1, $m1, $y2, $m2, YearMonthRange::parse($text));
    }

    public static function providerParse(): array
    {
        return [
            ['2001-02/04', 2001, 2, 2001, 4],
            ['2001-02/2002-01', 2001, 2, 2002, 1],
        ];
    }

    /**
     * @param string $text The invalid text to parse.
     */
    #[DataProvider('providerParseInvalidRangeThrowsException')]
    public function testParseInvalidRangeThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        YearMonthRange::parse($text);
    }

    public static function providerParseInvalidRangeThrowsException(): array
    {
        return [
            ['2001-02'],
            ['2001-02/'],
            ['2001-02/2004'],
            ['2001-02/2004-'],
            ['2001-02/-05'],
            ['2001-02/05-'],
            [' 2001-02/03'],
        ];
    }

    /**
     * @param string $testRange The string representation of the range to test.
     * @param bool   $isEqual   Whether this range is expected to be equal to our range.
     */
    #[DataProvider('providerIsEqualTo')]
    public function testIsEqualTo(string $testRange, bool $isEqual): void
    {
        self::assertSame($isEqual, YearMonthRange::of(
            YearMonth::of(2001, 2),
            YearMonth::of(2004, 5),
        )->isEqualTo(YearMonthRange::parse($testRange)));
    }

    public static function providerIsEqualTo(): array
    {
        return [
            ['2001-02/2004-05', true],
            ['2000-02/2004-05', false],
            ['2001-03/2004-05', false],
            ['2001-02/2005-05', false],
            ['2001-02/2004-06', false],
        ];
    }

    #[DataProvider('providerContains')]
    public function testContains(string $range, string $yearMonth, bool $contains): void
    {
        self::assertSame($contains, YearMonthRange::parse($range)->contains(YearMonth::parse($yearMonth)));
    }

    public static function providerContains(): array
    {
        return [
            ['2001-05/2004-02', '2001-04', false],
            ['2001-05/2004-02', '2001-05', true],
            ['2001-05/2004-02', '2001-12', true],
            ['2001-05/2004-02', '2002-01', true],
            ['2001-05/2004-02', '2002-12', true],
            ['2001-05/2004-02', '2003-01', true],
            ['2001-05/2004-02', '2003-12', true],
            ['2001-05/2004-02', '2004-01', true],
            ['2001-05/2004-02', '2004-02', true],
            ['2001-05/2004-02', '2004-03', false],
        ];
    }

    public function testIterator(): void
    {
        $start = YearMonth::of(2013, 10);
        $end = YearMonth::of(2014, 3);

        $range = YearMonthRange::of($start, $end);

        $expected = [
            '2013-10',
            '2013-11',
            '2013-12',
            '2014-01',
            '2014-02',
            '2014-03',
        ];

        for ($i = 0; $i < 2; $i++) { // Test twice to test iterator rewind
            $actual = [];

            foreach ($range as $yearMonth) {
                $actual[] = (string) $yearMonth;
            }

            self::assertSame($expected, $actual);
        }
    }

    /**
     * @param string $range The year-month range string representation.
     * @param int    $count The expected day count.
     */
    #[DataProvider('providerCount')]
    public function testCount(string $range, int $count): void
    {
        self::assertCount($count, YearMonthRange::parse($range));
    }

    public static function providerCount(): array
    {
        return [
            ['2010-01/2010-01', 1],
            ['2013-12/2014-01', 2],
            ['2013-01/2013-12', 12],
            ['2010-01/2022-12', 156],
            ['2000-09/2099-06', 1186],
        ];
    }

    #[DataProvider('providerToLocalDateRange')]
    public function testToLocalDateRange(string $yearMonthRange, string $expectedRange): void
    {
        self::assertSame($expectedRange, (string) YearMonthRange::parse($yearMonthRange)->toLocalDateRange());
    }

    public static function providerToLocalDateRange(): array
    {
        return [
            ['1900-01/1900-12', '1900-01-01/1900-12-31'],
            ['1900-02/1900-02', '1900-02-01/1900-02-28'],
            ['2000-01/2000-02', '2000-01-01/2000-02-29'],
            ['2001-01/2001-02', '2001-01-01/2001-02-28'],
            ['1901-01/3000-12', '1901-01-01/3000-12-31'],
        ];
    }

    #[DataProvider('providerToString')]
    public function testJsonSerialize(int $yearStart, int $monthStart, int $yearEnd, int $monthEnd, string $expectedString): void
    {
        $yearMonthRange = YearMonthRange::of(
            YearMonth::of($yearStart, $monthStart),
            YearMonth::of($yearEnd, $monthEnd),
        );

        self::assertSame(json_encode($expectedString, JSON_THROW_ON_ERROR), json_encode($yearMonthRange, JSON_THROW_ON_ERROR));
    }

    #[DataProvider('providerToString')]
    public function testToISOString(int $yearStart, int $monthStart, int $yearEnd, int $monthEnd, string $expectedString): void
    {
        $yearMonthRange = YearMonthRange::of(
            YearMonth::of($yearStart, $monthStart),
            YearMonth::of($yearEnd, $monthEnd),
        );

        self::assertSame($expectedString, $yearMonthRange->toISOString());
    }

    #[DataProvider('providerToString')]
    public function testToString(int $yearStart, int $monthStart, int $yearEnd, int $monthEnd, string $expectedString): void
    {
        $yearMonthRange = YearMonthRange::of(
            YearMonth::of($yearStart, $monthStart),
            YearMonth::of($yearEnd, $monthEnd),
        );

        self::assertSame($expectedString, (string) $yearMonthRange);
    }

    public static function providerToString(): array
    {
        return [
            [2008, 12, 2008, 12, '2008-12/2008-12'],
            [2008, 12, 2011, 1, '2008-12/2011-01'],
        ];
    }
}
