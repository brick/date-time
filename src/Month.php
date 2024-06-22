<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a month-of-year such as January.
 */
enum Month: int implements JsonSerializable
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
     * Returns the minimum length of this month in days.
     *
     * @return int<28, 31>
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
     * @return int<28, 31>
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
     *
     * @return int<1, 336>
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
     *
     * @return int<28, 31>
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
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Returns the capitalized English name of this Month.
     *
     * @psalm-return non-empty-string
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
