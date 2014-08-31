<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;

/**
 * Unit tests for class LocalDateTime.
 */
class LocalDateTimeTest extends AbstractTestCase
{
    public function testOf()
    {
        $dateTime = LocalDateTime::of(2001, 12, 23, 12, 34, 56, 987654321);
        $this->assertLocalDateTimeEquals(2001, 12, 23, 12, 34, 56, 987654321, $dateTime);
    }

    /**
     * @dataProvider providerParse
     *
     * @param string  $t The text to parse.
     * @param integer $y The expected year.
     * @param integer $m The expected month.
     * @param integer $d The expected day.
     * @param integer $h The expected hour.
     * @param integer $i The expected minute.
     * @param integer $s The expected second.
     * @param integer $n The expected nano-of-second.
     */
    public function testParse($t, $y, $m, $d, $h, $i, $s, $n)
    {
        $this->assertLocalDateTimeEquals($y, $m, $d, $h, $i, $s, $n, LocalDateTime::parse($t));
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['0999-02-28T12:34', 999, 2, 28, 12, 34, 0, 0],
            ['2014-02-28T12:34', 2014, 2, 28, 12, 34, 0, 0],
            ['1999-12-31T01:02:03', 1999, 12, 31, 1, 2, 3, 0],
            ['2012-02-29T23:43:10.1234', 2012, 2, 29, 23, 43, 10, 123400000]
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
        LocalDateTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            [' 2014-02-28T12:34'],
            ['2014-02-28T12:34 '],
            ['2014-2-27T12:34'],
            ['2014-222-27T12:34'],
            ['2014-02-2T12:34'],
            ['2014-02-222T12:34'],
            ['2014-02-28T1:34'],
            ['2014-02-28T111:34'],
            ['2014-02-28T12:3'],
            ['2014-02-28T12:345'],
            ['2014-02-28T12:34:5'],
            ['2014-02-28T12:34:567'],
            ['2014-02-28T12:34:56.'],
            ['2014-02-28T12:34:56.1234567890'],
            ['201X-02-27T12:34:56.123'],
            ['2014-0X-27T12:34:56.123'],
            ['2014-02-2XT12:34:56.123'],
            ['2014-02-27T1X:34:56.123'],
            ['2014-02-27T12:3X:56.123'],
            ['2014-02-27T12:34:5X.123'],
            ['2014-02-27T12:34:56.12X'],
        ];
    }

    /**
     * @dataProvider providerParseInvalidDateTimeThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param string $text
     */
    public function testParseInvalidDateTimeThrowsException($text)
    {
        LocalDateTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidDateTimeThrowsException()
    {
        return [
            ['2014-00-15T12:34'],
            ['2014-13-15T12:34'],
            ['2014-02-00T12:34'],
            ['2014-02-29T12:34'],
            ['2014-03-32T12:34'],
            ['2014-01-01T60:00:00'],
            ['2014-01-01T00:60:00'],
            ['2014-01-01T00:00:60'],
        ];
    }

    /**
     * @dataProvider providerPlusYears
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $years            The number of years to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusYears($dateTime, $years, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusYears($years);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusYears()
    {
        return [
            ['2000-02-29T12:34', 0, '2000-02-29T12:34'],
            ['2001-02-23T12:34:56.123456789', 1, '2002-02-23T12:34:56.123456789'],
            ['2000-02-29T12:34', -1, '1999-02-28T12:34']
        ];
    }

    /**
     * @dataProvider providerPlusMonths
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $months           The number of months to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusMonths($dateTime, $months, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusMonths($months);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusMonths()
    {
        return [
            ['2001-01-31T12:34:56', 0, '2001-01-31T12:34:56'],
            ['2001-01-31T12:34:56', 1, '2001-02-28T12:34:56'],
            ['2001-04-30T12:34:56.123456789', -14, '2000-02-29T12:34:56.123456789']
        ];
    }

    /**
     * @dataProvider providerPlusDays
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $days             The number of days to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusDays($dateTime, $days, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusDays($days);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusDays()
    {
        return [
            ['1999-11-30T12:34', 0, '1999-11-30T12:34'],
            ['1999-11-30T12:34', 5000, '2013-08-08T12:34'],
            ['2000-11-30T12:34:56.123456789', -500, '1999-07-19T12:34:56.123456789']
        ];
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $hours            The number of hours to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusHours($dateTime, $hours, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusHours($hours);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusHours()
    {
        return [
            ['1999-11-30T12:34:56', 0, '1999-11-30T12:34:56'],
            ['1999-11-30T12:34:56', 123456, '2013-12-30T12:34:56'],
            ['2000-11-30T12:34:56.123456789', -654321, '1926-04-10T03:34:56.123456789']
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $minutes          The number of minutes to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusMinutes($dateTime, $minutes, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusMinutes($minutes);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes()
    {
        return [
            ['1999-11-30T12:34:56', 0, '1999-11-30T12:34:56'],
            ['1999-11-30T12:34:56', 123456789, '2234-08-24T09:43:56'],
            ['2000-11-30T12:34:56.123456789', -987654321, '0123-01-24T11:13:56.123456789']
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $seconds          The number of seconds to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusSeconds($dateTime, $seconds, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusSeconds($seconds);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds()
    {
        return [
            ['1999-11-30T12:34:56', 0, '1999-11-30T12:34:56'],
            ['1999-11-30T12:34:56', 123456789, '2003-10-29T10:08:05'],
            ['2000-11-30T12:34:56.123456789', -987654321, '1969-08-14T08:09:35.123456789']
        ];
    }

    /**
     * @dataProvider providerPlusNanos
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $nanosToAdd       The nanoseconds to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusNanos($dateTime, $nanosToAdd, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusNanos($nanosToAdd);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusNanos()
    {
        return [
            ['2000-03-01T00:00', 0, '2000-03-01T00:00'],
            ['2014-12-31T23:59:58.5', 1500000000, '2015-01-01T00:00'],
            ['2000-03-01T00:00', -1, '2000-02-29T23:59:59.999999999'],
            ['2000-01-01T00:00:01', -1999999999, '1999-12-31T23:59:59.000000001']
        ];
    }

    /**
     * @dataProvider providerMinusYears
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $years            The number of years to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusYears($dateTime, $years, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusYears($years);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusYears()
    {
        return [
            ['2000-02-29T12:34', 0, '2000-02-29T12:34'],
            ['2000-02-29T12:34', 1, '1999-02-28T12:34'],
            ['2000-02-29T12:34:56.123456789', -1, '2001-02-28T12:34:56.123456789']
        ];
    }

    /**
     * @dataProvider providerMinusMonths
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $months           The number of months to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusMonths($dateTime, $months, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusMonths($months);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusMonths()
    {
        return [
            ['2001-01-31T12:34:56', 0, '2001-01-31T12:34:56'],
            ['2001-04-30T12:34:56.123456789', 14, '2000-02-29T12:34:56.123456789'],
            ['2001-01-31T12:34:56', -1, '2001-02-28T12:34:56']
        ];
    }

    /**
     * @dataProvider providerMinusDays
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $days             The number of days to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusDays($dateTime, $days, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusDays($days);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusDays()
    {
        return [
            ['1999-11-30T12:34', 0, '1999-11-30T12:34'],
            ['2000-11-30T12:34:56.123456789', 123456, '1662-11-26T12:34:56.123456789'],
            ['1999-11-30T12:34', -654321, '3791-05-20T12:34']
        ];
    }

    /**
     * @dataProvider providerMinusHours
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $hours            The number of hours to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusHours($dateTime, $hours, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusHours($hours);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusHours()
    {
        return [
            ['1999-11-30T12:34:56', 0, '1999-11-30T12:34:56'],
            ['2000-11-30T12:34:56.123456789', 123456, '1986-10-31T12:34:56.123456789'],
            ['1999-11-30T12:34:56', -654321, '2074-07-22T21:34:56']
        ];
    }

    /**
     * @dataProvider providerMinusMinutes
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $minutes          The number of minutes to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusMinutes($dateTime, $minutes, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusMinutes($minutes);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusMinutes()
    {
        return [
            ['1999-11-30T12:34:56', 0, '1999-11-30T12:34:56'],
            ['2000-11-30T12:34:56.123456789', 123456789, '1766-03-08T15:25:56.123456789'],
            ['1999-11-30T12:34:56', -987654321, '3877-10-06T13:55:56'],
        ];
    }

    /**
     * @dataProvider providerMinusSeconds
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $seconds          The number of seconds to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusSeconds($dateTime, $seconds, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusSeconds($seconds);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusSeconds()
    {
        return [
            ['1999-11-30T12:34:56', 0, '1999-11-30T12:34:56'],
            ['2000-11-30T12:34:56.123456789', 123456789, '1997-01-01T15:01:47.123456789'],
            ['1999-11-30T12:34:56', -987654321, '2031-03-18T17:00:17'],
        ];
    }

    /**
     * @dataProvider providerMinusNanos
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $nanosToSubtract  The nanoseconds to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusNanos($dateTime, $nanosToSubtract, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusNanos($nanosToSubtract);
        $this->assertSame($expectedDateTime, (string)$actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusNanos()
    {
        return [
            ['2000-03-01T00:00', 0, '2000-03-01T00:00'],
            ['2014-12-31T23:59:59.5', -500000000, '2015-01-01T00:00'],
            ['2001-03-01T00:00', 1, '2001-02-28T23:59:59.999999999'],
            ['2000-01-01T00:00:00', 999999999, '1999-12-31T23:59:59.000000001']
        ];
    }

    /**
     * @dataProvider providerAtTimeZone
     *
     * @param string  $dateTime     The date-time.
     * @param string  $timeZone     The time-zone.
     * @param integer $epochSeconds  The expected epoch second of the resulting instant.
     * @param integer $nanos The expected nano-of-second of the resulting instant.
     */
    public function testAtTimeZone($dateTime, $timeZone, $epochSeconds, $nanos)
    {
        $zonedDateTime = LocalDateTime::parse($dateTime)->atTimeZone(TimeZone::parse($timeZone));
        $this->assertReadableInstantEquals($epochSeconds, $nanos, $zonedDateTime);
    }

    /**
     * @return array
     */
    public function providerAtTimeZone()
    {
        return [
            ['2001-03-28T23:23:23', '-06:00', 985843403, 0],
            ['1960-04-30T06:00:00.123456', '+02:00', -305236800, 123456000],
            ['2008-01-02T12:34:56', 'Europe/Paris', 1199273696, 0],
            ['2008-01-02T12:34:56.123', 'America/Los_Angeles', 1199306096, 123000000]
        ];
    }

    /**
     * @dataProvider providerIsEqualTo
     *
     * @param string  $dateTime1 The base date-time.
     * @param string  $dateTime2 The date-time to compare to.
     * @param boolean $isEqual   The expected result.
     */
    public function testIsEqualTo($dateTime1, $dateTime2, $isEqual)
    {
        $dateTime1 = LocalDateTime::parse($dateTime1);
        $dateTime2 = LocalDateTime::parse($dateTime2);

        $this->assertSame($isEqual, $dateTime1->isEqualTo($dateTime2));
    }

    /**
     * @return array
     */
    public function providerIsEqualTo()
    {
        return [
            ['2001-01-01T11:11:11.1', '2001-01-01T11:11:11.1', true],
            ['2001-01-01T01:01:01.1', '2009-01-01T01:01:01.1', false],
            ['2001-01-01T01:01:01.1', '2001-09-01T01:01:01.1', false],
            ['2001-01-01T01:01:01.1', '2001-01-09T01:01:01.1', false],
            ['2001-01-01T01:01:01.1', '2001-01-01T09:01:01.1', false],
            ['2001-01-01T01:01:01.1', '2001-01-01T01:09:01.1', false],
            ['2001-01-01T01:01:01.1', '2001-01-01T01:01:09.1', false],
            ['2001-01-01T01:01:01.1', '2001-01-01T01:01:01.9', false],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param string  $dateTime1 The base date-time.
     * @param string  $dateTime2 The date-time to compare to.
     * @param integer $result    The expected result.
     */
    public function testCompareTo($dateTime1, $dateTime2, $result)
    {
        $dateTime1 = LocalDateTime::parse($dateTime1);
        $dateTime2 = LocalDateTime::parse($dateTime2);

        $this->assertSame($result, $dateTime1->compareTo($dateTime2));
    }

    /**
     * @return array
     */
    public function providerCompareTo()
    {
        return [
            ['2000-01-01T11:11:11.1', '2000-01-01T11:11:11.1',  0],
            ['2000-01-01T00:00:00.0', '1999-12-31T23:59:59.9',  1],
            ['1999-12-31T23:59:59.9', '2000-01-01T00:00:00.0', -1],
            ['9999-01-31T23:59:59.9', '0000-12-01T00:00:00.0',  1],
            ['0000-12-01T00:00:00.0', '9999-01-31T23:59:59.9', -1],
            ['9999-12-01T23:59:59.9', '0000-01-31T00:00:00.0',  1],
            ['0000-01-31T00:00:00.0', '9999-12-01T23:59:59.9', -1],
            ['9999-12-31T00:59:59.9', '0000-01-01T23:00:00.0',  1],
            ['0000-01-01T23:00:00.0', '9999-12-31T00:59:59.9', -1],
            ['9999-12-31T23:00:59.9', '0000-01-01T00:59:00.0',  1],
            ['0000-01-01T00:59:00.0', '9999-12-31T23:00:59.9', -1],
            ['9999-12-31T23:59:00.9', '0000-01-01T00:00:59.0',  1],
            ['0000-01-01T00:00:59.0', '9999-12-31T23:59:00.9', -1],
            ['9999-12-31T23:59:59.0', '0000-01-01T00:00:00.9',  1],
            ['0000-01-01T00:00:00.9', '9999-12-31T23:59:59.0', -1],
        ];
    }

    /**
     * @dataProvider providerIsBefore
     *
     * @param string  $dateTime1 The base date-time.
     * @param string  $dateTime2 The date-time to compare to.
     * @param boolean $isBefore  The expected result.
     */
    public function testIsBefore($dateTime1, $dateTime2, $isBefore)
    {
        $dateTime1 = LocalDateTime::parse($dateTime1);
        $dateTime2 = LocalDateTime::parse($dateTime2);

        $this->assertSame($isBefore, $dateTime1->isBefore($dateTime2));
    }

    /**
     * @return array
     */
    public function providerIsBefore()
    {
        $data = $this->providerCompareTo();

        foreach ($data as & $values) {
            $values[2] = ($values[2] == -1);
        }

        return $data;
    }

    /**
     * @dataProvider providerIsAfter
     *
     * @param string  $dateTime1 The base date-time.
     * @param string  $dateTime2 The date-time to compare to.
     * @param boolean $isAfter   The expected result.
     */
    public function testIsAfter($dateTime1, $dateTime2, $isAfter)
    {
        $dateTime1 = LocalDateTime::parse($dateTime1);
        $dateTime2 = LocalDateTime::parse($dateTime2);

        $this->assertSame($isAfter, $dateTime1->isAfter($dateTime2));
    }

    /**
     * @return array
     */
    public function providerIsAfter()
    {
        $data = $this->providerCompareTo();

        foreach ($data as & $values) {
            $values[2] = ($values[2] == 1);
        }

        return $data;
    }
}
