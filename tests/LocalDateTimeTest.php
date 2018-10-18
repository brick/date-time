<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Field\Year;
use Brick\DateTime\Instant;
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

    public function testFromDateTime()
    {
        $dateTime = new \DateTime('2018-07-21 14:09:10.23456');
        $this->assertLocalDateTimeIs(2018, 7, 21, 14, 9, 10, 234560000, LocalDateTime::fromDateTime($dateTime));
    }

    /**
     * @dataProvider providerNow
     *
     * @param int $second The second to set the clock to.
     * @param int $nano   The nanosecond adjustment to the clock.
     * @param int $offset The time-zone offset to get the time at.
     * @param int $y      The expected year.
     * @param int $m      The expected month
     * @param int $d      The expected day.
     * @param int $h      The expected hour.
     * @param int $i      The expected minute.
     * @param int $s      The expected second.
     * @param int $n      The expected nano.
     */
    public function testNow(int $second, int $nano, int $offset, int $y, int $m, int $d, int $h, int $i, int $s, int $n)
    {
        $clock = new FixedClock(Instant::of($second, $nano));
        $timeZone = TimeZoneOffset::ofTotalSeconds($offset);
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, LocalDateTime::now($timeZone, $clock));
    }

    /**
     * @return array
     */
    public function providerNow() : array
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
     * @param string $t The text to parse.
     * @param int    $y The expected year.
     * @param int    $m The expected month.
     * @param int    $d The expected day.
     * @param int    $h The expected hour.
     * @param int    $i The expected minute.
     * @param int    $s The expected second.
     * @param int    $n The expected nano-of-second.
     */
    public function testParse(string $t, int $y, int $m, int $d, int $h, int $i, int $s, int $n)
    {
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, LocalDateTime::parse($t));
    }

    /**
     * @return array
     */
    public function providerParse() : array
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
    public function testParseInvalidStringThrowsException(string $text)
    {
        LocalDateTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException() : array
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
    public function testParseInvalidDateTimeThrowsException(string $text)
    {
        LocalDateTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidDateTimeThrowsException() : array
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
     * @param int $year      The year to test.
     * @param int $month     The month to test.
     * @param int $day       The day-of-month to test.
     * @param int $dayOfWeek The expected day-of-week number.
     */
    public function testGetDayOfWeek(int $year, int $month, int $day, int $dayOfWeek)
    {
        $dateTime = LocalDateTime::of($year, $month, $day, 15, 30, 45);
        $this->assertDayOfWeekIs($dayOfWeek, $dateTime->getDayOfWeek());
    }

    /**
     * @return array
     */
    public function providerGetDayOfWeek() : array
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
     * @param int $year      The year to test.
     * @param int $month     The month to test.
     * @param int $day       The day-of-month to test.
     * @param int $dayOfYear The expected day-of-year number.
     */
    public function testGetDayOfYear(int $year, int $month, int $day, int $dayOfYear)
    {
        $dateTime = LocalDate::of($year, $month, $day)->atTime(LocalTime::midnight());
        $this->assertSame($dayOfYear, $dateTime->getDayOfYear());
    }

    /**
     * @return array
     */
    public function providerGetDayOfYear() : array
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
     * @param int $y  The new year.
     * @param int $m  The new month.
     * @param int $d  The new day.
     */
    public function testWithDate(int $y, int $m, int $d)
    {
        $dateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 789));
        $newDate = LocalDate::of($y, $m, $d);
        $this->assertLocalDateTimeIs($y, $m, $d, 4, 5, 6, 789, $dateTime->withDate($newDate));
    }

    /**
     * @return array
     */
    public function providerWithDate() : array
    {
        return [
            [2001, 2, 3],
            [2002, 3, 1]
        ];
    }

    /**
     * @dataProvider providerWithTime
     *
     * @param int $h The new hour.
     * @param int $m The new minute.
     * @param int $s The new second.
     * @param int $n The new nano.
     */
    public function testWithTime(int $h, int $m, int $s, int $n)
    {
        $dateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 789));
        $newTime = LocalTime::of($h, $m, $s, $n);
        $this->assertLocalDateTimeIs(2001, 2, 3, $h, $m, $s, $n, $dateTime->withTime($newTime));
    }

    /**
     * @return array
     */
    public function providerWithTime() : array
    {
        return [
            [4, 5, 6, 789],
            [5, 6, 4, 987]
        ];
    }

    /**
     * @dataProvider providerWithYear
     *
     * @param int $year        The base year.
     * @param int $month       The base month.
     * @param int $day         The base day-of-month.
     * @param int $newYear     The new year.
     * @param int $expectedDay The expected day-of-month of the resulting date.
     */
    public function testWithYear(int $year, int $month, int $day, int $newYear, int $expectedDay)
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of(1, 2, 3, 123456789);
        $localDateTime = $date->atTime($time)->withYear($newYear);
        $this->assertLocalDateTimeIs($newYear, $month, $expectedDay, 1, 2, 3, 123456789, $localDateTime);
    }

    /**
     * @return array
     */
    public function providerWithYear() : array
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
     * @param int $invalidYear The year to test.
     */
    public function testWithInvalidYearThrowsException(int $invalidYear)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withYear($invalidYear);
    }

    /**
     * @return array
     */
    public function providerWithInvalidYearThrowsException() : array
    {
        return [
            [-1000000],
            [1000000]
        ];
    }

    /**
     * @dataProvider providerWithMonth
     *
     * @param int $year        The base year.
     * @param int $month       The base month.
     * @param int $day         The base day-of-month.
     * @param int $newMonth    The new month.
     * @param int $expectedDay The expected day-of-month of the resulting date.
     */
    public function testWithMonth(int $year, int $month, int $day, int $newMonth, int $expectedDay)
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of(1, 2, 3, 123456789);
        $localDateTime = $date->atTime($time)->withMonth($newMonth);
        $this->assertLocalDateTimeIs($year, $newMonth, $expectedDay, 1, 2, 3, 123456789, $localDateTime);
    }

    /**
     * @return array
     */
    public function providerWithMonth() : array
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
     * @param int $invalidMonth The month to test.
     */
    public function testWithInvalidMonthThrowsException(int $invalidMonth)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withMonth($invalidMonth);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMonthThrowsException() : array
    {
        return [
            [0],
            [13]
        ];
    }

    /**
     * @dataProvider providerWithDay
     *
     * @param int $year   The base year.
     * @param int $month  The base month.
     * @param int $day    The base day-of-month.
     * @param int $newDay The new day-of-month.
     */
    public function testWithDay(int $year, int $month, int $day, int $newDay)
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of(1, 2, 3, 123456789);
        $localDateTime = $date->atTime($time)->withDay($newDay);
        $this->assertLocalDateTimeIs($year, $month, $newDay, 1, 2, 3, 123456789, $localDateTime);
    }

    /**
     * @return array
     */
    public function providerWithDay() : array
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
     * @param int $year   The base year.
     * @param int $month  The base month.
     * @param int $day    The base day-of-month.
     * @param int $newDay The new day-of-month.
     */
    public function testWithInvalidDayThrowsException(int $year, int $month, int $day, int $newDay)
    {
        LocalDate::of($year, $month, $day)->atTime(LocalTime::midnight())->withDay($newDay);
    }

    /**
     * @return array
     */
    public function providerWithInvalidDayThrowsException() : array
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
     * @param int $hour The new hour.
     */
    public function testWithHour(int $hour)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, $hour, 34, 56, 123456789, $localDateTime->withHour($hour));
    }

    /**
     * @return array
     */
    public function providerWithHour() : array
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
     * @param int $invalidHour
     */
    public function testWithInvalidHourThrowsException(int $invalidHour)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withHour($invalidHour);
    }

    /**
     * @return array
     */
    public function providerWithInvalidHourThrowsException() : array
    {
        return [
            [-1],
            [24]
        ];
    }

    /**
     * @dataProvider providerWithMinute
     *
     * @param int $minute The new minute.
     */
    public function testWithMinute(int $minute)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, 12, $minute, 56, 123456789, $localDateTime->withMinute($minute));
    }

    /**
     * @return array
     */
    public function providerWithMinute() : array
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
     * @param int $invalidMinute
     */
    public function testWithInvalidMinuteThrowsException(int $invalidMinute)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withMinute($invalidMinute);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMinuteThrowsException() : array
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithSecond
     *
     * @param int $second The new second.
     */
    public function testWithSecond(int $second)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, 12, 34, $second, 123456789, $localDateTime->withSecond($second));
    }

    /**
     * @return array
     */
    public function providerWithSecond() : array
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
     * @param int $invalidSecond
     */
    public function testWithInvalidSecondThrowsException(int $invalidSecond)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withSecond($invalidSecond);
    }

    /**
     * @return array
     */
    public function providerWithInvalidSecondThrowsException() : array
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithNano
     *
     * @param int $nano The new nano.
     */
    public function testWithNano(int $nano)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $this->assertLocalDateTimeIs(2001, 2, 3, 12, 34, 56, $nano, $localDateTime->withNano($nano));
    }

    /**
     * @return array
     */
    public function providerWithNano() : array
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
     * @param int $invalidNano
     */
    public function testWithInvalidNanoThrowsException(int $invalidNano)
    {
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withNano($invalidNano);
    }

    /**
     * @return array
     */
    public function providerWithInvalidNanoThrowsException() : array
    {
        return [
            [-1],
            [1000000000]
        ];
    }

    /**
     * @dataProvider providerPeriod
     *
     * @param int $y  The year of the base date.
     * @param int $m  The month of the base date.
     * @param int $d  The day of the base date.
     * @param int $py The number of years in the period.
     * @param int $pm The number of months in the period.
     * @param int $pd The number of days in the period.
     * @param int $ey The expected year of the result date.
     * @param int $em The expected month of the result date.
     * @param int $ed The expected day of the result date.
     */
    public function testPlusPeriod(int $y, int $m, int $d, int $py, int $pm, int $pd, int $ey, int $em, int $ed)
    {
        $dateTime = LocalDate::of($y, $m, $d)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $period = Period::of($py, $pm, $pd);

        $this->assertLocalDateTimeIs($ey, $em, $ed, 12, 34, 56, 123456789, $dateTime->plusPeriod($period));
    }

    /**
     * @dataProvider providerPeriod
     *
     * @param int $y  The year of the base date.
     * @param int $m  The month of the base date.
     * @param int $d  The day of the base date.
     * @param int $py The number of years in the period.
     * @param int $pm The number of months in the period.
     * @param int $pd The number of days in the period.
     * @param int $ey The expected year of the result date.
     * @param int $em The expected month of the result date.
     * @param int $ed The expected day of the result date.
     */
    public function testMinusPeriod(int $y, int $m, int $d, int $py, int $pm, int $pd, int $ey, int $em, int $ed)
    {
        $dateTime = LocalDate::of($y, $m, $d)->atTime(LocalTime::of(12, 34, 56, 123456789));
        $period = Period::of($py, $pm, $pd);

        $this->assertLocalDateTimeIs($ey, $em, $ed, 12, 34, 56, 123456789, $dateTime->minusPeriod($period->negated()));
    }

    /**
     * @return array
     */
    public function providerPeriod() : array
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
     * @param int $ds The seconds of the duration.
     * @param int $dn The nano adjustment of the duration.
     * @param int $y  The expected year.
     * @param int $m  The expected month.
     * @param int $d  The expected day.
     * @param int $h  The exepected hour.
     * @param int $i  The expected minute.
     * @param int $s  The expected second.
     * @param int $n  The expected nano.
     */
    public function testPlusDuration(int $ds, int $dn, int $y, int $m, int $d, int $h, int $i, int $s, int $n)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 123456789));
        $duration = Duration::ofSeconds($ds, $dn);
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, $localDateTime->plusDuration($duration));
    }

    /**
     * @dataProvider providerDuration
     *
     * @param int $ds The seconds of the duration.
     * @param int $dn The nano adjustment of the duration.
     * @param int $y  The expected year.
     * @param int $m  The expected month.
     * @param int $d  The expected day.
     * @param int $h  The exepected hour.
     * @param int $i  The expected minute.
     * @param int $s  The expected second.
     * @param int $n  The expected nano.
     */
    public function testMinusDuration(int $ds, int $dn, int $y, int $m, int $d, int $h, int $i, int $s, int $n)
    {
        $localDateTime = LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6, 123456789));
        $duration = Duration::ofSeconds(-$ds, -$dn);
        $this->assertLocalDateTimeIs($y, $m, $d, $h, $i, $s, $n, $localDateTime->minusDuration($duration));
    }

    /**
     * @return array
     */
    public function providerDuration() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $years            The number of years to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusYears(string $dateTime, int $years, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusYears($years);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusYears() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $months           The number of months to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusMonths(string $dateTime, int $months, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusMonths($months);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusMonths() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $weeks            The number of weeks to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusWeeks(string $dateTime, int $weeks, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusWeeks($weeks);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusWeeks() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $days             The number of days to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusDays(string $dateTime, int $days, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusDays($days);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusDays() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $hours            The number of hours to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusHours(string $dateTime, int $hours, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusHours($hours);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusHours() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $minutes          The number of minutes to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusMinutes(string $dateTime, int $minutes, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusMinutes($minutes);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $seconds          The number of seconds to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusSeconds(string $dateTime, int $seconds, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusSeconds($seconds);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $nanosToAdd       The nanoseconds to add.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testPlusNanos(string $dateTime, int $nanosToAdd, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->plusNanos($nanosToAdd);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerPlusNanos() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $years            The number of years to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusYears(string $dateTime, int $years, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusYears($years);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusYears() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $months           The number of months to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusMonths(string $dateTime, int $months, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusMonths($months);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusMonths() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $weeks            The number of weeks to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusWeeks(string $dateTime, int $weeks, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusWeeks($weeks);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusWeeks() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $days             The number of days to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusDays(string $dateTime, int $days, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusDays($days);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusDays() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $hours            The number of hours to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusHours(string $dateTime, int $hours, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusHours($hours);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusHours() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $minutes          The number of minutes to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusMinutes(string $dateTime, int $minutes, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusMinutes($minutes);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusMinutes() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $seconds          The number of seconds to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusSeconds(string $dateTime, int $seconds, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusSeconds($seconds);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusSeconds() : array
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
     * @param string $dateTime         The base date-time string.
     * @param int    $nanosToSubtract  The nanoseconds to subtract.
     * @param string $expectedDateTime The expected resulting date-time string.
     */
    public function testMinusNanos(string $dateTime, int $nanosToSubtract, string $expectedDateTime)
    {
        $actualDateTime = LocalDateTime::parse($dateTime)->minusNanos($nanosToSubtract);
        $this->assertSame($expectedDateTime, (string) $actualDateTime);
    }

    /**
     * @return array
     */
    public function providerMinusNanos() : array
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
     * @param string $dateTime     The date-time.
     * @param string $timeZone     The time-zone.
     * @param int    $epochSeconds  The expected epoch second of the resulting instant.
     * @param int    $nanos The expected nano-of-second of the resulting instant.
     */
    public function testAtTimeZone(string $dateTime, string $timeZone, int $epochSeconds, int $nanos)
    {
        $zonedDateTime = LocalDateTime::parse($dateTime)->atTimeZone(TimeZone::parse($timeZone));
        $this->assertInstantIs($epochSeconds, $nanos, $zonedDateTime->getInstant());
    }

    /**
     * @return array
     */
    public function providerAtTimeZone() : array
    {
        return [
            ['2001-03-28T23:23:23', '-06:00', 985843403, 0],
            ['1960-04-30T06:00:00.123456', '+02:00', -305236800, 123456000],
            ['2008-01-02T12:34:56', 'Europe/Paris', 1199273696, 0],
            ['2008-01-02T12:34:56.123', 'America/Los_Angeles', 1199306096, 123000000]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param string $dateTime1 The base date-time.
     * @param string $dateTime2 The date-time to compare to.
     * @param int    $result    The expected result.
     */
    public function testCompareTo(string $dateTime1, string $dateTime2, int $result)
    {
        $dateTime1 = LocalDateTime::parse($dateTime1);
        $dateTime2 = LocalDateTime::parse($dateTime2);

        $this->assertSame($result, $dateTime1->compareTo($dateTime2));

        $this->assertSame($result === 0, $dateTime1->isEqualTo($dateTime2));
        $this->assertSame($result === 1, $dateTime1->isAfter($dateTime2));
        $this->assertSame($result === -1, $dateTime1->isBefore($dateTime2));
        $this->assertSame($result >= 0, $dateTime1->isAfterOrEqualTo($dateTime2));
        $this->assertSame($result <= 0, $dateTime1->isBeforeOrEqualTo($dateTime2));
    }

    /**
     * @return array
     */
    public function providerCompareTo() : array
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
     * @dataProvider providerToDateTime
     *
     * @param string $dateTime The date-time string that will be parse()d by LocalDateTime.
     * @param string $expected The expected output from the native DateTime object.
     */
    public function testToDateTime(string $dateTime, string $expected)
    {
        $zonedDateTime = LocalDateTime::parse($dateTime);
        $dateTime = $zonedDateTime->toDateTime();

        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    /**
     * @return array
     */
    public function providerToDateTime()
    {
        return [
            ['2018-10-18T12:34',              '2018-10-18T12:34:00.000000+0000'],
            ['2018-10-18T12:34:56',           '2018-10-18T12:34:56.000000+0000'],
            ['2018-10-18T12:34:00.001',       '2018-10-18T12:34:00.001000+0000'],
            ['2018-10-18T12:34:56.123002',    '2018-10-18T12:34:56.123002+0000'],
            ['2011-07-31T23:59:59',           '2011-07-31T23:59:59.000000+0000'],
            ['2011-07-31T23:59:59.02',        '2011-07-31T23:59:59.020000+0000'],
            ['2011-07-31T23:59:59.000123456', '2011-07-31T23:59:59.000123+0000'],
        ];
    }
}
