<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;

/**
 * Unit tests for class LocalDateRange.
 */
class LocalDateRangeTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertLocalDateRangeEquals(2001, 2, 3, 2004, 5, 6, LocalDateRange::of(
            LocalDate::of(2001, 2, 3),
            LocalDate::of(2004, 5, 6)
        ));
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testOfInvalidRangeThrowsException()
    {
        LocalDateRange::of(
            LocalDate::of(2001, 2, 3),
            LocalDate::of(2001, 2, 2)
        );
    }

    public function testParse()
    {
        $range = LocalDateRange::parse('2008-01-01/2009-12-31');
        $this->assertLocalDateRangeEquals(2008, 1, 1, 2009, 12, 31, $range);
    }

    /**
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     */
    public function testParseInvalidRangeThrowsException()
    {
        LocalDateRange::parse('2010-01-01');
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param string  $testRange The string representation of the range to test.
     * @param boolean $isEqual   Whether this range is expected to be equal to our range.
     */
    public function testIsEqualTo($testRange, $isEqual)
    {
        $this->assertSame($isEqual, LocalDateRange::of(
            LocalDate::of(2001, 2, 3),
            LocalDate::of(2004, 5, 6)
        )->isEqualTo(LocalDateRange::parse($testRange)));
    }

    /**
     * @return array
     */
    public function providerIsEqualTo()
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
     *
     * @param string  $range
     * @param string  $date
     * @param boolean $contains
     */
    public function testContains($range, $date, $contains)
    {
        $this->assertSame($contains, LocalDateRange::parse($range)->contains(LocalDate::parse($date)));
    }

    /**
     * @return array
     */
    public function providerContains()
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
            ['2001-02-03/2004-05-06', '2004-05-07', false]
        ];
    }

    public function testIterator()
    {
        $start = LocalDate::of(2013, 12, 30);
        $end   = LocalDate::of(2014, 1, 2);

        $range = LocalDateRange::of($start, $end);

        for ($i = 0; $i < 2; $i++) { // Test twice to test iterator rewind
            $expected = $start;
            foreach ($range as $date) {
                /** @var LocalDate $date */
                $this->assertTrue($date->isEqualTo($expected));
                $expected = $expected->plusDays(1);
            }
        }
    }

    /**
     * @dataProvider providerCount
     *
     * @param string  $range The date range string representation.
     * @param integer $count The expected day count.
     */
    public function testCount($range, $count)
    {
        $this->assertCount($count, LocalDateRange::parse($range));
    }

    /**
     * @return array
     */
    public function providerCount()
    {
        return [
            ['2010-01-01/2010-01-01', 1],
            ['2013-12-30/2014-01-02', 4],
            ['2013-01-01/2013-12-31', 365],
            ['2012-01-01/2012-12-31', 366],
            ['2000-01-01/2020-01-01', 7306],
            ['1900-01-01/2000-01-01', 36525]
        ];
    }

    public function testToString()
    {
        $this->assertSame('2008-12-31/2011-01-01', (string) LocalDateRange::of(
            LocalDate::of(2008, 12, 31),
            LocalDate::of(2011, 1, 1)
        ));
    }
}
