<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Month;
use Brick\DateTime\MonthDay;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\Year;
use Brick\DateTime\YearMonth;

use PHPUnit\Framework\TestCase;

/**
 * Base class for DateTime tests.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @param int $epochSecond    The epoch second.
     * @param int $nanoAdjustment The nanosecond adjustment to the epoch second.
     */
    protected function setClockTime(int $epochSecond, int $nanoAdjustment = 0)
    {
        Clock::setDefault(new FixedClock(Instant::of($epochSecond, $nanoAdjustment)));
    }

    /**
     * @param int     $epochSecond The expected epoch second.
     * @param int     $nano        The expected nanosecond adjustment.
     * @param Instant $instant     The instant to test.
     */
    protected function assertInstantIs(int $epochSecond, int $nano, Instant $instant)
    {
        $this->compare([$epochSecond, $nano], [
            $instant->getEpochSecond(),
            $instant->getNano()
        ]);
    }

    /**
     * @param int       $year  The expected year.
     * @param int       $month The expected month.
     * @param int       $day   The expected day.
     * @param LocalDate $date  The local date to test.
     */
    protected function assertLocalDateIs(int $year, int $month, int $day, LocalDate $date)
    {
        $this->compare([$year, $month, $day], [
            $date->getYear(),
            $date->getMonth(),
            $date->getDay()
        ]);
    }

    /**
     * @param int       $hour   The expected hour.
     * @param int       $minute The expected minute.
     * @param int       $second The expected second.
     * @param int       $nano   The expected nano-of-second.
     * @param LocalTime $time   The local time to test.
     */
    protected function assertLocalTimeIs(int $hour, int $minute, int $second, int $nano, LocalTime $time)
    {
        $this->compare([$hour, $minute, $second, $nano], [
            $time->getHour(),
            $time->getMinute(),
            $time->getSecond(),
            $time->getNano()
        ]);
    }

    /**
     * @param int           $y        The expected year.
     * @param int           $m        The expected month.
     * @param int           $d        The expected day.
     * @param int           $h        The expected hour.
     * @param int           $i        The expected minute.
     * @param int           $s        The expected second.
     * @param int           $n        The expected nano-of-second.
     * @param LocalDateTime $dateTime The local date-time to test.
     */
    protected function assertLocalDateTimeIs(int $y, int $m, int $d, int $h, int $i, int $s, int $n, LocalDateTime $dateTime)
    {
        $this->compare([$y, $m, $d, $h, $i, $s, $n], [
            $dateTime->getYear(),
            $dateTime->getMonth(),
            $dateTime->getDay(),
            $dateTime->getHour(),
            $dateTime->getMinute(),
            $dateTime->getSecond(),
            $dateTime->getNano()
        ]);
    }

    /**
     * @param LocalDateTime $expected The expected local date-time.
     * @param LocalDateTime $actual   The actual local date-time.
     */
    protected function assertLocalDateTimeEquals(LocalDateTime $expected, LocalDateTime $actual)
    {
        $this->assertTrue($actual->isEqualTo($expected), "$actual != $expected");
    }

    /**
     * @param int  $yearValue
     * @param Year $year
     */
    protected function assertYearIs(int $yearValue, Year $year)
    {
        $this->compare([$yearValue], [
            $year->getValue()
        ]);
    }

    /**
     * @param int       $year      The expected year.
     * @param int       $month     The expected month.
     * @param YearMonth $yearMonth The year-month to test.
     */
    protected function assertYearMonthIs(int $year, int $month, YearMonth $yearMonth)
    {
        $this->compare([$year, $month], [
            $yearMonth->getYear(),
            $yearMonth->getMonth()
        ]);
    }

    /**
     * @param int      $month    The expected month.
     * @param int      $day      The expected day.
     * @param MonthDay $monthDay The month-day to test.
     */
    protected function assertMonthDayIs(int $month, int $day, MonthDay $monthDay)
    {
        $this->compare([$month, $day], [
            $monthDay->getMonth(),
            $monthDay->getDay()
        ]);
    }

    /**
     * @param int   $monthValue The expected month-of-year value, from 1 to 12.
     * @param Month $month      The Month instance to test.
     */
    protected function assertMonthIs(int $monthValue, Month $month)
    {
        $this->compare([$monthValue], [
            $month->getValue()
        ]);
    }

    /**
     * @param int       $dayOfWeekValue The expected day-of-week value, from 1 to 7.
     * @param DayOfWeek $dayOfWeek      The DayOfWeek instance to test.
     */
    protected function assertDayOfWeekIs(int $dayOfWeekValue, DayOfWeek $dayOfWeek)
    {
        $this->compare([$dayOfWeekValue], [
            $dayOfWeek->getValue()
        ]);
    }

    /**
     * @param int      $seconds  The expected seconds.
     * @param int      $nanos    The expected nanos.
     * @param Duration $duration The duration to test.
     */
    protected function assertDurationIs(int $seconds, int $nanos, Duration $duration)
    {
        $this->compare([$seconds, $nanos], [
            $duration->getSeconds(),
            $duration->getNanos()
        ]);
    }

    /**
     * @param int     $years  The expected number of years in the period.
     * @param int     $months The expected number of months in the period.
     * @param int     $days   The expected number of days in the period.
     * @param Period  $period The period to test.
     */
    protected function assertPeriodIs(int $years, int $months, int $days, Period $period)
    {
        $this->compare([$years, $months, $days], [
            $period->getYears(),
            $period->getMonths(),
            $period->getDays()
        ]);
    }

    /**
     * @param int            $y1    The expected year of the start date.
     * @param int            $m1    The expected month-of-year of the start date.
     * @param int            $d1    The expected day-of-month of the start date.
     * @param int            $y2    The expected year of the end date.
     * @param int            $m2    The expected month-of-year of the end date.
     * @param int            $d2    The expected day-of-month of the end date.
     * @param LocalDateRange $range The LocalDateRange instance to test.
     */
    protected function assertLocalDateRangeIs(int $y1, int $m1, int $d1, int $y2, int $m2, int $d2, LocalDateRange $range)
    {
        $this->assertLocalDateIs($y1, $m1, $d1, $range->getStartDate());
        $this->assertLocalDateIs($y2, $m2, $d2, $range->getEndDate());
    }

    /**
     * @param TimeZone $expected The expected time-zone.
     * @param TimeZone $actual   The actual time-zone.
     */
    protected function assertTimeZoneEquals(TimeZone $expected, TimeZone $actual)
    {
        $this->assertTrue($actual->isEqualTo($expected), "$actual != $expected");
    }

    /**
     * @param int            $totalSeconds   The expected total offset in seconds.
     * @param TimeZoneOffset $timeZoneOffset The time-zone offset to test.
     */
    protected function assertTimeZoneOffsetIs(int $totalSeconds, TimeZoneOffset $timeZoneOffset)
    {
        $this->compare([$totalSeconds], [
            $timeZoneOffset->getTotalSeconds()
        ]);
    }

    /**
     * @param array $expected The expected values.
     * @param array $actual   The actual values, count & keys matching expected values.
     */
    private function compare(array $expected, array $actual)
    {
        $message = $this->export($actual) . ' !== ' . $this->export($expected);

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $actual[$key], $message);
        }
    }

    /**
     * Exports the given values as a string.
     *
     * @param array $values The values to export.
     *
     * @return string
     */
    private function export(array $values) : string
    {
        foreach ($values as & $value) {
            $value = \var_export($value, true);
        }

        return '(' . \implode(', ', $values) . ')';
    }
}
