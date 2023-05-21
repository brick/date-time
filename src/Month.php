<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a month-of-year such as January.
 */
enum Month : int implements JsonSerializable
{
    case JANUARY = 1;
    case FEBRUARY = 2;
    case MARCH = 3;
    case APRIL = 4;
    case MAY = 5;
    case JUNE = 6;
    case JULY = 7;
    case AUGUST = 8;
    case SEPTEMBER = 9;
    case OCTOBER = 10;
    case NOVEMBER = 11;
    case DECEMBER = 12;

    /**
     * Returns an instance of Month for the given month value.
     *
     * This method accepts Month instances in addition to integers, for backward compatibility with v0.5 constants
     * that are now enum instances.
     *
     * @deprecated Use Month::from() to get a Month instance from its integer value.
     *
     * @param int|Month $value The month number, from 1 (January) to 12 (December).
     *
     * @return Month The Month instance.
     *
     * @throws DateTimeException
     */
    public static function of(Month|int $value): Month
    {
        if ($value instanceof Month) {
            return $value;
        }

        Field\MonthOfYear::check($value);

        return Month::from($value);
    }

    /**
     * Returns the twelve months of the year in an array.
     *
     * @deprecated Use Month::cases() instead.
     *
     * @return Month[]
     */
    public static function getAll(): array
    {
        return Month::cases();
    }

    /**
     * Returns the ISO-8601 month number.
     *
     * @deprecated Use Month::$value instead.
     *
     * @return int The month number, from 1 (January) to 12 (December).
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Checks if this month matches the given month number.
     *
     * This method accepts Month instances in addition to integers, for backward compatibility with v0.5 constants
     * that are now enum instances.
     *
     * @deprecated Use === instead for strict equality between Month instances,
     *             or $monthEnum->value === $month for equality with a month-of-year integer value.
     *
     * @param int|Month $month The month number to test against.
     *
     * @return bool True if this month is equal to the given value, false otherwise.
     */
    public function is(Month|int $month): bool
    {
        if ($month instanceof Month) {
            return $this === $month;
        }

        return $this->value === $month;
    }

    /**
     * Returns whether this Month equals another Month.
     *
     * @deprecated Use strict equality between Month instances instead.
     */
    public function isEqualTo(Month $that): bool
    {
        return $this === $that;
    }

    /**
     * Returns the minimum length of this month in days.
     *
     * @return int The minimum length of this month in days, from 28 to 31.
     */
    public function getMinLength(): int
    {
        return match ($this) {
            Month::FEBRUARY => 28,
            Month::APRIL, Month::JUNE, Month::SEPTEMBER, Month::NOVEMBER => 30,
            default => 31,
        };
    }

    /**
     * Returns the maximum length of this month in days.
     *
     * @return int The maximum length of this month in days, from 29 to 31.
     */
    public function getMaxLength(): int
    {
        return match ($this) {
            Month::FEBRUARY => 29,
            Month::APRIL, Month::JUNE, Month::SEPTEMBER, Month::NOVEMBER => 30,
            default => 31,
        };
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

        return match ($this) {
            Month::JANUARY => 1,
            Month::FEBRUARY => 32,
            Month::MARCH => 60 + $leap,
            Month::APRIL => 91 + $leap,
            Month::MAY => 121 + $leap,
            Month::JUNE => 152 + $leap,
            Month::JULY => 182 + $leap,
            Month::AUGUST => 213 + $leap,
            Month::SEPTEMBER => 244 + $leap,
            Month::OCTOBER => 274 + $leap,
            Month::NOVEMBER => 305 + $leap,
            Month::DECEMBER => 335 + $leap,
        };
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
        return match ($this) {
            Month::FEBRUARY => $leapYear ? 29 : 28,
            Month::APRIL, Month::JUNE, Month::SEPTEMBER, Month::NOVEMBER => 30,
            default => 31,
        };
    }

    /**
     * Returns the month that is the specified number of months after this one.
     *
     * The calculation rolls around the end of the year from December to January.
     * The specified period may be negative.
     */
    public function plus(int $months): Month
    {
        return Month::from((((($this->value - 1 + $months) % 12) + 12) % 12) + 1);
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
     * Serializes as a string using {@see Month::toString()}.
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Returns the capitalized English name of this Month.
     */
    public function toString(): string
    {
        return match ($this) {
            Month::JANUARY => 'January',
            Month::FEBRUARY => 'February',
            Month::MARCH => 'March',
            Month::APRIL => 'April',
            Month::MAY => 'May',
            Month::JUNE => 'June',
            Month::JULY => 'July',
            Month::AUGUST => 'August',
            Month::SEPTEMBER => 'September',
            Month::OCTOBER => 'October',
            Month::NOVEMBER => 'November',
            Month::DECEMBER => 'December',
        };
    }
}
