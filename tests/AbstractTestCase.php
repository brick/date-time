<?php

namespace Brick\Tests\DateTime;

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
use Brick\DateTime\ReadableInstant;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\Year;
use Brick\DateTime\YearMonth;

/**
 * Base class for DateTime tests.
 */
abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param integer $epochSecond    The epoch second.
     * @param integer $nanoAdjustment The nanosecond adjustment to the epoch second.
     */
    protected function setClockTime($epochSecond, $nanoAdjustment = 0)
    {
        Clock::setDefault(new FixedClock(Instant::of($epochSecond, $nanoAdjustment)));
    }

    /**
     * @param integer         $epochSecond The expected epoch second.
     * @param integer         $nano        The expected nanosecond adjustment.
     * @param ReadableInstant $instant     The instant to test.
     */
    protected function assertReadableInstantEquals($epochSecond, $nano, ReadableInstant $instant)
    {
        $this->compare([$epochSecond, $nano], [
            $instant->getEpochSecond(),
            $instant->getNano()
        ]);
    }

    /**
     * @param integer   $year  The expected year.
     * @param integer   $month The expected month.
     * @param integer   $day   The expected day.
     * @param LocalDate $date  The local date to test.
     */
    protected function assertLocalDateEquals($year, $month, $day, LocalDate $date)
    {
        $this->compare([$year, $month, $day], [
            $date->getYear(),
            $date->getMonth(),
            $date->getDay()
        ]);
    }

    /**
     * @param integer   $hour   The expected hour.
     * @param integer   $minute The expected minute.
     * @param integer   $second The expected second.
     * @param integer   $nano   The expected nano-of-second.
     * @param LocalTime $time   The local time to test.
     */
    protected function assertLocalTimeEquals($hour, $minute, $second, $nano, LocalTime $time)
    {
        $this->compare([$hour, $minute, $second, $nano], [
            $time->getHour(),
            $time->getMinute(),
            $time->getSecond(),
            $time->getNano()
        ]);
    }

    /**
     * @param integer       $y        The expected year.
     * @param integer       $m        The expected month.
     * @param integer       $d        The expected day.
     * @param integer       $h        The expected hour.
     * @param integer       $i        The expected minute.
     * @param integer       $s        The expected second.
     * @param integer       $n        The expected nano-of-second.
     * @param LocalDateTime $dateTime The local date-time to test.
     */
    protected function assertLocalDateTimeEquals($y, $m, $d, $h, $i, $s, $n, LocalDateTime $dateTime)
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
     * @param integer $yearValue
     * @param Year    $year
     */
    protected function assertYearEquals($yearValue, Year $year)
    {
        $this->compare([$yearValue], [
            $year->getValue()
        ]);
    }

    /**
     * @param integer   $year      The expected year.
     * @param integer   $month     The expected month.
     * @param YearMonth $yearMonth The year-month to test.
     */
    protected function assertYearMonthEquals($year, $month, YearMonth $yearMonth)
    {
        $this->compare([$year, $month], [
            $yearMonth->getYear(),
            $yearMonth->getMonth()
        ]);
    }

    /**
     * @param integer  $month    The expected month.
     * @param integer  $day      The expected day.
     * @param MonthDay $monthDay The month-day to test.
     */
    protected function assertMonthDayEquals($month, $day, MonthDay $monthDay)
    {
        $this->compare([$month, $day], [
            $monthDay->getMonth(),
            $monthDay->getDay()
        ]);
    }

    /**
     * @param integer $monthValue The expected month-of-year value, from 1 to 12.
     * @param Month   $month      The Month instance to test.
     */
    protected function assertMonthEquals($monthValue, Month $month)
    {
        $this->compare([$monthValue], [
            $month->getValue()
        ]);
    }

    /**
     * @param integer   $dayOfWeekValue The expected day-of-week value, from 1 to 7.
     * @param DayOfWeek $dayOfWeek      The DayOfWeek instance to test.
     */
    protected function assertDayOfWeekEquals($dayOfWeekValue, DayOfWeek $dayOfWeek)
    {
        $this->compare([$dayOfWeekValue], [
            $dayOfWeek->getValue()
        ]);
    }

    /**
     * @param integer  $seconds  The expected seconds.
     * @param integer  $nanos    The expected nanos.
     * @param Duration $duration The duration to test.
     */
    protected function assertDurationEquals($seconds, $nanos, Duration $duration)
    {
        $this->compare([$seconds, $nanos], [
            $duration->getSeconds(),
            $duration->getNanos()
        ]);
    }

    /**
     * @param integer $years  The expected number of years in the period.
     * @param integer $months The expected number of months in the period.
     * @param integer $days   The expected number of days in the period.
     * @param Period  $period The period to test.
     */
    protected function assertPeriodEquals($years, $months, $days, Period $period)
    {
        $this->compare([$years, $months, $days], [
            $period->getYears(),
            $period->getMonths(),
            $period->getDays()
        ]);
    }

    /**
     * @param integer        $y1    The expected year of the start date.
     * @param integer        $m1    The expected month-of-year of the start date.
     * @param integer        $d1    The expected day-of-month of the start date.
     * @param integer        $y2    The expected year of the end date.
     * @param integer        $m2    The expected month-of-year of the end date.
     * @param integer        $d2    The expected day-of-month of the end date.
     * @param LocalDateRange $range The LocalDateRange instance to test.
     */
    protected function assertLocalDateRangeEquals($y1, $m1, $d1, $y2, $m2, $d2, LocalDateRange $range)
    {
        $this->assertLocalDateEquals($y1, $m1, $d1, $range->getStartDate());
        $this->assertLocalDateEquals($y2, $m2, $d2, $range->getEndDate());
    }

    /**
     * @param integer        $totalSeconds   The expected total offset in seconds.
     * @param TimeZoneOffset $timeZoneOffset The time-zone offset to test.
     */
    protected function assertTimeZoneOffsetEquals($totalSeconds, TimeZoneOffset $timeZoneOffset)
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
    private function export(array $values)
    {
        foreach ($values as & $value) {
            $value = var_export($value, true);
        }

        return '(' . implode(', ', $values) . ')';
    }
}
