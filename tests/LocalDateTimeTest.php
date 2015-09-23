<?php

namespace Brick\DateTime\Tests;

use Brick\DateTime\Duration;
use Brick\DateTime\Field\Year;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;

/**
 * Unit tests for class LocalDateTime.
 */
class LocalDateTimeTest extends AbstractTestCase
{
    public function testOf()
    {
        $date = LocalDate::of(2001, 12, 23);
        $time = LocalTime::of(12, 34, 56, 987654321);

        $this->assertLocalDateTimeIs(2001, 12, 23, 12, 34, 56, 987654321, new LocalDateTime($date, $time));
    }

    /**
     * @dataProvider providerNow
     *
     * @param integer $second The second to set the clock to.
     * @param integer $nano   The nanosecond adjustment to the clock.
     * @param integer $offset The time-zone offset to get the time at.
     * @param integer $y      The expected year.
     * @param integer $m      The expected month
     * @param integer $d      The expected day.
     * @param integer $h      The expected hour.
     * @param integer $i      The expected minute.
     * @param integer $s      The expected second.
     * @param integer $n      The expected nano.
     */
    public function testNow($second, $nano, $offset, $y, $m, $d, $h, $i, $s, $n)
    {
        $this->setClockTime($second, $nano);
        $timeZone = TimeZoneOffset::ofTotalSeconds($offset);
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, LocalDateTime::now($timeZone));
    }

    /**
     * @return array
     */
    public function providerNow()
    {
        return [
            [1409574896, 0,         0, 2014, 9, 1, 12, 34, 56,      0],
            [1409574896, 123,       0, 2014, 9, 1, 12, 34, 56,    123],
            [1409574896, 0,      3600, 2014, 9, 1, 13, 34, 56,      0],
            [1409574896, 123456, 5400, 2014, 9, 1, 14,  4, 56, 123456]
        ];
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
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, LocalDateTime::parse($t));
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

    public function testMin()
    {
        $this->assertLocalDateTimeIs(Year::MIN_VALUE, 1, 1, 0, 0, 0, 0, LocalDateTime::min());
    }

    public function testMax()
    {
        $this->assertLocalDateTimeIs(Year::MAX_VALUE, 12, 31, 23, 59, 59, 999999999, LocalDateTime::max());
    }

    public function testMinMaxOf()
    {
        $a = LocalDateTime::parse('2003-12-31T12:30:00');
        $b = LocalDateTime::parse('2005-12-31T23:59:59.999999999');
        $c = LocalDateTime::parse('2006-07-12T05:22:11');

        $this->assertSame($a, LocalDateTime::minOf($a, $b, $c));
        $this->assertSame($c, LocalDateTime::maxOf($a, $b, $c));
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testMinOfZeroElementsThrowsException()
    {
        LocalDateTime::minOf();
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testMaxOfZeroElementsThrowsException()
    {
        LocalDateTime::maxOf();
    }

    /**
     * @dataProvider providerGetDayOfWeek
     *
     * @param integer $year      The year to test.
     * @param integer $month     The month to test.
     * @param integer $day       The day-of-month to test.
     * @param integer $dayOfWeek The expected day-of-week number.
     */
    public function testGetDayOfWeek($year, $month, $day, $dayOfWeek)
    {
        $dateTime = LocalDateTime::of($year, $month, $day, 15, 30, 45);
        $this->assertDayOfWeekIs($dayOfWeek, $dateTime->getDayOfWeek());
    }

    /**
     * @return array
     */
    public function providerGetDayOfWeek()
    {
        return [
            [2000, 1, 3, 1],
            [2000, 2, 8, 2],
            [2000, 3, 8, 3],
            [2000, 4, 6, 4],
            [2000, 5, 5, 5],
            [2000, 6, 3, 6],
            [2000, 7, 9, 7],
            [2001, 1, 1, 1],
            [2001, 2, 6, 2],
            [2001, 3, 7, 3],
            [2001, 4, 5, 4],
            [2001, 5, 4, 5],
            [2001, 6, 9, 6],
            [2001, 7, 8, 7]
        ];
    }

    /**
     * @dataProvider providerGetDayOfYear
     *
     * @param integer $year      The year to test.
     * @param integer $month     The month to test.
     * @param integer $day       The day-of-month to test.
     * @param integer $dayOfYear The expected day-of-year number.
     */
    public function testGetDayOfYear($year, $month, $day, $dayOfYear)
    {
        $dateTime = LocalDate::of($year, $month, $day)->atTime(LocalTime::midnight());
        $this->assertSame($dayOfYear, $dateTime->getDayOfYear());
    }

    /**
     * @return array
     */
    public function providerGetDayOfYear()
    {
        return [
            [2000, 1, 1, 1],
            [2000, 3, 1, 61],
            [2000, 12, 31, 366],
            [2001, 1, 1, 1],
            [2001, 12, 31, 365]
        ];
    }

    /**
     * @dataProvider providerWithDate
     *
     * @param integer $y  The new year.
     * @param integer $m  The new month.
     * @param integer $d  The new day.
     */
    public function testWithDate($y, $m, $d)
    {
        $dateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 789));
        $newDate = LocalDate::of($y, $m, $d);
        $this->assertLocalDateTimeIs($y, $m, $d, 4, 5, 6, 789, $dateTime->withDate($newDate));
    }

    /**
     * @return array
     */
    public function providerWithDate()
    {
        return [
            [2001, 2, 3],
            [2002, 3, 1]
        ];
    }

    /**
     * @dataProvider providerWithTime
     *
     * @param integer $h The new hour.
     * @param integer $m The new minute.
     * @param integer $s The new second.
     * @param integer $n The new nano.
     */
    public function testWithTime($h, $m, $s, $n)
    {
        $dateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 789));
        $newTime = LocalTime::of($h, $m, $s, $n);
        $this->assertLocalDateTimeIs(2001, 2, 3, $h, $m, $s, $n, $dateTime->withTime($newTime));
    }

    /**
     * @return array
     */
    public function providerWithTime()
    {
        return [
            [4, 5, 6, 789],
            [5, 6, 4, 987]
        ];
    }

    /**
     * @dataProvider providerWithYear
     *
     * @param integer $year        The base year.
     * @param integer $month       The base month.
     * @param integer $day         The base day-of-month.
     * @param integer $newYear     The new year.
     * @param integer $expectedDay The expected day-of-month of the resulting date.
     */
    public function testWithYear($year, $month, $day, $newYear, $expectedDay)
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of(1, 2, 3, 123456789);
        $localDateTime = $date->atTime($time)->withYear($newYear);
        $this->assertLocalDateTimeIs($newYear, $month, $expectedDay, 1, 2, 3, 123456789, $localDateTime);
    }

    /**
     * @return array
     */
    public function providerWithYear()
    {
        return [
            [2007, 3, 31, 2008, 31],
            [2007, 2, 28, 2008, 28],
            [2008, 2, 28, 2009, 28],
            [2008, 2, 29, 2008, 29],
            [2008, 2, 29, 2009, 28],
            [2008, 2, 29, 2012, 29]
        ];
    }

    /**
     * @dataProvider providerWithInvalidYearThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidYear The year to test.
     */
    public function testWithInvalidYearThrowsException($invalidYear)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withYear($invalidYear);
    }

    /**
     * @return array
     */
    public function providerWithInvalidYearThrowsException()
    {
        return [
            [-1000000],
            [1000000]
        ];
    }

    /**
     * @dataProvider providerWithMonth
     *
     * @param integer $year        The base year.
     * @param integer $month       The base month.
     * @param integer $day         The base day-of-month.
     * @param integer $newMonth    The new month.
     * @param integer $expectedDay The expected day-of-month of the resulting date.
     */
    public function testWithMonth($year, $month, $day, $newMonth, $expectedDay)
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of(1, 2, 3, 123456789);
        $localDateTime = $date->atTime($time)->withMonth($newMonth);
        $this->assertLocalDateTimeIs($year, $newMonth, $expectedDay, 1, 2, 3, 123456789, $localDateTime);
    }

    /**
     * @return array
     */
    public function providerWithMonth()
    {
        return [
            [2007, 3, 31, 2, 28],
            [2008, 3, 31, 2, 29],
            [2007, 3, 31, 1, 31],
            [2008, 3, 31, 3, 31],
            [2007, 3, 31, 4, 30],
            [2008, 3, 31, 5, 31],
            [2007, 3, 31, 6, 30],
            [2008, 3, 31, 7, 31],
            [2007, 3, 31, 8, 31],
            [2008, 3, 31, 9, 30],
            [2007, 3, 31, 10, 31],
            [2008, 3, 31, 11, 30],
            [2007, 3, 31, 12, 31],
            [2008, 4, 30, 12, 30]
        ];
    }

    /**
     * @dataProvider providerWithInvalidYearThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidMonth The month to test.
     */
    public function testWithInvalidMonthThrowsException($invalidMonth)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withMonth($invalidMonth);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMonthThrowsException()
    {
        return [
            [0],
            [13]
        ];
    }

    /**
     * @dataProvider providerWithDay
     *
     * @param integer $year   The base year.
     * @param integer $month  The base month.
     * @param integer $day    The base day-of-month.
     * @param integer $newDay The new day-of-month.
     */
    public function testWithDay($year, $month, $day, $newDay)
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of(1, 2, 3, 123456789);
        $localDateTime = $date->atTime($time)->withDay($newDay);
        $this->assertLocalDateTimeIs($year, $month, $newDay, 1, 2, 3, 123456789, $localDateTime);
    }

    /**
     * @return array
     */
    public function providerWithDay()
    {
        return [
            [2007, 6, 2, 2],
            [2007, 1, 1, 31],
            [2008, 2, 28, 29],
            [2010, 2, 27, 28]
        ];
    }

    /**
     * @dataProvider providerWithInvalidDayThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $year   The base year.
     * @param integer $month  The base month.
     * @param integer $day    The base day-of-month.
     * @param integer $newDay The new day-of-month.
     */
    public function testWithInvalidDayThrowsException($year, $month, $day, $newDay)
    {
        LocalDate::of($year, $month, $day)->atTime(LocalTime::midnight())->withDay($newDay);
    }

    /**
     * @return array
     */
    public function providerWithInvalidDayThrowsException()
    {
        return [
            [2007, 1, 1, 0],
            [2007, 1, 1, 32],
            [2007, 2, 1, 29],
            [2008, 2, 1, 30],
            [2009, 4, 1, 31]
        ];
    }

    /**
     * @dataProvider providerWithHour
     *
     * @param integer $hour The new hour.
     */
    public function testWithHour($hour)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, $hour, 34, 56, 123456789, $localDateTime->withHour($hour));
    }

    /**
     * @return array
     */
    public function providerWithHour()
    {
        return [
            [12],
            [23]
        ];
    }

    /**
     * @dataProvider providerWithInvalidHourThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidHour
     */
    public function testWithInvalidHourThrowsException($invalidHour)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withHour($invalidHour);
    }

    /**
     * @return array
     */
    public function providerWithInvalidHourThrowsException()
    {
        return [
            [-1],
            [24]
        ];
    }

    /**
     * @dataProvider providerWithMinute
     *
     * @param integer $minute The new minute.
     */
    public function testWithMinute($minute)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, 12, $minute, 56, 123456789, $localDateTime->withMinute($minute));
    }

    /**
     * @return array
     */
    public function providerWithMinute()
    {
        return [
            [34],
            [45]
        ];
    }

    /**
     * @dataProvider providerWithInvalidMinuteThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidMinute
     */
    public function testWithInvalidMinuteThrowsException($invalidMinute)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withMinute($invalidMinute);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMinuteThrowsException()
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithSecond
     *
     * @param integer $second The new second.
     */
    public function testWithSecond($second)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, 12, 34, $second, 123456789, $localDateTime->withSecond($second));
    }

    /**
     * @return array
     */
    public function providerWithSecond()
    {
        return [
            [56],
            [45]
        ];
    }

    /**
     * @dataProvider providerWithInvalidSecondThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidSecond
     */
    public function testWithInvalidSecondThrowsException($invalidSecond)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withSecond($invalidSecond);
    }

    /**
     * @return array
     */
    public function providerWithInvalidSecondThrowsException()
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithNano
     *
     * @param integer $nano The new nano.
     */
    public function testWithNano($nano)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, 12, 34, 56, $nano, $localDateTime->withNano($nano));
    }

    /**
     * @return array
     */
    public function providerWithNano()
    {
        return [
            [789],
            [123456]
        ];
    }

    /**
     * @dataProvider providerWithInvalidNanoThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidNano
     */
    public function testWithInvalidNanoThrowsException($invalidNano)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withNano($invalidNano);
    }

    /**
     * @return array
     */
    public function providerWithInvalidNanoThrowsException()
    {
        return [
            [-1],
            [1000000000]
        ];
    }

    /**
     * @dataProvider providerPeriod
     *
     * @param integer $y  The year of the base date.
     * @param integer $m  The month of the base date.
     * @param integer $d  The day of the base date.
     * @param integer $py The number of years in the period.
     * @param integer $pm The number of months in the period.
     * @param integer $pd The number of days in the period.
     * @param integer $ey The expected year of the result date.
     * @param integer $em The expected month of the result date.
     * @param integer $ed The expected day of the result date.
     */
    public function testPlusPeriod($y, $m, $d, $py, $pm, $pd, $ey, $em, $ed)
    {
        $dateTime = LocalDate::of($y, $m, $d)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $period = Period::of($py, $pm, $pd);

        $this->assertLocalDateTimeIs($ey, $em, $ed, 12, 34, 56, 123456789, $dateTime->plusPeriod($period));
    }

    /**
     * @dataProvider providerPeriod
     *
     * @param integer $y  The year of the base date.
     * @param integer $m  The month of the base date.
     * @param integer $d  The day of the base date.
     * @param integer $py The number of years in the period.
     * @param integer $pm The number of months in the period.
     * @param integer $pd The number of days in the period.
     * @param integer $ey The expected year of the result date.
     * @param integer $em The expected month of the result date.
     * @param integer $ed The expected day of the result date.
     */
    public function testMinusPeriod($y, $m, $d, $py, $pm, $pd, $ey, $em, $ed)
    {
        $dateTime = LocalDate::of($y, $m, $d)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $period = Period::of($py, $pm, $pd);

        $this->assertLocalDateTimeIs($ey, $em, $ed, 12, 34, 56, 123456789, $dateTime->minusPeriod($period->negated()));
    }

    /**
     * @return array
     */
    public function providerPeriod()
    {
        return [
            [2001, 2, 3,  0,   0,   0, 2001,  2,  3],
            [2001, 2, 3,  0,   0,   1, 2001,  2,  4],
            [2001, 2, 3,  0,   0,  -1, 2001,  2,  2],
            [2001, 2, 3,  0,   1,   0, 2001,  3,  3],
            [2001, 2, 3,  0,  -1,   0, 2001,  1,  3],
            [2001, 2, 3,  1,   0,   0, 2002,  2,  3],
            [2001, 2, 3, -1,   0,   0, 2000,  2,  3],
            [2001, 2, 3,  0,   0,  30, 2001,  3,  5],
            [2001, 2, 3,  0,  30,  50, 2003,  9, 22],
            [2001, 2, 3,  0,   0, -30, 2001,  1,  4],
            [2001, 2, 3,  0, -30, -50, 1998,  6, 14]
        ];
    }

    /**
     * @dataProvider providerDuration
     *
     * @param integer $ds The seconds of the duration.
     * @param integer $dn The nano adjustment of the duration.
     * @param integer $y  The expected year.
     * @param integer $m  The expected month.
     * @param integer $d  The expected day.
     * @param integer $h  The exepected hour.
     * @param integer $i  The expected minute.
     * @param integer $s  The expected second.
     * @param integer $n  The expected nano.
     */
    public function testPlusDuration($ds, $dn, $y, $m, $d, $h, $i, $s, $n)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 123456789));
        $duration = Duration::ofSeconds($ds, $dn);
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, $localDateTime->plusDuration($duration));
    }

    /**
     * @dataProvider providerDuration
     *
     * @param integer $ds The seconds of the duration.
     * @param integer $dn The nano adjustment of the duration.
     * @param integer $y  The expected year.
     * @param integer $m  The expected month.
     * @param integer $d  The expected day.
     * @param integer $h  The exepected hour.
     * @param integer $i  The expected minute.
     * @param integer $s  The expected second.
     * @param integer $n  The expected nano.
     */
    public function testMinusDuration($ds, $dn, $y, $m, $d, $h, $i, $s, $n)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 123456789));
        $duration = Duration::ofSeconds(-$ds, -$dn);
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, $localDateTime->minusDuration($duration));
    }

    /**
     * @return array
     */
    public function providerDuration()
    {
        return [
            [123456, 2000000000, 2001, 2, 4, 14, 22, 44, 123456789],
            [7654321, 1999999999, 2001, 5, 2, 18, 17, 9, 123456788],
            [-654321, -987654321, 2001, 1, 26, 14, 19, 44, 135802468],
            [-7654321, 2013456789, 2000, 11, 6, 13, 53, 7, 136913578]
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
     * @dataProvider providerPlusWeeks
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $weeks            The number of weeks to add.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusWeeks($dateTime, $weeks, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusWeeks($weeks);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusWeeks()
    {
        return [
            ['1999-11-30T12:34', 0, '1999-11-30T12:34'],
            ['1999-11-30T12:34', 714, '2013-08-06T12:34'],
            ['2000-11-30T12:34:56.123456789', -71, '1999-07-22T12:34:56.123456789']
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
     * @dataProvider providerMinusWeeks
     *
     * @param string  $dateTime         The base date-time string.
     * @param integer $weeks            The number of weeks to subtract.
     * @param string  $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusWeeks($dateTime, $weeks, $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusWeeks($weeks);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusWeeks()
    {
        return [
            ['1999-11-30T12:34', 0, '1999-11-30T12:34'],
            ['2000-11-30T12:34:56.123456789', 17636, '1662-11-30T12:34:56.123456789'],
            ['1999-11-30T12:34', -93474, '3791-05-17T12:34']
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
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
        $this->assertReadableInstantIs($epochSeconds, $nanos, $zonedDateTime);
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
