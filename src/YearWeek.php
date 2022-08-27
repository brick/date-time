<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

use function sprintf;

/**
 * Represents the combination of a year and a week.
 */
final class YearWeek implements JsonSerializable
{
    /**
     * The year, from MIN_YEAR to MAX_YEAR.
     */
    private int $year;

    /**
     * The week number, from 1 to 53. Must be valid for the year.
     */
    private int $week;

    /**
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
     * @throws DateTimeException
     */
    public static function of(int $year, int $week): YearWeek
    {
        Field\Year::check($year);
        Field\WeekOfYear::check($week, $year);

        return new YearWeek($year, $week);
    }

    public static function now(TimeZone $timeZone, ?Clock $clock = null): YearWeek
    {
        return LocalDate::now($timeZone, $clock)->getYearWeek();
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    /**
     * @return int [-1,0,1] If this year-week is before, on, or after the given year-week.
     */
    public function compareTo(YearWeek $that): int
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

    public function isEqualTo(YearWeek $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isBefore(YearWeek $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(YearWeek $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isAfter(YearWeek $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(YearWeek $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns a copy of this YearWeek with the year altered.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     *
     * @throws DateTimeException If the year is not valid.
     */
    public function withYear(int $year): YearWeek
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
     * @throws DateTimeException If the week is not valid.
     */
    public function withWeek(int $week): YearWeek
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
     */
    public function atDay(int $dayOfWeek): LocalDate
    {
        Field\DayOfWeek::check($dayOfWeek);

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
     * Returns the first day of this week.
     */
    public function getFirstDay(): LocalDate
    {
        return $this->atDay(DayOfWeek::MONDAY);
    }

    /**
     * Returns the last day of this week.
     */
    public function getLastDay(): LocalDate
    {
        return $this->atDay(DayOfWeek::SUNDAY);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in years added.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     */
    public function plusYears(int $years): YearWeek
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): YearWeek
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
     */
    public function minusYears(int $years): YearWeek
    {
        return $this->plusYears(-$years);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in weeks subtracted.
     */
    public function minusWeeks(int $weeks): YearWeek
    {
        return $this->plusWeeks(-$weeks);
    }

    /**
     * Returns whether this year has 53 weeks.
     */
    public function is53WeekYear(): bool
    {
        return Field\WeekOfYear::is53WeekYear($this->year);
    }

    /**
     * Returns LocalDateRange that contains all days of this year week.
     */
    public function toLocalDateRange(): LocalDateRange
    {
        return LocalDateRange::of($this->getFirstDay(), $this->getLastDay());
    }

    /**
     * Serializes as a string using {@see YearWeek::__toString()}.
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        $pattern = ($this->year < 0 ? '%05d' : '%04d') . '-W%02d';

        return sprintf($pattern, $this->year, $this->week);
    }
}
