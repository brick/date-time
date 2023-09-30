<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZone;

use function array_map;
use function iterator_count;
use function iterator_to_array;
use function json_encode;

/**
 * Unit tests for class LocalDateRange.
 */
class LocalDateRangeTest extends AbstractTestCase
{
    public function testOf(): void
    {
        self::assertLocalDateRangeIs(2001, 2, 3, 2004, 5, 6, LocalDateRange::of(
            LocalDate::of(2001, 2, 3),
            LocalDate::of(2004, 5, 6)
        ));
    }

    public function testOfInvalidRangeThrowsException(): void
    {
        $this->expectException(DateTimeException::class);

        LocalDateRange::of(
            LocalDate::of(2001, 2, 3),
            LocalDate::of(2001, 2, 2)
        );
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text The text to parse.
     * @param int    $y1   The expected start year.
     * @param int    $m1   The expected start month.
     * @param int    $d1   The expected start day.
     * @param int    $y2   The expected end year.
     * @param int    $m2   The expected end month.
     * @param int    $d2   The expected end day.
     */
    public function testParse(string $text, int $y1, int $m1, int $d1, int $y2, int $m2, int $d2): void
    {
        self::assertLocalDateRangeIs($y1, $m1, $d1, $y2, $m2, $d2, LocalDateRange::parse($text));
    }

    public function providerParse(): array
    {
        return [
            ['2001-02-03/04', 2001, 2, 3, 2001, 2, 4],
            ['2001-02-03/04-05', 2001, 2, 3, 2001, 4, 5],
            ['2001-02-03/2004-05-06', 2001, 2, 3, 2004, 5, 6],
        ];
    }

    /**
     * @dataProvider providerParseInvalidRangeThrowsException
     *
     * @param string $text The invalid text to parse.
     */
    public function testParseInvalidRangeThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        LocalDateRange::parse($text);
    }

    public function providerParseInvalidRangeThrowsException(): array
    {
        return [
            ['2001-02-03'],
            ['2001-02-03/'],
            ['2001-02-03/2004'],
            ['2001-02-03/2004-'],
            ['2001-02-03/2004-05'],
            ['2001-02-03/2004-05-'],
            ['2001-02-03/-04-05'],
            ['2001-02-03/-04'],
            ['2001-02-03/04-'],
            [' 2001-02-03/04'],
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param string $testRange The string representation of the range to test.
     * @param bool   $isEqual   Whether this range is expected to be equal to our range.
     */
    public function testIsEqualTo(string $testRange, bool $isEqual): void
    {
        self::assertSame($isEqual, LocalDateRange::of(
            LocalDate::of(2001, 2, 3),
            LocalDate::of(2004, 5, 6)
        )->isEqualTo(LocalDateRange::parse($testRange)));
    }

    public function providerIsEqualTo(): array
    {
        return [
            ['2001-02-03/2004-05-06', true],
            ['2000-02-03/2004-05-06', false],
            ['2001-01-03/2004-05-06', false],
            ['2001-02-01/2004-05-06', false],
            ['2001-02-03/2009-05-06', false],
            ['2001-02-03/2004-09-06', false],
            ['2001-02-03/2004-05-09', false],
        ];
    }

    /**
     * @dataProvider providerContains
     */
    public function testContains(string $range, string $date, bool $contains): void
    {
        self::assertSame($contains, LocalDateRange::parse($range)->contains(LocalDate::parse($date)));
    }

    public function providerContains(): array
    {
        return [
            ['2001-02-03/2004-05-06', '2001-02-02', false],
            ['2001-02-03/2004-05-06', '2001-02-03', true],
            ['2001-02-03/2004-05-06', '2001-02-04', true],
            ['2001-02-03/2004-05-06', '2001-12-31', true],
            ['2001-02-03/2004-05-06', '2002-01-01', true],
            ['2001-02-03/2004-05-06', '2003-12-31', true],
            ['2001-02-03/2004-05-06', '2004-05-05', true],
            ['2001-02-03/2004-05-06', '2004-05-06', true],
            ['2001-02-03/2004-05-06', '2004-05-07', false],
        ];
    }

    public function testIterator(): void
    {
        $start = LocalDate::of(2013, 12, 29);
        $end = LocalDate::of(2014, 1, 3);

        $range = LocalDateRange::of($start, $end);

        $expected = [
            '2013-12-29',
            '2013-12-30',
            '2013-12-31',
            '2014-01-01',
            '2014-01-02',
            '2014-01-03',
        ];

        for ($i = 0; $i < 2; $i++) { // Test twice to test iterator rewind
            $actual = [];

            foreach ($range as $date) {
                $actual[] = (string) $date;
            }

            self::assertSame($expected, $actual);
        }
    }

    /**
     * @dataProvider providerCount
     *
     * @param string $range The date range string representation.
     * @param int    $count The expected day count.
     */
    public function testCount(string $range, int $count): void
    {
        self::assertCount($count, LocalDateRange::parse($range));
    }

    public function providerCount(): array
    {
        return [
            ['2010-01-01/2010-01-01', 1],
            ['2013-12-30/2014-01-02', 4],
            ['2013-01-01/2013-12-31', 365],
            ['2012-01-01/2012-12-31', 366],
            ['2000-01-01/2020-01-01', 7306],
            ['1900-01-01/2000-01-01', 36525],
        ];
    }

    public function testJsonSerialize(): void
    {
        self::assertSame(json_encode('2008-12-31/2011-01-01'), json_encode(LocalDateRange::of(
            LocalDate::of(2008, 12, 31),
            LocalDate::of(2011, 1, 1)
        )));
    }

    public function testToString(): void
    {
        self::assertSame('2008-12-31/2011-01-01', (string) LocalDateRange::of(
            LocalDate::of(2008, 12, 31),
            LocalDate::of(2011, 1, 1)
        ));
    }

    /**
     * @dataProvider providerToNativeDatePeriod
     *
     * @param string $range         The date-time string that will be parse()d by LocalDateRange.
     * @param string $expectedStart The expected output from the native DateTime object.
     * @param string $expectedEnd   The expected output from the native DateTime object.
     */
    public function testToNativeDatePeriod(string $range, string $expectedStart, string $expectedEnd): void
    {
        $range = LocalDateRange::parse($range);

        $period = $range->toNativeDatePeriod();

        $rangeArray = iterator_to_array($range);
        $periodArray = iterator_to_array($period);
        $zip = array_map(null, $rangeArray, $periodArray);

        foreach ($zip as [$date, $dateTime]) {
            self::assertTrue($date->isEqualTo(LocalDate::fromNativeDateTime($dateTime)));
        }

        self::assertSame(iterator_count($period), $range->count());
        self::assertSame($expectedStart, $period->start->format('Y-m-d\TH:i:s.uO'));
        self::assertSame($expectedEnd, $period->end->format('Y-m-d\TH:i:s.uO'));
    }

    public function providerToNativeDatePeriod(): array
    {
        return [
            ['2010-01-01/2010-01-01', '2010-01-01T00:00:00.000000+0000', '2010-01-01T23:59:59.999999+0000'],
            ['2010-01-01/2010-01-02', '2010-01-01T00:00:00.000000+0000', '2010-01-02T23:59:59.999999+0000'],
            ['2010-01-01/2010-12-31', '2010-01-01T00:00:00.000000+0000', '2010-12-31T23:59:59.999999+0000'],
        ];
    }

    /**
     * @dataProvider providerToInterval
     */
    public function testToInterval(string $range, string $timeZone, string $expectedInterval): void
    {
        $actualResult = LocalDateRange::parse($range)->toInterval(TimeZone::parse($timeZone));
        self::assertSame($expectedInterval, (string) $actualResult);
    }

    public function providerToInterval(): array
    {
        return [
            ['2010-01-01/2010-01-01', 'UTC', '2010-01-01T00:00Z/2010-01-02T00:00Z'],
            ['2010-01-01/2020-12-31', 'UTC', '2010-01-01T00:00Z/2021-01-01T00:00Z'],
            ['2022-03-20/2022-03-26', 'Europe/London', '2022-03-20T00:00Z/2022-03-27T00:00Z'],
            ['2022-03-20/2022-03-27', 'Europe/London', '2022-03-20T00:00Z/2022-03-27T23:00Z'],
            ['2022-03-20/2022-03-26', 'Europe/Berlin', '2022-03-19T23:00Z/2022-03-26T23:00Z'],
            ['2022-03-20/2022-03-27', 'Europe/Berlin', '2022-03-19T23:00Z/2022-03-27T22:00Z'],
            ['2022-01-01/2022-12-31', 'Europe/Berlin', '2021-12-31T23:00Z/2022-12-31T23:00Z'],
        ];
    }

    /**
     * @dataProvider providerIntersectsWith
     */
    public function testIntersectsWith(string $a, string $b, bool $expectedResult): void
    {
        $aRange = LocalDateRange::parse($a);
        $bRange = LocalDateRange::parse($b);

        self::assertSame($expectedResult, $aRange->intersectsWith($bRange));
        self::assertSame($expectedResult, $bRange->intersectsWith($aRange));
    }

    public function providerIntersectsWith(): array
    {
        return [
            ['2010-01-01/2010-01-01', '2010-01-01/2010-01-01', true],
            ['2010-01-01/2033-01-01', '2010-01-02/2010-01-02', true],
            ['2010-01-01/2033-02-27', '2010-01-10/2010-02-10', true],
            ['2010-01-01/2010-01-10', '2010-01-05/2010-01-15', true],
            ['2020-01-01/2021-12-31', '2021-07-15/2022-08-31', true],
            ['2010-01-01/2010-01-01', '2010-01-02/2010-01-02', false],
            ['2010-01-01/2010-01-01', '2020-01-02/2033-01-02', false],
            ['2023-01-01/2023-12-31', '2022-01-01/2022-12-31', false],
            ['2020-01-01/2021-12-31', '2022-01-01/2022-12-31', false],
        ];
    }

    /**
     * @dataProvider providerGetIntersectionWith
     */
    public function testGetIntersectionWith(string $a, string $b, string $expectedIntersection): void
    {
        $aRange = LocalDateRange::parse($a);
        $bRange = LocalDateRange::parse($b);

        self::assertSame($expectedIntersection, (string) $aRange->getIntersectionWith($bRange));
        self::assertSame($expectedIntersection, (string) $bRange->getIntersectionWith($aRange));
    }

    public function providerGetIntersectionWith(): array
    {
        return [
            ['2010-01-01/2010-01-01', '2010-01-01/2010-01-01', '2010-01-01/2010-01-01'],
            ['2010-01-01/2033-01-01', '2010-01-02/2010-01-02', '2010-01-02/2010-01-02'],
            ['2010-01-01/2033-02-27', '2010-01-10/2010-02-10', '2010-01-10/2010-02-10'],
            ['2010-01-01/2010-01-10', '2010-01-05/2010-01-15', '2010-01-05/2010-01-10'],
            ['2020-01-01/2021-12-31', '2021-07-15/2022-08-31', '2021-07-15/2021-12-31'],
            ['2020-01-01/2021-12-31', '2019-07-15/2020-08-15', '2020-01-01/2020-08-15'],
        ];
    }

    public function testGetIntersectionInvalidParams(): void
    {
        $aRange = LocalDateRange::parse('2010-01-01/2010-03-01');
        $bRange = LocalDateRange::parse('2033-01-02/2033-01-02');

        $this->expectException(DateTimeException::class);

        $aRange->getIntersectionWith($bRange);
    }

    /**
     * @dataProvider providerWithStart
     */
    public function testWithStart(string $originalRange, string $start, ?string $expectedRange): void
    {
        $originalRange = LocalDateRange::parse($originalRange);

        if ($expectedRange === null) {
            $this->expectException(DateTimeException::class);
        }

        $actualRange = $originalRange->withStart(LocalDate::parse($start));

        if ($expectedRange !== null) {
            self::assertSame($expectedRange, (string) $actualRange);
        }
    }

    public function providerWithStart(): array
    {
        return [
            ['2021-06-15/2021-07-07', '2021-05-29', '2021-05-29/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-06-14', '2021-06-14/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-06-15', '2021-06-15/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-06-16', '2021-06-16/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-07-06', '2021-07-06/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-07-07', '2021-07-07/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-07-08', null],
        ];
    }

    /**
     * @dataProvider providerWithEnd
     */
    public function testWithEnd(string $originalRange, string $end, ?string $expectedRange): void
    {
        $originalRange = LocalDateRange::parse($originalRange);

        if ($expectedRange === null) {
            $this->expectException(DateTimeException::class);
        }

        $actualRange = $originalRange->withEnd(LocalDate::parse($end));

        if ($expectedRange !== null) {
            self::assertSame($expectedRange, (string) $actualRange);
        }
    }

    public function providerWithEnd(): array
    {
        return [
            ['2021-06-15/2021-07-07', '2021-06-14', null],
            ['2021-06-15/2021-07-07', '2021-06-15', '2021-06-15/2021-06-15'],
            ['2021-06-15/2021-07-07', '2021-06-16', '2021-06-15/2021-06-16'],
            ['2021-06-15/2021-07-07', '2021-07-06', '2021-06-15/2021-07-06'],
            ['2021-06-15/2021-07-07', '2021-07-07', '2021-06-15/2021-07-07'],
            ['2021-06-15/2021-07-07', '2021-07-08', '2021-06-15/2021-07-08'],
        ];
    }

    /**
     * @dataProvider providerToPeriod
     */
    public function testToPeriod(string $dateRange, string $expectedPeriod): void
    {
        $dateRange = LocalDateRange::parse($dateRange);

        self::assertSame($expectedPeriod, (string) $dateRange->toPeriod());
    }

    public function providerToPeriod(): array
    {
        return [
            ['2020-01-28/2020-03-01', 'P1M2D'],
            ['2020-01-29/2029-03-01', 'P9Y1M1D'],
            ['2020-01-29/2030-03-01', 'P10Y1M1D'],
            ['2020-01-28/2029-03-01', 'P9Y1M1D'],
            ['2020-01-28/2030-03-01', 'P10Y1M1D'],
            ['2020-02-28/2020-04-01', 'P1M4D'],
            ['2020-02-29/2020-04-01', 'P1M3D'],
            ['2020-03-01/2020-04-01', 'P1M'],
            ['2020-03-02/2020-04-01', 'P30D'],
            ['2021-01-01/2021-01-01', 'P0D'],
            ['2021-01-28/2021-03-01', 'P1M1D'],
            ['2021-02-28/2021-04-01', 'P1M4D'],
            ['2021-02-28/2021-04-01', 'P1M4D'],
            ['2021-03-01/2021-04-01', 'P1M'],
            ['2021-03-02/2021-04-01', 'P30D'],
            ['2021-02-28/2022-04-01', 'P1Y1M4D'],
            ['2021-03-01/2022-04-01', 'P1Y1M'],
            ['2021-03-02/2022-04-01', 'P1Y30D'],
            ['2021-03-03/2022-04-01', 'P1Y29D'],
            ['2021-03-30/2022-04-01', 'P1Y2D'],
            ['2021-03-31/2022-04-01', 'P1Y1D'],
            ['2021-04-01/2022-04-01', 'P1Y'],
        ];
    }
}
