<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\YearMonth;
use Brick\DateTime\YearMonthRange;

/**
 * Unit tests for class YearMonthRange.
 */
class YearMonthRangeTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertYearMonthRangeIs(2001, 2, 2004, 5, YearMonthRange::of(
            YearMonth::of(2001, 2),
            YearMonth::of(2004, 5)
        ));
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testOfInvalidRangeThrowsException()
    {
        YearMonthRange::of(
            YearMonth::of(2001, 3),
            YearMonth::of(2001, 2)
        );
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text The text to parse.
     * @param int    $y1   The expected start year.
     * @param int    $m1   The expected start month.
     * @param int    $y2   The expected end year.
     * @param int    $m2   The expected end month.
     */
    public function testParse(string $text, int $y1, int $m1, int $y2, int $m2)
    {
        $this->assertYearMonthRangeIs($y1, $m1, $y2, $m2, YearMonthRange::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse() : array
    {
        return [
            ['2001-02/04', 2001, 2, 2001, 4],
            ['2001-02/2002-01', 2001, 2, 2002, 1],
        ];
    }

    /**
     * @dataProvider providerParseInvalidRangeThrowsException
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     *
     * @param string $text The invalid text to parse.
     */
    public function testParseInvalidRangeThrowsException(string $text)
    {
        YearMonthRange::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidRangeThrowsException() : array
    {
        return [
            ['2001-02'],
            ['2001-02/'],
            ['2001-02/2004'],
            ['2001-02/2004-'],
            ['2001-02/-05'],
            ['2001-02/05-'],
            [' 2001-02/03']
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param string $testRange The string representation of the range to test.
     * @param bool   $isEqual   Whether this range is expected to be equal to our range.
     */
    public function testIsEqualTo(string $testRange, bool $isEqual)
    {
        $this->assertSame($isEqual, YearMonthRange::of(
            YearMonth::of(2001, 2),
            YearMonth::of(2004, 5)
        )->isEqualTo(YearMonthRange::parse($testRange)));
    }

    /**
     * @return array
     */
    public function providerIsEqualTo() : array
    {
        return [
            ['2001-02/2004-05', true],
            ['2000-02/2004-05', false],
            ['2001-03/2004-05', false],
            ['2001-02/2005-05', false],
            ['2001-02/2004-06', false],
        ];
    }

    /**
     * @dataProvider providerContains
     *
     * @param string $range
     * @param string $yearMonth
     * @param bool   $contains
     */
    public function testContains(string $range, string $yearMonth, bool $contains)
    {
        $this->assertSame($contains, YearMonthRange::parse($range)->contains(YearMonth::parse($yearMonth)));
    }

    /**
     * @return array
     */
    public function providerContains() : array
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
            ['2001-05/2004-02', '2004-03', false]
        ];
    }

    public function testIterator()
    {
        $start = YearMonth::of(2013, 10);
        $end   = YearMonth::of(2014, 3);

        $range = YearMonthRange::of($start, $end);

        for ($i = 0; $i < 2; $i++) { // Test twice to test iterator rewind
            $expected = $start;
            foreach ($range as $yearMonth) {
                $this->assertTrue($yearMonth->isEqualTo($expected));
                $expected = $expected->plusMonths(1);
            }
        }
    }

    /**
     * @dataProvider providerCount
     *
     * @param string $range The year-month range string representation.
     * @param int    $count The expected day count.
     */
    public function testCount(string $range, int $count)
    {
        $this->assertCount($count, YearMonthRange::parse($range));
    }

    /**
     * @return array
     */
    public function providerCount() : array
    {
        return [
            ['2010-01/2010-01', 1],
            ['2013-12/2014-01', 2],
            ['2013-01/2013-12', 12],
            ['2010-01/2022-12', 156],
            ['2000-09/2099-06', 1186],
        ];
    }

    public function testToString()
    {
        $this->assertSame('2008-12/2011-01', (string) YearMonthRange::of(
            YearMonth::of(2008, 12),
            YearMonth::of(2011, 1)
        ));
    }
}
