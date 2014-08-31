<?php

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Cast;

/**
 * A month-day in the ISO-8601 calendar system, such as `--12-03`.
 */
class MonthDay
{
    /**
     * The month-of-year, from 1 to 12.
     *
     * @var integer
     */
    private $month;

    /**
     * The day-of-month, from 1 to 31.
     *
     * @var integer
     */
    private $day;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param integer $month The month-of-year, validated.
     * @param integer $day   The day-of-month, validated.
     */
    private function __construct($month, $day)
    {
        $this->month = $month;
        $this->day   = $day;
    }

    /**
     * Obtains an instance of MonthDay.
     *
     * @param integer $month The month-of-year, from 1 (January) to 12 (December).
     * @param integer $day   The day-of-month, from 1 to 31.
     *
     * @return MonthDay
     *
     * @throws DateTimeException If the month-day is not valid.
     */
    public static function of($month, $day)
    {
        $month = Cast::toInteger($month);
        $day   = Cast::toInteger($day);

        Field\MonthOfYear::check($month);
        Field\DayOfMonth::check($day, $month);

        return new MonthDay($month, $day);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return MonthDay
     *
     * @throws DateTimeException      If the month-day is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result)
    {
        return MonthDay::of(
            (int) $result->getField(Field\MonthOfYear::NAME),
            (int) $result->getField(Field\DayOfMonth::NAME)
        );
    }

    /**
     * Obtains an instance of `LocalDate` from a text string.
     *
     * @param string              $text   The text to parse, such as `--12-03`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return MonthDay
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse($text, DateTimeParser $parser = null)
    {
        if (! $parser) {
            $parser = IsoParsers::monthDay();
        }

        return MonthDay::from($parser->parse($text));
    }

    /**
     * Returns the current month-day.
     *
     * @param TimeZone $timeZone
     *
     * @return MonthDay
     */
    public static function now(TimeZone $timeZone)
    {
        $date = LocalDate::now($timeZone);

        return new MonthDay($date->getMonth(), $date->getDay());
    }

    /**
     * Returns the month-of-year.
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Returns the day-of-month.
     *
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Returns -1 if this date is before the given date, 1 if after, 0 if the dates are equal.
     *
     * @param MonthDay $that
     *
     * @return integer [-1,0,1] If this date is before, on, or after the given date.
     */
    public function compareTo(MonthDay $that)
    {
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
     * Returns whether this month-day is equal to the specified month-day.
     *
     * @param MonthDay $that
     *
     * @return boolean
     */
    public function isEqualTo(MonthDay $that)
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this month-day is before the specified month-day.
     *
     * @param MonthDay $that
     *
     * @return boolean
     */
    public function isBefore(MonthDay $that)
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns whether this month-day is after the specified month-day.
     *
     * @param MonthDay $that
     *
     * @return boolean
     */
    public function isAfter(MonthDay $that)
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Returns whether the given year is valid for this month-day.
     *
     * This method checks whether this month and day and the input year form a valid date.
     * This can only return false for February 29th.
     *
     * @param integer $year
     *
     * @return boolean
     */
    public function isValidYear($year)
    {
        return $this->month !== 2 || $this->day !== 29 || Field\Year::isLeap($year);
    }

    /**
     * Returns a copy of this MonthDay with the month-of-year altered.
     *
     * If the day-of-month is invalid for the specified month, the day will
     * be adjusted to the last valid day-of-month.
     *
     * @param integer $month
     *
     * @return MonthDay
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function withMonth($month)
    {
        $month = Cast::toInteger($month);

        if ($month === $this->month) {
            return $this;
        }

        Field\MonthOfYear::check($month);

        $lastDay = Field\MonthOfYear::getLength($month);

        return new MonthDay($month, ($lastDay < $this->day) ? $lastDay : $this->day);
    }

    /**
     * Returns a copy of this MonthDay with the day-of-month altered.
     *
     * If the day-of-month is invalid for the month, an exception is thrown.
     *
     * @param integer $day
     *
     * @return MonthDay
     *
     * @throws DateTimeException If the day-of-month is invalid for the month.
     */
    public function withDay($day)
    {
        $day = Cast::toInteger($day);

        if ($day === $this->day) {
            return $this;
        }

        Field\DayOfMonth::check($day, $this->month);

        return new MonthDay($this->month, $day);
    }

    /**
     * Combines this month-day with a year to create a LocalDate.
     *
     * This returns a LocalDate formed from this month-day and the specified year.
     *
     * A month-day of February 29th will be adjusted to February 28th
     * in the resulting date if the year is not a leap year.
     *
     * @param integer $year
     *
     * @return LocalDate
     *
     * @throws DateTimeException If the year is invalid.
     */
    public function atYear($year)
    {
        return LocalDate::of($year, $this->month, $this->isValidYear($year) ? $this->day : 28);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('--%02d-%02d', $this->month, $this->day);
    }
}
