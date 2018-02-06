<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * Represents the combination of a year and a week.
 */
class YearWeek
{
    /**
     * The year, from MIN_YEAR to MAX_YEAR.
     *
     * @var int
     */
    private $year;

    /**
     * The week number, from 1 to 53. Must be valid for the year.
     *
     * @var int
     */
    private $week;

    /**
     * Class constructor.
     *
     * @param int $year The year, validated from MIN_YEAR to MAX_YEAR.
     * @param int $week The week number, validated in the range 1 to 53, and valid for the year.
     */
    private function __construct(int $year, int $week)
    {
        $this->year = $year;
        $this->week = $week;
    }

    /**
     * Obtains an instance of `YearWeek` from a year and week number.
     *
     * @param int $year The year, from MIN_YEAR to MAX_YEAR.
     * @param int $week The week number, from 1 to 53.
     *
     * @return YearWeek
     *
     * @throws DateTimeException
     */
    public static function of(int $year, int $week) : YearWeek
    {
        Field\Year::check($year);
        Field\WeekOfYear::check($week, $year);

        return new YearWeek($year, $week);
    }

    /**
     * @return int
     */
    public function getYear() : int
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getWeek() : int
    {
        return $this->week;
    }

    /**
     * @param YearWeek $that
     *
     * @return int [-1,0,1] If this year-week is before, on, or after the given year-week.
     */
    public function compareTo(YearWeek $that) : int
    {
        if ($this->year < $that->year) {
            return -1;
        }
        if ($this->year > $that->year) {
            return 1;
        }
        if ($this->week < $that->week) {
            return -1;
        }
        if ($this->week > $that->week) {
            return 1;
        }

        return 0;
    }

    /**
     * @param YearWeek $that
     *
     * @return bool
     */
    public function isEqualTo(YearWeek $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * @param YearWeek $that
     *
     * @return bool
     */
    public function isBefore(YearWeek $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * @param YearWeek $that
     *
     * @return bool
     */
    public function isBeforeOrEqualTo(YearWeek $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * @param YearWeek $that
     *
     * @return bool
     */
    public function isAfter(YearWeek $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * @param YearWeek $that
     *
     * @return bool
     */
    public function isAfterOrEqualTo(YearWeek $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns a copy of this YearWeek with the year altered.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     *
     * @param int $year
     *
     * @return YearWeek
     *
     * @throws DateTimeException If the year is not valid.
     */
    public function withYear(int $year) : YearWeek
    {
        if ($year === $this->year) {
            return $this;
        }

        Field\Year::check($year);

        $week = $this->week;

        if ($week === 53 && ! Field\WeekOfYear::is53WeekYear($year)) {
            $week = 52;
        }

        return new YearWeek($year, $week);
    }

    /**
     * Returns a copy of this YearWeek with the week altered.
     *
     * If the new week is 53 and the year does not have 53 weeks, week one of the following year is selected.
     *
     * @param int $week
     *
     * @return YearWeek
     *
     * @throws DateTimeException If the week is not valid.
     */
    public function withWeek(int $week) : YearWeek
    {
        if ($week === $this->week) {
            return $this;
        }

        Field\WeekOfYear::check($week);

        $year = $this->year;

        if ($week === 53 && ! Field\WeekOfYear::is53WeekYear($year)) {
            $year++;
            $week = 1;
        }

        return new YearWeek($year, $week);
    }

    /**
     * Combines this year-week with a day-of-week to create a LocalDate.
     *
     * @param int $dayOfWeek
     *
     * @return LocalDate
     */
    public function atDay(int $dayOfWeek) : LocalDate
    {
        $correction = LocalDate::of($this->year, 1, 4)->getDayOfWeek()->getValue() + 3;
        $dayOfYear = $this->week * 7 + $dayOfWeek - $correction;
        $maxDaysOfYear = Field\Year::isLeap($this->year) ? 366 : 365;

        if ($dayOfYear > $maxDaysOfYear) {
            return LocalDate::ofYearDay($this->year + 1, $dayOfYear - $maxDaysOfYear);
        }

        if ($dayOfYear > 0) {
            return LocalDate::ofYearDay($this->year, $dayOfYear);
        }

        $daysOfPreviousYear = Field\Year::isLeap($this->year - 1) ? 366 : 365;

        return LocalDate::ofYearDay($this->year - 1, $daysOfPreviousYear + $dayOfYear);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in years added.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     *
     * @param int $years
     *
     * @return YearWeek
     */
    public function plusYears(int $years) : YearWeek
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in weeks added.
     *
     * @param int $weeks
     *
     * @return YearWeek
     */
    public function plusWeeks(int $weeks) : YearWeek
    {
        if ($weeks === 0) {
            return $this;
        }

        $mondayOfWeek = $this->atDay(DayOfWeek::MONDAY)->plusWeeks($weeks);

        return $mondayOfWeek->getYearWeek();
    }

    /**
     * Returns a copy of this YearWeek with the specified period in years subtracted.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     *
     * @param int $years
     *
     * @return YearWeek
     */
    public function minusYears(int $years) : YearWeek
    {
        return $this->plusYears(- $years);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in weeks subtracted.
     *
     * @param int $weeks
     *
     * @return YearWeek
     */
    public function minusWeeks(int $weeks) : YearWeek
    {
        return $this->plusWeeks(- $weeks);
    }

    /**
     * Returns whether this year has 53 weeks.
     *
     * @return bool
     */
    public function is53WeekYear() : bool
    {
        return Field\WeekOfYear::is53WeekYear($this->year);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return \sprintf('%02u-W%02u', $this->year, $this->week);
    }
}
