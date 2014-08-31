<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\Year;

/**
 * Unit tests for class LocalDate.
 */
class LocalDateTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertLocalDateEquals(2007, 7, 15, LocalDate::of(2007, 7, 15));
    }

    /**
     * @dataProvider providerOfInvalidDateThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $year  The year of the invalid date.
     * @param integer $month The month of the invalid date.
     * @param integer $day   The day of the invalid date.
     */
    public function testOfInvalidDateThrowsException($year, $month, $day)
    {
        LocalDate::of($year, $month, $day);
    }

    /**
     * @return array
     */
    public function providerOfInvalidDateThrowsException()
    {
        return [
            [2007, 2, 29],
            [2007, 4, 31],
            [2007, 1, 0],
            [2007, 1, 32],
            [2007, 0, 1],
            [2007, 13, 1],
            [~PHP_INT_MAX, 1, 1],
            [PHP_INT_MAX, 1, 1]
        ];
    }

    /**
     * @dataProvider providerOfInvalidYearDayThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $year      The year.
     * @param integer $dayOfYear The day-of-year.
     */
    public function testOfInvalidYearDayThrowsException($year, $dayOfYear)
    {
        LocalDate::ofYearDay($year, $dayOfYear);
    }

    /**
     * @return array
     */
    public function providerOfInvalidYearDayThrowsException()
    {
        return [
            [2007, 366],
            [2007, 0],
            [2007, 367],
            [~ PHP_INT_MAX, 1],
            [PHP_INT_MAX, 1],
        ];
    }

    /**
     * @dataProvider providerEpochDay
     *
     * @param integer $epochDay The epoch day.
     * @param integer $year     The expected year.
     * @param integer $month    The expected month.
     * @param integer $day      The expected day.
     */
    public function testOfEpochDay($epochDay, $year, $month, $day)
    {
        $this->assertLocalDateEquals($year, $month, $day, LocalDate::ofEpochDay($epochDay));
    }

    /**
     * @dataProvider providerEpochDay
     *
     * @param integer $epochDay The expected epoch day.
     * @param integer $year     The year.
     * @param integer $month    The month.
     * @param integer $day      The day.
     */
    public function testToEpochDay($epochDay, $year, $month, $day)
    {
        $this->assertSame($epochDay, LocalDate::of($year, $month, $day)->toEpochDay());
    }

    /**
     * @return array
     */
    public function providerEpochDay()
    {
        return [
            [-1000000, -768,  2,  4],
            [ -100000, 1696,  3, 17],
            [  -10000, 1942,  8, 16],
            [   -1000, 1967,  4,  7],
            [    -100, 1969,  9, 23],
            [     -10, 1969, 12, 22],
            [      -1, 1969, 12, 31],
            [       0, 1970,  1,  1],
            [       1, 1970,  1,  2],
            [      10, 1970,  1, 11],
            [     100, 1970,  4, 11],
            [    1000, 1972,  9, 27],
            [   10000, 1997,  5, 19],
            [  100000, 2243, 10, 17],
            [ 1000000, 4707, 11, 29]
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param integer $epochSecond The epoch second to set the clock to.
     * @param string  $timeZone    The time-zone to get the date in.
     * @param integer $year        The expected year.
     * @param integer $month       The expected month.
     * @param integer $day         The expected day.
     */
    public function testNow($epochSecond, $timeZone, $year, $month, $day)
    {
        Clock::setDefault(new FixedClock(Instant::of($epochSecond)));
        $this->assertLocalDateEquals($year, $month, $day, LocalDate::now(TimeZone::parse($timeZone)));
    }

    /**
     * @return array
     */
    public function providerNow()
    {
        return [
            [0, '-01:00', 1969, 12, 31],
            [0, '+00:00', 1970, 1, 1],
            [1407970800, '+01:00', 2014, 8, 14],
            [1407970800, '-01:00', 2014, 8, 13]
        ];
    }

    public function testMin()
    {
        $this->assertLocalDateEquals(Year::MIN_VALUE, 1, 1, LocalDate::min());
    }

    public function testMax()
    {
        $this->assertLocalDateEquals(Year::MAX_VALUE, 12, 31, LocalDate::max());
    }

    /**
     * @dataProvider providerDayOfWeek
     *
     * @param integer $year      The year to test.
     * @param integer $month     The month to test.
     * @param integer $day       The day-of-month to test.
     * @param integer $dayOfWeek The expected day-of-week number.
     */
    public function testGetDayOfWeek($year, $month, $day, $dayOfWeek)
    {
        $this->assertSame($dayOfWeek, LocalDate::of($year, $month, $day)->getDayOfWeek()->getValue());
    }

    /**
     * @return array
     */
    public function providerDayOfWeek()
    {
        return [
            [2000, 1, 3, 1],
            [2000, 2, 8, 2],
            [2000, 3, 8, 3],
            [2000, 4, 6, 4],
            [2000, 5, 5, 5],
            [2000, 6, 3, 6],
            [2000, 7, 9, 7],
            [2000, 8, 7, 1],
            [2000, 9, 5, 2],
            [2000, 10, 11, 3],
            [2000, 11, 16, 4],
            [2000, 12, 29, 5],
            [2001, 1, 1, 1],
            [2001, 2, 6, 2],
            [2001, 3, 7, 3],
            [2001, 4, 5, 4],
            [2001, 5, 4, 5],
            [2001, 6, 9, 6],
            [2001, 7, 8, 7],
            [2001, 8, 6, 1],
            [2001, 9, 4, 2],
            [2001, 10, 10, 3],
            [2001, 11, 15, 4],
            [2001, 12, 21, 5]
        ];
    }

    /**
     * @dataProvider providerDayOfYear
     *
     * @param integer $year      The year.
     * @param integer $month     The expected month.
     * @param integer $day       The expected day.
     * @param integer $dayOfYear The day-of-year.
     */
    public function testOfYearDay($year, $month, $day, $dayOfYear)
    {
        $this->assertLocalDateEquals($year, $month, $day, LocalDate::ofYearDay($year, $dayOfYear));
    }

    /**
     * @dataProvider providerDayOfYear
     *
     * @param integer $year      The year to test.
     * @param integer $month     The month to test.
     * @param integer $day       The day-of-month to test.
     * @param integer $dayOfYear The expected day-of-year number.
     */
    public function testGetDayOfYear($year, $month, $day, $dayOfYear)
    {
        $this->assertSame($dayOfYear, LocalDate::of($year, $month, $day)->getDayOfYear());
    }

    /**
     * @return array
     */
    public function providerDayOfYear()
    {
        return [
            [2000, 1, 1, 1],
            [2000, 1, 31, 31],
            [2000, 2, 1, 32],
            [2000, 2, 29, 60],
            [2000, 3, 1, 61],
            [2000, 3, 31, 91],
            [2000, 4, 1, 92],
            [2000, 4, 30, 121],
            [2000, 5, 1, 122],
            [2000, 5, 31, 152],
            [2000, 6, 1, 153],
            [2000, 6, 30, 182],
            [2000, 7, 1, 183],
            [2000, 7, 31, 213],
            [2000, 8, 1, 214],
            [2000, 8, 31, 244],
            [2000, 9, 1, 245],
            [2000, 9, 30, 274],
            [2000, 10, 1, 275],
            [2000, 10, 31, 305],
            [2000, 11, 1, 306],
            [2000, 11, 30, 335],
            [2000, 12, 1, 336],
            [2000, 12, 31, 366],
            [2001, 1, 1, 1],
            [2001, 1, 31, 31],
            [2001, 2, 1, 32],
            [2001, 2, 28, 59],
            [2001, 3, 1, 60],
            [2001, 3, 31, 90],
            [2001, 4, 1, 91],
            [2001, 4, 30, 120],
            [2001, 5, 1, 121],
            [2001, 5, 31, 151],
            [2001, 6, 1, 152],
            [2001, 6, 30, 181],
            [2001, 7, 1, 182],
            [2001, 7, 31, 212],
            [2001, 8, 1, 213],
            [2001, 8, 31, 243],
            [2001, 9, 1, 244],
            [2001, 9, 30, 273],
            [2001, 10, 1, 274],
            [2001, 10, 31, 304],
            [2001, 11, 1, 305],
            [2001, 11, 30, 334],
            [2001, 12, 1, 335],
            [2001, 12, 31, 365]
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
        $localDate = LocalDate::of($year, $month, $day)->withYear($newYear);
        $this->assertLocalDateEquals($newYear, $month, $expectedDay, $localDate);
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
        $localDate = LocalDate::of($year, $month, $day)->withMonth($newMonth);
        $this->assertLocalDateEquals($year, $newMonth, $expectedDay, $localDate);
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
     * @dataProvider providerWithDay
     *
     * @param integer $year   The base year.
     * @param integer $month  The base month.
     * @param integer $day    The base day-of-month.
     * @param integer $newDay The new day-of-month.
     */
    public function testWithDay($year, $month, $day, $newDay)
    {
        $localDate = LocalDate::of($year, $month, $day)->withDay($newDay);
        $this->assertLocalDateEquals($year, $month, $newDay, $localDate);
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
        LocalDate::of($year, $month, $day)->withDay($newDay);
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
     * @dataProvider providerPlusYears
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $ay The number of years to add.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testPlusYears($y, $m, $d, $ay, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusYears($ay));
    }

    /**
     * @return array
     */
    public function providerPlusYears()
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2016, 2, 3],
            [2016, 3, 4, -1, 2015, 3, 4],
            [2000, 2, 29, 1, 2001, 2, 28],
            [2000, 2, 29, -1, 1999,2, 28]
        ];
    }

    /**
     * @dataProvider providerPlusMonths
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $am The number of months to add.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testPlusMonths($y, $m, $d, $am, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusMonths($am));
    }

    /**
     * @return array
     */
    public function providerPlusMonths()
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2015, 3, 3],
            [2015, 2, 3, 12, 2016, 2, 3],
            [2015, 2, 3, 13, 2016, 3, 3],
            [2016, 3, 4, -1, 2016, 2, 4],
            [2016, 3, 4, -3, 2015, 12, 4],
            [2016, 3, 4, -12, 2015, 3, 4],
            [2011, 12, 31, 1, 2012, 1, 31],
            [2011, 12, 31, 2, 2012, 2, 29],
            [2012, 12, 31, 1, 2013, 1, 31],
            [2012, 12, 31, 2, 2013, 2, 28],
            [2012, 12, 31, 3, 2013, 3, 31],
            [2013, 12, 31, 2, 2014, 2, 28],
            [2013, 12, 31, 4, 2014, 4, 30]
        ];
    }

    /**
     * @dataProvider providerPlusWeeks
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $aw The number of weeks to add.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testPlusWeeks($y, $m, $d, $aw, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusWeeks($aw));
    }

    /**
     * @return array
     */
    public function providerPlusWeeks()
    {
        return [
            [2014, 7, 31, 0, 2014, 7, 31],
            [2014, 7, 31, 1, 2014, 8, 7],
            [2014, 7, 31, 5, 2014, 9, 4],
            [2014, 7, 31, 30, 2015, 2, 26],
            [2014, 8, 2, 30, 2015, 2, 28],
            [2014, 8, 3, 30, 2015, 3, 1],
            [2014, 7, 31, -9, 2014, 5, 29]
        ];
    }

    /**
     * @dataProvider providerPlusDays
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $ad The number of days to add.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testPlusDays($y, $m, $d, $ad, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusDays($ad));
    }

    /**
     * @return array
     */
    public function providerPlusDays()
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2014, 1, 2, 29, 2014, 1, 31],
            [2014, 1, 2, 30, 2014, 2, 1],
            [2014, 1, 2, 365, 2015, 1, 2],
            [2012, 1, 1, 365, 2012, 12, 31],
            [2012, 1, 1, 366, 2013, 1, 1],
            [2012, 1, 2, -1, 2012, 1, 1],
            [2012, 1, 1, -1, 2011, 12, 31]
        ];
    }

    /**
     * @dataProvider providerMinusYears
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $sy The number of years to subtract.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function tesMinusYears($y, $m, $d, $sy, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusYears($sy));
    }

    /**
     * @return array
     */
    public function providerMinusYears()
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2014, 2, 3],
            [2016, 3, 4, -1, 2015, 3, 4],
            [2000, 2, 29, 1, 1999, 2, 28],
            [2000, 2, 29, -1, 2001,2, 28]
        ];
    }

    /**
     * @dataProvider providerMinusMonths
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $sm The number of months to subtract.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testMinusMonths($y, $m, $d, $sm, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusMonths($sm));
    }

    /**
     * @return array
     */
    public function providerMinusMonths()
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2015, 1, 3],
            [2015, 2, 3, 12, 2014, 2, 3],
            [2015, 2, 3, 13, 2014, 1, 3],
            [2016, 3, 4, -1, 2016, 4, 4],
            [2016, 3, 4, -10, 2017, 1, 4],
            [2016, 3, 4, -12, 2017, 3, 4],
            [2012, 1, 31, 1, 2011, 12, 31],
            [2011, 12, 31, 10, 2011, 2, 28],
            [2013, 12, 31, 22, 2012, 2, 29],
            [2012, 12, 31, 1, 2012, 11, 30],
            [2012, 12, 31, 2, 2012, 10, 31],
            [2013, 12, 31, -2, 2014, 2, 28],
            [2013, 12, 31, -26, 2016, 2, 29]
        ];
    }

    /**
     * @dataProvider providerMinusWeeks
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $sw The number of weeks to subtract.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testMinusWeeks($y, $m, $d, $sw, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusWeeks($sw));
    }

    /**
     * @return array
     */
    public function providerMinusWeeks()
    {
        return [
            [2014, 7, 31, 0, 2014, 7, 31],
            [2014, 7, 31, 1, 2014, 7, 24],
            [2014, 7, 31, 5, 2014, 6, 26],
            [2014, 7, 31, 30, 2014, 1, 2],
            [2014, 8, 2, 30, 2014, 1, 4],
            [2014, 8, 3, 50, 2013, 8, 18],
            [2014, 7, 31, -50, 2015, 7, 16]
        ];
    }

    /**
     * @dataProvider providerMinusDays
     *
     * @param integer $y  The base year.
     * @param integer $m  The base month.
     * @param integer $d  The base day.
     * @param integer $sd The number of days to subtract.
     * @param integer $ey The expected resulting year.
     * @param integer $em The expected resulting month.
     * @param integer $ed The expected resulting day.
     */
    public function testMinusDays($y, $m, $d, $sd, $ey, $em, $ed)
    {
        $this->assertLocalDateEquals($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusDays($sd));
    }

    /**
     * @return array
     */
    public function providerMinusDays()
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2014, 1, 2, -29, 2014, 1, 31],
            [2014, 1, 2, -30, 2014, 2, 1],
            [2014, 1, 2, 365, 2013, 1, 2],
            [2013, 1, 1, 365, 2012, 1, 2],
            [2013, 1, 1, 366, 2012, 1, 1],
            [2013, 1, 1, 367, 2011, 12, 31],
            [2013, 1, 1, 1000, 2010, 4, 7]
        ];
    }

    public function testAtTime()
    {
        $localDateTime = LocalDate::of(1, 2, 3)->atTime(LocalTime::of(4, 5, 6, 7));
        $this->assertLocalDateTimeEquals(1, 2, 3, 4, 5, 6, 7, $localDateTime);
    }

    /**
     * @dataProvider providerToString
     *
     * @param integer $year     The year.
     * @param integer $month    The month.
     * @param integer $day      The day-of-month.
     * @param string  $expected The expected result string.
     */
    public function testToString($year, $month, $day, $expected)
    {
        $this->assertSame($expected, (string) LocalDate::of($year, $month, $day));
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [999, 1, 2, '0999-01-02'],
            [-2, 1, 1, '-0002-01-01']
        ];
    }
}
