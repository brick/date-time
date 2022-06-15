<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a year in the proleptic calendar.
 */
final class Year implements JsonSerializable
{
    public const MIN_VALUE = LocalDate::MIN_YEAR;
    public const MAX_VALUE = LocalDate::MAX_YEAR;

    /**
     * The year being represented.
     */
    private int $year;

    /**
     * @param int $year The year to represent, validated.
     */
    private function __construct(int $year)
    {
        $this->year = $year;
    }

    /**
     * @throws DateTimeException If the year is out of range.
     */
    public static function of(int $year): Year
    {
        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * Returns the current year in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): Year
    {
        return new Year(LocalDate::now($timeZone, $clock)->getYear());
    }

    public function getValue(): int
    {
        return $this->year;
    }

    /**
     * Checks if the year is a leap year, according to the ISO proleptic calendar system rules.
     *
     * This method applies the current rules for leap years across the whole time-line.
     * In general, a year is a leap year if it is divisible by four without
     * remainder. However, years divisible by 100, are not leap years, with
     * the exception of years divisible by 400 which are.
     *
     * The calculation is proleptic - applying the same rules into the far future and far past.
     * This is historically inaccurate, but is correct for the ISO-8601 standard.
     */
    public function isLeap(): bool
    {
        return Field\Year::isLeap($this->year);
    }

    public function isValidMonthDay(MonthDay $monthDay): bool
    {
        return $monthDay->isValidYear($this->year);
    }

    /**
     * Returns the length of this year in days.
     *
     * @return int The length of this year in days, 365 or 366.
     */
    public function getLength(): int
    {
        return $this->isLeap() ? 366 : 365;
    }

    /**
     * Returns a copy of this year with the specified number of years added.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $years The years to add, may be negative.
     *
     * @return Year A Year based on this year with the period added.
     *
     * @throws DateTimeException If the resulting year exceeds the supported range.
     */
    public function plus(int $years): Year
    {
        if ($years === 0) {
            return $this;
        }

        $year = $this->year + $years;

        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * Returns a copy of this year with the specified number of years subtracted.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $years The years to subtract, may be negative.
     *
     * @return Year A Year based on this year with the period subtracted.
     *
     * @throws DateTimeException If the resulting year exceeds the supported range.
     */
    public function minus(int $years): Year
    {
        if ($years === 0) {
            return $this;
        }

        $year = $this->year - $years;

        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * Compares this year to another year.
     *
     * @param Year $that The year to compare to.
     *
     * @return int [-1, 0, 1] If this year is before, equal to, or after the given year.
     */
    public function compareTo(Year $that): int
    {
        if ($this->year > $that->year) {
            return 1;
        }

        if ($this->year < $that->year) {
            return -1;
        }

        return 0;
    }

    /**
     * Checks if this year is equal to the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return bool True if this year is equal to the given year, false otherwise.
     */
    public function isEqualTo(Year $that): bool
    {
        return $this->year === $that->year;
    }

    /**
     * Checks if this year is after the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return bool True if this year is after the given year, false otherwise.
     */
    public function isAfter(Year $that): bool
    {
        return $this->year > $that->year;
    }

    /**
     * Checks if this year is before the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return bool True if this year is before the given year, false otherwise.
     */
    public function isBefore(Year $that): bool
    {
        return $this->year < $that->year;
    }

    /**
     * Combines this year with a day-of-year to create a LocalDate.
     *
     * @param int $dayOfYear The day-of-year to use, from 1 to 366.
     *
     * @throws DateTimeException If the day-of-year is invalid for this year.
     */
    public function atDay(int $dayOfYear): LocalDate
    {
        return LocalDate::ofYearDay($this->year, $dayOfYear);
    }

    /**
     * Combines this year with a month to create a YearMonth.
     *
     * @param int $month The month-of-year to use, from 1 to 12.
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function atMonth(int $month): YearMonth
    {
        return YearMonth::of($this->year, $month);
    }

    /**
     * Combines this Year with a MonthDay to create a LocalDate.
     *
     * A month-day of February 29th will be adjusted to February 28th
     * in the resulting date if the year is not a leap year.
     *
     * @param MonthDay $monthDay The month-day to use.
     */
    public function atMonthDay(MonthDay $monthDay): LocalDate
    {
        return $monthDay->atYear($this->year);
    }

    /**
     * Returns LocalDateRange that contains all days of this year.
     */
    public function toLocalDateRange(): LocalDateRange
    {
        return LocalDateRange::of(
            $this->atMonth(1)->getFirstDay(),
            $this->atMonth(12)->getLastDay()
        );
    }

    /**
     * Serializes as a string using {@see Year::__toString()}.
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return (string) $this->year;
    }
}
