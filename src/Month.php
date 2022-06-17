<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a month-of-year such as January.
 */
final class Month implements JsonSerializable
{
    public const JANUARY = 1;
    public const FEBRUARY = 2;
    public const MARCH = 3;
    public const APRIL = 4;
    public const MAY = 5;
    public const JUNE = 6;
    public const JULY = 7;
    public const AUGUST = 8;
    public const SEPTEMBER = 9;
    public const OCTOBER = 10;
    public const NOVEMBER = 11;
    public const DECEMBER = 12;

    /**
     * The month number, from 1 (January) to 12 (December).
     */
    private int $month;

    /**
     * Private constructor. Use of() to get a Month instance.
     *
     * @param int $month The month value, validated from 1 to 12.
     */
    private function __construct(int $month)
    {
        $this->month = $month;
    }

    /**
     * Returns an instance of Month for the given month value.
     *
     * @param int $value The month number, from 1 (January) to 12 (December).
     *
     * @return Month The Month instance.
     *
     * @throws DateTimeException
     */
    public static function of(int $value): Month
    {
        Field\MonthOfYear::check($value);

        return Month::get($value);
    }

    /**
     * Returns the twelve months of the year in an array.
     *
     * @return Month[]
     */
    public static function getAll(): array
    {
        $months = [];

        for ($month = Month::JANUARY; $month <= Month::DECEMBER; $month++) {
            $months[] = Month::get($month);
        }

        return $months;
    }

    /**
     * Returns the ISO-8601 month number.
     *
     * @return int The month number, from 1 (January) to 12 (December).
     */
    public function getValue(): int
    {
        return $this->month;
    }

    /**
     * Checks if this month matches the given month number.
     *
     * @param int $month The month number to test against.
     *
     * @return bool True if this month is equal to the given value, false otherwise.
     */
    public function is(int $month): bool
    {
        return $this->month === $month;
    }

    /**
     * Returns whether this Month equals another Month.
     */
    public function isEqualTo(Month $that): bool
    {
        return $this->month === $that->month;
    }

    /**
     * Returns the minimum length of this month in days.
     *
     * @return int The minimum length of this month in days, from 28 to 31.
     */
    public function getMinLength(): int
    {
        switch ($this->month) {
            case Month::FEBRUARY:
                return 28;
            case Month::APRIL:
            case Month::JUNE:
            case Month::SEPTEMBER:
            case Month::NOVEMBER:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the maximum length of this month in days.
     *
     * @return int The maximum length of this month in days, from 29 to 31.
     */
    public function getMaxLength(): int
    {
        switch ($this->month) {
            case Month::FEBRUARY:
                return 29;
            case Month::APRIL:
            case Month::JUNE:
            case Month::SEPTEMBER:
            case Month::NOVEMBER:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the day-of-year for the first day of this month.
     *
     * This returns the day-of-year that this month begins on, using the leap
     * year flag to determine the length of February.
     */
    public function getFirstDayOfYear(bool $leapYear): int
    {
        $leap = $leapYear ? 1 : 0;

        switch ($this->month) {
            case Month::JANUARY:
                return 1;
            case Month::FEBRUARY:
                return 32;
            case Month::MARCH:
                return 60 + $leap;
            case Month::APRIL:
                return 91 + $leap;
            case Month::MAY:
                return 121 + $leap;
            case Month::JUNE:
                return 152 + $leap;
            case Month::JULY:
                return 182 + $leap;
            case Month::AUGUST:
                return 213 + $leap;
            case Month::SEPTEMBER:
                return 244 + $leap;
            case Month::OCTOBER:
                return 274 + $leap;
            case Month::NOVEMBER:
                return 305 + $leap;
        }

        return 335 + $leap;
    }

    /**
     * Returns the length of this month in days.
     *
     * This takes a flag to determine whether to return the length for a leap year or not.
     *
     * February has 28 days in a standard year and 29 days in a leap year.
     * April, June, September and November have 30 days.
     * All other months have 31 days.
     */
    public function getLength(bool $leapYear): int
    {
        switch ($this->month) {
            case Month::FEBRUARY:
                return $leapYear ? 29 : 28;
            case Month::APRIL:
            case Month::JUNE:
            case Month::SEPTEMBER:
            case Month::NOVEMBER:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the month that is the specified number of months after this one.
     *
     * The calculation rolls around the end of the year from December to January.
     * The specified period may be negative.
     */
    public function plus(int $months): Month
    {
        return Month::get((((($this->month - 1 + $months) % 12) + 12) % 12) + 1);
    }

    /**
     * Returns the month that is the specified number of months before this one.
     *
     * The calculation rolls around the start of the year from January to December.
     * The specified period may be negative.
     */
    public function minus(int $months): Month
    {
        return $this->plus(-$months);
    }

    /**
     * Serializes as a string using {@see Month::__toString()}.
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    /**
     * Returns the capitalized English name of this Month.
     */
    public function __toString(): string
    {
        return [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ][$this->month];
    }

    /**
     * Returns a cached Month instance.
     *
     * @param int $value The month value, validated from 1 to 12.
     *
     * @return Month The cached Month instance.
     */
    private static function get(int $value): Month
    {
        /** @var array<int, Month> $values */
        static $values = [];

        if (! isset($values[$value])) {
            $values[$value] = new Month($value);
        }

        return $values[$value];
    }
}
