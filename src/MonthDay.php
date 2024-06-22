<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use JsonSerializable;
use Stringable;

use function is_int;

/**
 * A month-day in the ISO-8601 calendar system, such as `--12-03`.
 */
final class MonthDay implements JsonSerializable, Stringable
{
    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param int<1, 12> $month The month-of-year.
     * @param int<1, 31> $day   The day-of-month, valid for this month.
     */
    private function __construct(
        private readonly int $month,
        private readonly int $day,
    ) {
    }

    /**
     * Obtains an instance of MonthDay.
     *
     * @param int|Month $month The month-of-year, from 1 (January) to 12 (December).
     * @param int       $day   The day-of-month, from 1 to 31.
     *
     * @throws DateTimeException If the month-day is not valid.
     */
    public static function of(int|Month $month, int $day): MonthDay
    {
        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        } else {
            $month = $month->value;
        }

        Field\DayOfMonth::check($day, $month);

        return new MonthDay($month, $day);
    }

    /**
     * @throws DateTimeException      If the month-day is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): MonthDay
    {
        return MonthDay::of(
            (int) $result->getField(Field\MonthOfYear::NAME),
            (int) $result->getField(Field\DayOfMonth::NAME),
        );
    }

    /**
     * Obtains an instance of `LocalDate` from a text string.
     *
     * @param string              $text   The text to parse, such as `--12-03`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): MonthDay
    {
        if ($parser === null) {
            $parser = IsoParsers::monthDay();
        }

        return MonthDay::from($parser->parse($text));
    }

    /**
     * Returns the current month-day in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): MonthDay
    {
        $date = LocalDate::now($timeZone, $clock);

        return new MonthDay($date->getMonthValue(), $date->getDayOfMonth());
    }

    /**
     * Returns the month-of-year as a Month enum.
     */
    public function getMonth(): Month
    {
        return Month::from($this->month);
    }

    /**
     * Returns the month-of-year value from 1 to 12.
     *
     * @return int<1, 12>
     */
    public function getMonthValue(): int
    {
        return $this->month;
    }

    /**
     * Returns the day-of-month.
     *
     * @return int<1, 31>
     */
    public function getDayOfMonth(): int
    {
        return $this->day;
    }

    /**
     * @return -1|0|1 If this date is before, on, or after the given date.
     */
    public function compareTo(MonthDay $that): int
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
     */
    public function isEqualTo(MonthDay $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this month-day is before the specified month-day.
     */
    public function isBefore(MonthDay $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns whether this month-day is after the specified month-day.
     */
    public function isAfter(MonthDay $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Returns whether the given year is valid for this month-day.
     *
     * This method checks whether this month and day and the input year form a valid date.
     * This can only return false for February 29th.
     */
    public function isValidYear(int $year): bool
    {
        return $this->month !== 2 || $this->day !== 29 || Field\Year::isLeap($year);
    }

    /**
     * Returns a copy of this MonthDay with the month-of-year altered.
     *
     * If the day-of-month is invalid for the specified month, the day will
     * be adjusted to the last valid day-of-month.
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function withMonth(int|Month $month): MonthDay
    {
        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        } else {
            $month = $month->value;
        }

        if ($month === $this->month) {
            return $this;
        }

        $lastDay = Field\MonthOfYear::getLength($month);

        return new MonthDay($month, ($lastDay < $this->day) ? $lastDay : $this->day);
    }

    /**
     * Returns a copy of this MonthDay with the day-of-month altered.
     *
     * If the day-of-month is invalid for the month, an exception is thrown.
     *
     * @throws DateTimeException If the day-of-month is invalid for the month.
     */
    public function withDay(int $day): MonthDay
    {
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
     * @throws DateTimeException If the year is invalid.
     */
    public function atYear(int $year): LocalDate
    {
        return LocalDate::of($year, $this->month, $this->isValidYear($year) ? $this->day : 28);
    }

    /**
     * Serializes as a string using {@see MonthDay::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this month-day.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        // This code is optimized for high performance
        return '--'
            . ($this->month < 10 ? '0' . $this->month : $this->month)
            . '-'
            . ($this->day < 10 ? '0' . $this->day : $this->day);
    }

    /**
     * {@see MonthDay::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
