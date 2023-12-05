<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

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
use Brick\DateTime\YearMonthRange;
use Brick\DateTime\YearWeek;
use PHPUnit\Framework\TestCase;

use function implode;
use function var_export;

/**
 * Base class for DateTime tests.
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @param string $className      The expected class name.
     * @param string $expectedString The expected string representation.
     * @param object $object         The object to test.
     */
    protected function assertIs(string $className, string $expectedString, object $object): void
    {
        self::assertInstanceOf($className, $object);
        self::assertSame($expectedString, (string) $object);
    }

    /**
     * @param int     $epochSecond The expected epoch second.
     * @param int     $nano        The expected nanosecond adjustment.
     * @param Instant $instant     The instant to test.
     */
    protected function assertInstantIs(int $epochSecond, int $nano, Instant $instant): void
    {
        $this->compare([$epochSecond, $nano], [
            $instant->getEpochSecond(),
            $instant->getNano(),
        ]);
    }

    /**
     * @param int       $year  The expected year.
     * @param int       $month The expected month.
     * @param int       $day   The expected day.
     * @param LocalDate $date  The local date to test.
     */
    protected function assertLocalDateIs(int $year, int $month, int $day, LocalDate $date): void
    {
        $this->compare([$year, $month, $day], [
            $date->getYear(),
            $date->getMonthValue(),
            $date->getDayOfMonth(),
        ]);

        // temporary assertions to test the deprecated getters as well
        self::assertSame($month, $date->getMonth());
        self::assertSame($day, $date->getDay());
    }

    /**
     * @param int       $hour   The expected hour.
     * @param int       $minute The expected minute.
     * @param int       $second The expected second.
     * @param int       $nano   The expected nano-of-second.
     * @param LocalTime $time   The local time to test.
     */
    protected function assertLocalTimeIs(int $hour, int $minute, int $second, int $nano, LocalTime $time): void
    {
        $this->compare([$hour, $minute, $second, $nano], [
            $time->getHour(),
            $time->getMinute(),
            $time->getSecond(),
            $time->getNano(),
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
    protected function assertLocalDateTimeIs(int $y, int $m, int $d, int $h, int $i, int $s, int $n, LocalDateTime $dateTime): void
    {
        $this->compare([$y, $m, $d, $h, $i, $s, $n], [
            $dateTime->getYear(),
            $dateTime->getMonthValue(),
            $dateTime->getDayOfMonth(),
            $dateTime->getHour(),
            $dateTime->getMinute(),
            $dateTime->getSecond(),
            $dateTime->getNano(),
        ]);

        // temporary assertions to test the deprecated getters as well
        self::assertSame($m, $dateTime->getMonth());
        self::assertSame($d, $dateTime->getDay());
    }

    /**
     * @param LocalDateTime $expected The expected local date-time.
     * @param LocalDateTime $actual   The actual local date-time.
     */
    protected function assertLocalDateTimeEquals(LocalDateTime $expected, LocalDateTime $actual): void
    {
        self::assertTrue($actual->isEqualTo($expected), "$actual != $expected");
    }

    protected function assertYearIs(int $yearValue, Year $year): void
    {
        $this->compare([$yearValue], [
            $year->getValue(),
        ]);
    }

    /**
     * @param int       $year      The expected year.
     * @param int       $month     The expected month.
     * @param YearMonth $yearMonth The year-month to test.
     */
    protected function assertYearMonthIs(int $year, int $month, YearMonth $yearMonth): void
    {
        $this->compare([$year, $month], [
            $yearMonth->getYear(),
            $yearMonth->getMonthValue(),
        ]);

        // temporary assertion to test the deprecated getter as well
        self::assertSame($month, $yearMonth->getMonth());
    }

    /**
     * @param int      $year     The expected year.
     * @param int      $week     The expected week.
     * @param YearWeek $yearWeek The year-week to test.
     */
    protected function assertYearWeekIs(int $year, int $week, YearWeek $yearWeek): void
    {
        $this->compare([$year, $week], [
            $yearWeek->getYear(),
            $yearWeek->getWeek(),
        ]);
    }

    /**
     * @param int      $month    The expected month.
     * @param int      $day      The expected day.
     * @param MonthDay $monthDay The month-day to test.
     */
    protected function assertMonthDayIs(int $month, int $day, MonthDay $monthDay): void
    {
        $this->compare([$month, $day], [
            $monthDay->getMonthValue(),
            $monthDay->getDayOfMonth(),
        ]);

        // temporary assertions to test the deprecated getters as well
        self::assertSame($month, $monthDay->getMonth());
        self::assertSame($day, $monthDay->getDay());
    }

    /**
     * @param int      $seconds  The expected seconds.
     * @param int      $nanos    The expected nanos.
     * @param Duration $duration The duration to test.
     */
    protected function assertDurationIs(int $seconds, int $nanos, Duration $duration): void
    {
        $this->compare([$seconds, $nanos], [
            $duration->getSeconds(),
            $duration->getNanos(),
        ]);
    }

    /**
     * @param int    $years  The expected number of years in the period.
     * @param int    $months The expected number of months in the period.
     * @param int    $days   The expected number of days in the period.
     * @param Period $period The period to test.
     */
    protected function assertPeriodIs(int $years, int $months, int $days, Period $period): void
    {
        $this->compare([$years, $months, $days], [
            $period->getYears(),
            $period->getMonths(),
            $period->getDays(),
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
    protected function assertLocalDateRangeIs(int $y1, int $m1, int $d1, int $y2, int $m2, int $d2, LocalDateRange $range): void
    {
        self::assertLocalDateIs($y1, $m1, $d1, $range->getStart());
        self::assertLocalDateIs($y2, $m2, $d2, $range->getEnd());
    }

    /**
     * @param int            $y1    The expected year of the start year-month.
     * @param int            $m1    The expected month-of-year of the start year-month.
     * @param int            $y2    The expected year of the end year-month.
     * @param int            $m2    The expected month-of-year of the end year-month.
     * @param YearMonthRange $range The YearMonthRange instance to test.
     */
    protected function assertYearMonthRangeIs(int $y1, int $m1, int $y2, int $m2, YearMonthRange $range): void
    {
        self::assertYearMonthIs($y1, $m1, $range->getStart());
        self::assertYearMonthIs($y2, $m2, $range->getEnd());
    }

    /**
     * @param TimeZone $expected The expected time-zone.
     * @param TimeZone $actual   The actual time-zone.
     */
    protected function assertTimeZoneEquals(TimeZone $expected, TimeZone $actual): void
    {
        self::assertTrue($actual->isEqualTo($expected), "$actual != $expected");
    }

    /**
     * @param int            $totalSeconds   The expected total offset in seconds.
     * @param TimeZoneOffset $timeZoneOffset The time-zone offset to test.
     */
    protected function assertTimeZoneOffsetIs(int $totalSeconds, TimeZoneOffset $timeZoneOffset): void
    {
        $this->compare([$totalSeconds], [
            $timeZoneOffset->getTotalSeconds(),
        ]);
    }

    /**
     * @param array $expected The expected values.
     * @param array $actual   The actual values, count & keys matching expected values.
     */
    private function compare(array $expected, array $actual): void
    {
        $message = $this->export($actual) . ' !== ' . $this->export($expected);

        foreach ($expected as $key => $value) {
            self::assertSame($value, $actual[$key], $message);
        }
    }

    /**
     * Exports the given values as a string.
     *
     * @param array $values The values to export.
     */
    private function export(array $values): string
    {
        foreach ($values as &$value) {
            $value = var_export($value, true);
        }

        return '(' . implode(', ', $values) . ')';
    }
}
