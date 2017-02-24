<?php

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;

/**
 * A date without a time-zone in the ISO-8601 calendar system, such as `2007-12-03`.
 *
 * This class is immutable.
 */
class LocalDate implements DateTimeAccessor
{
    /**
     * The minimum supported year for instances of `LocalDate`, -999,999.
     */
    const MIN_YEAR = -999999;

    /**
     * The maximum supported year for instances of `LocalDate`, 999,999.
     */
    const MAX_YEAR = 999999;

    /**
     * The number of days from year zero to year 1970.
     */
    const DAYS_0000_TO_1970 = 719528;

    /**
     * The number of days in a 400 year cycle.
     */
    const DAYS_PER_CYCLE = 146097;

    /**
     * The year.
     *
     * @var integer
     */
    private $year;

    /**
     * The month-of-year.
     *
     * @var integer
     */
    private $month;

    /**
     * The day-of-month.
     *
     * @var integer
     */
    private $day;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param integer $year  The year, validated as an integer from MIN_YEAR to MAX_YEAR.
     * @param integer $month The month-of-year, validated as an integer from 1 to 12.
     * @param integer $day   The day-of-month, validated as an integer from 1 to 31, valid for the year-month.
     */
    private function __construct($year, $month, $day)
    {
        $this->year  = $year;
        $this->month = $month;
        $this->day   = $day;
    }

    /**
     * Obtains an instance of `LocalDate` from a year, month and day.
     *
     * @param integer $year  The year, from MIN_YEAR to MAX_YEAR.
     * @param integer $month The month-of-year, from 1 (January) to 12 (December).
     * @param integer $day   The day-of-month, from 1 to 31.
     *
     * @return LocalDate The LocalDate instance.
     *
     * @throws DateTimeException If the date is not valid.
     */
    public static function of($year, $month, $day)
    {
        Field\Year::check($year);
        Field\MonthOfYear::check($month);
        Field\DayOfMonth::check($day, $month, $year);

        return new LocalDate($year, $month, $day);
    }

    /**
     * Obtains an instance of `LocalDate` from a year and day-of-year.
     *
     * @param integer $year      The year, from MIN_YEAR to MAX_YEAR.
     * @param integer $dayOfYear The day-of-year, from 1 to 366.
     *
     * @return LocalDate
     */
    public static function ofYearDay($year, $dayOfYear)
    {
        Field\Year::check($year);
        Field\DayOfYear::check($dayOfYear, $year);

        $isLeap = Field\Year::isLeap($year);

        $monthOfYear = Month::of(intdiv($dayOfYear - 1, 31) + 1);
        $monthEnd = $monthOfYear->getFirstDayOfYear($isLeap) + $monthOfYear->getLength($isLeap) - 1;

        if ($dayOfYear > $monthEnd) {
            $monthOfYear = $monthOfYear->plus(1);
        }

        $dayOfMonth = $dayOfYear - $monthOfYear->getFirstDayOfYear($isLeap) + 1;

        return LocalDate::of($year, $monthOfYear->getValue(), $dayOfMonth);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return LocalDate
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result)
    {
        $year  = (int) $result->getField(Field\Year::NAME);
        $month = (int) $result->getField(Field\MonthOfYear::NAME);
        $day   = (int) $result->getField(Field\DayOfMonth::NAME);

        return LocalDate::of($year, $month, $day);
    }

    /**
     * Obtains an instance of `LocalDate` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-12-03`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return LocalDate
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse($text, DateTimeParser $parser = null)
    {
        if (! $parser) {
            $parser = IsoParsers::localDate();
        }

        return LocalDate::from($parser->parse($text));
    }

    /**
     * Obtains an instance of `LocalDate` from the epoch day count.
     *
     * The Epoch Day count is a simple incrementing count of days
     * where day 0 is 1970-01-01. Negative numbers represent earlier days.
     *
     * @param integer $epochDay
     *
     * @return LocalDate
     */
    public static function ofEpochDay($epochDay)
    {
        $zeroDay = $epochDay + self::DAYS_0000_TO_1970;
        // Find the march-based year.
        $zeroDay -= 60; // Adjust to 0000-03-01 so leap day is at end of four year cycle.
        $adjust = 0;
        if ($zeroDay < 0) {
            // Adjust negative years to positive for calculation.
            $adjustCycles = intdiv(($zeroDay + 1), self::DAYS_PER_CYCLE) - 1;
            $adjust = $adjustCycles * 400;
            $zeroDay += -$adjustCycles * self::DAYS_PER_CYCLE;
        }
        $yearEst = intdiv(400 * $zeroDay + 591, self::DAYS_PER_CYCLE);
        $doyEst = $zeroDay - (365 * $yearEst + intdiv($yearEst, 4) - intdiv($yearEst, 100) + intdiv($yearEst, 400));
        if ($doyEst < 0) {
            // Fix estimate.
            $yearEst--;
            $doyEst = $zeroDay - (365 * $yearEst + intdiv($yearEst, 4) - intdiv($yearEst, 100) + intdiv($yearEst, 400));
        }
        $yearEst += $adjust; // Reset any negative year.
        $marchDoy0 = $doyEst;

        // Convert march-based values back to January-based.
        $marchMonth0 = intdiv($marchDoy0 * 5 + 2, 153);
        $month = ($marchMonth0 + 2) % 12 + 1;
        $dom = $marchDoy0 - intdiv($marchMonth0 * 306 + 5, 10) + 1;
        $yearEst += intdiv($marchMonth0, 10);

        // Check year now we are certain it is correct.
        Field\Year::check($yearEst);

        return new LocalDate($yearEst, $month, $dom);
    }

    /**
     * Returns the current date, in the given time zone.
     *
     * @param TimeZone $timeZone
     *
     * @return LocalDate
     */
    public static function now(TimeZone $timeZone)
    {
        return ZonedDateTime::now($timeZone)->getDate();
    }

    /**
     * Returns the minimum supported LocalDate.
     *
     * This can be used by an application as a "far past" date.
     *
     * @return LocalDate
     */
    public static function min()
    {
        return LocalDate::of(self::MIN_YEAR, 1, 1);
    }

    /**
     * Returns the maximum supported LocalDate.
     *
     * This can be used by an application as a "far future" date.
     *
     * @return LocalDate
     */
    public static function max()
    {
        return LocalDate::of(self::MAX_YEAR, 12, 31);
    }

    /**
     * Returns the smallest LocalDate among the given values.
     *
     * @param LocalDate ... $dates The LocalDate objects to compare.
     *
     * @return LocalDate The earliest LocalDate object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(LocalDate ... $dates)
    {
        if (! $dates) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $min = LocalDate::max();

        foreach ($dates as $time) {
            if ($time->isBefore($min)) {
                $min = $time;
            }
        }

        return $min;
    }

    /**
     * Returns the highest LocalDate among the given values.
     *
     * @param LocalDate ... $dates The LocalDate objects to compare.
     *
     * @return LocalDate The latest LocalDate object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(LocalDate ... $dates)
    {
        if (! $dates) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $max = LocalDate::min();

        foreach ($dates as $time) {
            if ($time->isAfter($max)) {
                $max = $time;
            }
        }

        return $max;
    }

    /**
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @return YearMonth
     */
    public function getYearMonth()
    {
        return YearMonth::of($this->year, $this->month);
    }

    /**
     * @return DayOfWeek
     */
    public function getDayOfWeek()
    {
        return DayOfWeek::of(Math::floorMod($this->toEpochDay() + 3, 7) + 1);
    }

    /**
     * Returns the day-of-year, from 1 to 365, or 366 in a leap year.
     *
     * @return integer
     */
    public function getDayOfYear()
    {
        return Month::of($this->month)->getFirstDayOfYear($this->isLeapYear()) + $this->day - 1;
    }

    /**
     * Returns a copy of this LocalDate with the year altered.
     *
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     *
     * @param integer $year
     *
     * @return LocalDate
     *
     * @throws DateTimeException If the year is outside the valid range.
     */
    public function withYear($year)
    {
        if ($year === $this->year) {
            return $this;
        }

        Field\Year::check($year);

        return $this->resolvePreviousValid($year, $this->month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the month-of-year altered.
     *
     * If the day-of-month is invalid for the month and year, it will be changed to the last valid day of the month.
     *
     * @param integer $month
     *
     * @return LocalDate
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function withMonth($month)
    {
        if ($month === $this->month) {
            return $this;
        }

        Field\MonthOfYear::check($month);

        return $this->resolvePreviousValid($this->year, $month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the day-of-month altered.
     *
     * If the resulting date is invalid, an exception is thrown.
     *
     * @param integer $day
     *
     * @return LocalDate
     *
     * @throws DateTimeException If the day is invalid for the current year and month.
     */
    public function withDay($day)
    {
        if ($day === $this->day) {
            return $this;
        }

        Field\DayOfMonth::check($day, $this->month, $this->year);

        return new LocalDate($this->year, $this->month, $day);
    }

    /**
     * Returns a copy of this LocalDate with the specified Period added.
     *
     * @param Period $period
     *
     * @return LocalDate
     */
    public function plusPeriod(Period $period)
    {
        return $this
            ->plusYears($period->getYears())
            ->plusMonths($period->getMonths())
            ->plusDays($period->getDays());
    }

    /**
     * Returns a copy of this LocalDate with the specified period in years added.
     *
     * If the day-of-month is invalid for the resulting year and month,
     * it will be changed to the last valid day of the month.
     *
     * @param integer $years
     *
     * @return LocalDate
     */
    public function plusYears($years)
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in months added.
     *
     * If the day-of-month is invalid for the resulting year and month,
     * it will be changed to the last valid day of the month.
     *
     * @param integer $months
     *
     * @return LocalDate
     */
    public function plusMonths($months)
    {
        $month = $this->month + $months - 1;

        $yearDiff = Math::floorDiv($month, 12);
        $month = Math::floorMod($month, 12) + 1;

        $year = $this->year + $yearDiff;

        return $this->resolvePreviousValid($year, $month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weeks added.
     *
     * @param integer $weeks
     *
     * @return LocalDate
     */
    public function plusWeeks($weeks)
    {
        if ($weeks === 0) {
            return $this;
        }

        return $this->plusDays($weeks * 7);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in days added.
     *
     * @param integer $days
     *
     * @return LocalDate
     */
    public function plusDays($days)
    {
        if ($days === 0) {
            return $this;
        }

        return LocalDate::ofEpochDay($this->toEpochDay() + $days);
    }

    /**
     * Returns a copy of this LocalDate with the specified Period subtracted.
     *
     * @param Period $period
     *
     * @return LocalDate
     */
    public function minusPeriod(Period $period)
    {
        return $this->plusPeriod($period->negated());
    }

    /**
     * Returns a copy of this LocalDate with the specified period in years subtracted.
     *
     * @param integer $years
     *
     * @return LocalDate
     */
    public function minusYears($years)
    {
        return $this->plusYears(- $years);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in months subtracted.
     *
     * @param integer $months
     *
     * @return LocalDate
     */
    public function minusMonths($months)
    {
        return $this->plusMonths(- $months);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weeks subtracted.
     *
     * @param integer $eeks
     *
     * @return LocalDate
     */
    public function minusWeeks($eeks)
    {
        return $this->plusWeeks(- $eeks);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in days subtracted.
     *
     * @param integer $days
     *
     * @return LocalDate
     */
    public function minusDays($days)
    {
        return $this->plusDays(- $days);
    }

    /**
     * Returns -1 if this date is before the given date, 1 if after, 0 if the dates are equal.
     *
     * @param LocalDate $that
     *
     * @return integer [-1,0,1] If this date is before, on, or after the given date.
     */
    public function compareTo(LocalDate $that)
    {
        if ($this->year < $that->year) {
            return -1;
        }
        if ($this->year > $that->year) {
            return 1;
        }
        if ($this->month < $that->month) {
            return -1;
        }
        if ($this->month > $that->month) {
            return 1;
        }
        if ($this->day < $that->day) {
            return -1;
        }
        if ($this->day > $that->day) {
            return 1;
        }

        return 0;
    }

    /**
     * @param LocalDate $that
     *
     * @return boolean
     */
    public function isEqualTo(LocalDate $that)
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * @param LocalDate $that
     *
     * @return boolean
     */
    public function isBefore(LocalDate $that)
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * @param LocalDate $that
     *
     * @return boolean
     */
    public function isBeforeOrEqualTo(LocalDate $that)
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * @param LocalDate $that
     *
     * @return boolean
     */
    public function isAfter(LocalDate $that)
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * @param LocalDate $that
     *
     * @return boolean
     */
    public function isAfterOrEqualTo(LocalDate $that)
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Calculates the period between this date and another date.
     *
     * This calculates the period between the two dates in terms of years, months and days.
     * The result will be negative if the end is before the start.
     * The negative sign will be the same in each of year, month and day.
     *
     * The start date is included, but the end date is not.
     * The period is calculated by removing complete months, then calculating
     * the remaining number of days, adjusting to ensure that both have the same sign.
     * The number of months is then normalized into years and months based on a 12 month year.
     * A month is considered to be complete if the end day-of-month is greater
     * than or equal to the start day-of-month.
     *
     * For example, from `2010-01-15` to `2011-03-18` is 1 year, 2 months and 3 days.
     *
     * @param LocalDate $endDateExclusive
     *
     * @return Period
     */
    public function until(LocalDate $endDateExclusive)
    {
        $totalMonths = $endDateExclusive->getProlepticMonth() - $this->getProlepticMonth();
        $days = $endDateExclusive->day - $this->day;

        if ($totalMonths > 0 && $days < 0) {
            $totalMonths--;
            $calcDate = $this->plusMonths($totalMonths);
            $days = $endDateExclusive->toEpochDay() - $calcDate->toEpochDay();
        } elseif ($totalMonths < 0 && $days > 0) {
            $totalMonths++;
            $days -= $endDateExclusive->getLengthOfMonth();
        }

        $years = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;

        return Period::of($years, $months, $days);
    }

    /**
     * Returns a local date-time formed from this date at the specified time.
     *
     * @param LocalTime $time
     *
     * @return LocalDateTime
     */
    public function atTime(LocalTime $time)
    {
        return new LocalDateTime($this, $time);
    }

    /**
     * Checks if the year is a leap year, according to the ISO proleptic calendar system rules.
     *
     * @return boolean
     */
    public function isLeapYear()
    {
        return Field\Year::isLeap($this->year);
    }

    /**
     * Returns the length of the year represented by this date.
     *
     * @return integer The length of the year in days.
     */
    public function getLengthOfYear()
    {
        return $this->isLeapYear() ? 366 : 365;
    }

    /**
     * Returns the length of the month represented by this date.
     *
     * @return integer The length of the month in days.
     */
    public function getLengthOfMonth()
    {
        return Field\MonthOfYear::getLength($this->month, $this->year);
    }

    /**
     * Returns the number of days since the UNIX epoch of 1st January 1970.
     *
     * @return integer
     */
    public function toEpochDay()
    {
        $y = $this->year;
        $m = $this->month;

        $total = 365 * $y;

        if ($y >= 0) {
            $total += intdiv($y + 3, 4) - intdiv($y + 99, 100) + intdiv($y + 399, 400);
        } else {
            $total -= intdiv($y, -4) - intdiv($y, -100) + intdiv($y, -400);
        }

        $total += intdiv(367 * $m - 362, 12);
        $total += $this->day - 1;

        if ($m > 2) {
            $total--;
            if (! $this->isLeapYear()) {
                $total--;
            }
        }

        return $total - self::DAYS_0000_TO_1970;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($field)
    {
        switch ($field) {
            case Field\Year::NAME:
                return $this->year;

            case Field\MonthOfYear::NAME:
                return $this->month;

            case Field\DayOfMonth::NAME:
                return $this->day;

            default:
                return null;
        }
    }

    /**
     * Returns the ISO 8601 representation of this LocalDate.
     *
     * @return string
     */
    public function __toString()
    {
        $pattern = ($this->year < 0 ? '%05d' : '%04d') . '-%02d-%02d';

        return sprintf($pattern, $this->year, $this->month, $this->day);
    }

    /**
     * Resolves the date, resolving days past the end of month.
     *
     * @param integer $year  The year to represent, validated as an integer from MIN_YEAR to MAX_YEAR.
     * @param integer $month The month-of-year to represent, validated as an integer from 1 to 12.
     * @param integer $day   The day-of-month to represent, validated as an integer from 1 to 31.
     *
     * @return LocalDate
     */
    private function resolvePreviousValid($year, $month, $day)
    {
        if ($day > 28) {
            $day = min($day, YearMonth::of($year, $month)->getLengthOfMonth());
        }

        return new LocalDate($year, $month, $day);
    }

    /**
     * @return boolean
     */
    private function getProlepticMonth()
    {
        return $this->year * 12 + $this->month - 1;
    }
}
