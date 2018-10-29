<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;

/**
 * Represents the combination of a year and a month.
 */
final class YearMonth
{
    /**
     * The year, from MIN_YEAR to MAX_YEAR.
     *
     * @var int
     */
    private $year;

    /**
     * The month, from 1 to 12.
     *
     * @var int
     */
    private $month;

    /**
     * Class constructor.
     *
     * @param int $year  The year, validated from MIN_YEAR to MAX_YEAR.
     * @param int $month The month, validated in the range 1 to 12.
     */
    private function __construct(int $year, int $month)
    {
        $this->year  = $year;
        $this->month = $month;
    }

    /**
     * Obtains an instance of `YearMonth` from a year and month.
     *
     * @param int $year  The year, from MIN_YEAR to MAX_YEAR.
     * @param int $month The month-of-year, from 1 (January) to 12 (December).
     *
     * @return YearMonth
     *
     * @throws DateTimeException
     */
    public static function of(int $year, int $month) : YearMonth
    {
        Field\Year::check($year);
        Field\MonthOfYear::check($month);

        return new YearMonth($year, $month);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return YearMonth
     *
     * @throws DateTimeException      If the year-month is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result) : YearMonth
    {
        return YearMonth::of(
            (int) $result->getField(Field\Year::NAME),
            (int) $result->getField(Field\MonthOfYear::NAME)
        );
    }

    /**
     * Obtains an instance of `YearMonth` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-12`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return YearMonth
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, DateTimeParser $parser = null) : YearMonth
    {
        if (! $parser) {
            $parser = IsoParsers::yearMonth();
        }

        return YearMonth::from($parser->parse($text));
    }

    /**
     * Returns the current year-month in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     *
     * @param TimeZone   $timeZone
     * @param Clock|null $clock
     *
     * @return YearMonth
     */
    public static function now(TimeZone $timeZone, Clock $clock = null) : YearMonth
    {
        $localDate = LocalDate::now($timeZone, $clock);

        return new YearMonth($localDate->getYear(), $localDate->getMonth());
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
    public function getMonth() : int
    {
        return $this->month;
    }

    /**
     * Returns whether the year is a leap year.
     *
     * @return bool
     */
    public function isLeapYear() : bool
    {
        return Year::of($this->year)->isLeap();
    }

    /**
     * Returns the length of the month in days, taking account of the year.
     *
     * @return int
     */
    public function getLengthOfMonth() : int
    {
        return Month::of($this->month)->getLength($this->isLeapYear());
    }

    /**
     * Returns the length of the year in days, either 365 or 366.
     *
     * @return int
     */
    public function getLengthOfYear() : int
    {
        return $this->isLeapYear() ? 366: 365;
    }

    /**
     * @param YearMonth $that
     *
     * @return int [-1,0,1] If this year-month is before, on, or after the given year-month.
     */
    public function compareTo(YearMonth $that) : int
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

        return 0;
    }

    /**
     * @param YearMonth $that
     *
     * @return bool
     */
    public function isEqualTo(YearMonth $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * @param YearMonth $that
     *
     * @return bool
     */
    public function isBefore(YearMonth $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * @param YearMonth $that
     *
     * @return bool
     */
    public function isBeforeOrEqualTo(YearMonth $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * @param YearMonth $that
     *
     * @return bool
     */
    public function isAfter(YearMonth $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * @param YearMonth $that
     *
     * @return bool
     */
    public function isAfterOrEqualTo(YearMonth $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns a copy of this YearMonth with the year altered.
     *
     * @param int $year
     *
     * @return YearMonth
     *
     * @throws DateTimeException If the year is not valid.
     */
    public function withYear(int $year) : YearMonth
    {
        if ($year === $this->year) {
            return $this;
        }

        Field\Year::check($year);

        return new YearMonth($year, $this->month);
    }

    /**
     * Returns a copy of this YearMonth with the month-of-year altered.
     *
     * @param int $month
     *
     * @return YearMonth
     *
     * @throws DateTimeException If the month-of-year is not valid.
     */
    public function withMonth(int $month) : YearMonth
    {
        if ($month === $this->month) {
            return $this;
        }

        Field\MonthOfYear::check($month);

        return new YearMonth($this->year, $month);
    }

    /**
     * @return LocalDate
     */
    public function getFirstDay() : LocalDate
    {
        return $this->atDay(1);
    }

    /**
     * @return LocalDate
     */
    public function getLastDay() : LocalDate
    {
        return $this->atDay($this->getLengthOfMonth());
    }

    /**
     * Combines this year-month with a day-of-month to create a LocalDate.
     *
     * @param int $day The day-of-month to use, valid for the year-month.
     *
     * @return LocalDate The date formed from this year-month and the specified day.
     *
     * @throws DateTimeException If the day is not valid for this year-month.
     */
    public function atDay(int $day) : LocalDate
    {
        return LocalDate::of($this->year, $this->month, $day);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in years added.
     *
     * @param int $years
     *
     * @return YearMonth
     */
    public function plusYears(int $years) : YearMonth
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in months added.
     *
     * @param int $months
     *
     * @return YearMonth
     */
    public function plusMonths(int $months) : YearMonth
    {
        if ($months === 0) {
            return $this;
        }

        $month = $this->month + $months - 1;

        $yearDiff = Math::floorDiv($month, 12);
        $month = Math::floorMod($month, 12) + 1;

        $year = $this->year + $yearDiff;

        return new YearMonth($year, $month);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in years subtracted.
     *
     * @param int $years
     *
     * @return YearMonth
     */
    public function minusYears(int $years) : YearMonth
    {
        return $this->plusYears(- $years);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in months subtracted.
     *
     * @param int $months
     *
     * @return YearMonth
     */
    public function minusMonths(int $months) : YearMonth
    {
        return $this->plusMonths(- $months);
    }

    /**
     * Returns the ISO 8601 representation of this YearMonth.
     *
     * @return string
     */
    public function __toString() : string
    {
        return \sprintf('%02u-%02u', $this->year, $this->month);
    }
}
