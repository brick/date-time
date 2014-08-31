<?php

namespace Brick\DateTime;

use Brick\DateTime\Utility\Cast;

/**
 * Represents a year in the proleptic calendar.
 */
class Year
{
    const MIN_VALUE = LocalDate::MIN_YEAR;
    const MAX_VALUE = LocalDate::MAX_YEAR;

    /**
     * The year being represented.
     *
     * @var integer
     */
    private $year;

    /**
     * Class constructor.
     *
     * @param integer $year The year to represent, validated.
     */
    private function __construct($year)
    {
        $this->year = $year;
    }

    /**
     * @param integer $year
     *
     * @return Year
     */
    public static function of($year)
    {
        $year = Cast::toInteger($year);

        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * @param TimeZone $timeZone
     *
     * @return Year
     */
    public static function now(TimeZone $timeZone)
    {
        return new Year(LocalDate::now($timeZone)->getYear());
    }

    /**
     * @return integer
     */
    public function getValue()
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
     *
     * @return boolean
     */
    public function isLeap()
    {
        return Field\Year::isLeap($this->year);
    }

    /**
     * @param MonthDay $monthDay
     *
     * @return boolean
     */
    public function isValidMonthDay(MonthDay $monthDay)
    {
        return $monthDay->isValidYear($this->year);
    }

    /**
     * Returns the length of this year in days.
     *
     * @return integer The length of this year in days, 365 or 366.
     */
    public function getLength()
    {
        return $this->isLeap() ? 366 : 365;
    }

    /**
     * Returns a copy of this year with the specified number of years added.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param integer $years The years to add, may be negative.
     *
     * @return Year A Year based on this year with the period added.
     *
     * @throws DateTimeException If the resulting year exceeds the supported range.
     */
    public function plus($years)
    {
        $years = Cast::toInteger($years);

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
     * @param integer $years The years to subtract, may be negative.
     *
     * @return Year A Year based on this year with the period subtracted.
     *
     * @throws DateTimeException If the resulting year exceeds the supported range.
     */
    public function minus($years)
    {
        $years = Cast::toInteger($years);

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
     * @return integer [-1, 0, 1] If this year is before, equal to, or after the given year.
     */
    public function compareTo(Year $that)
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
     * @return boolean True if this year is equal to the given year, false otherwise.
     */
    public function isEqualTo(Year $that)
    {
        return $this->year === $that->year;
    }

    /**
     * Checks if this year is after the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return boolean True if this year is after the given year, false otherwise.
     */
    public function isAfter(Year $that)
    {
        return $this->year > $that->year;
    }

    /**
     * Checks if this year is before the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return boolean True if this year is before the given year, false otherwise.
     */
    public function isBefore(Year $that)
    {
        return $this->year < $that->year;
    }

    /**
     * Combines this year with a day-of-year to create a LocalDate.
     *
     * @param integer $dayOfYear The day-of-year to use, from 1 to 366.
     *
     * @return LocalDate
     *
     * @throws DateTimeException If the day-of-year is invalid for this year.
     */
    public function atDay($dayOfYear)
    {
        return LocalDate::ofYearDay($this->year, $dayOfYear);
    }

    /**
     * Combines this year with a month to create a YearMonth.
     *
     * @param integer $month The month-of-year to use, from 1 to 12.
     *
     * @return YearMonth
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function atMonth($month)
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
     *
     * @return LocalDate
     */
    public function atMonthDay(MonthDay $monthDay)
    {
        return $monthDay->atYear($this->year);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->year;
    }
}
