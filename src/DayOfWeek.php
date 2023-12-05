<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a day-of-week such as Tuesday.
 */
enum DayOfWeek: int implements JsonSerializable
{
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;
    case SUNDAY = 7;

    /**
     * Returns an instance of DayOfWeek for the given day-of-week value.
     *
     * This method accepts DayOfWeek instances in addition to integers, for backward compatibility with v0.5 constants
     * that are now enum instances.
     *
     * @deprecated Use DayOfWeek::from() to get a DayOfWeek instance from its integer value.
     *
     * @param DayOfWeek|int $dayOfWeek The day-of-week value, from 1 (Monday) to 7 (Sunday).
     *
     * @return DayOfWeek The DayOfWeek instance.
     *
     * @throws DateTimeException If the day-of-week is not valid.
     */
    public static function of(DayOfWeek|int $dayOfWeek): DayOfWeek
    {
        if ($dayOfWeek instanceof DayOfWeek) {
            return $dayOfWeek;
        }

        Field\DayOfWeek::check($dayOfWeek);

        return DayOfWeek::from($dayOfWeek);
    }

    /**
     * Returns the current day-of-week in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): DayOfWeek
    {
        return LocalDate::now($timeZone, $clock)->getDayOfWeek();
    }

    /**
     * Returns the seven days of the week in an array.
     *
     * @param DayOfWeek $first The day to return first. Optional, defaults to Monday.
     *
     * @return DayOfWeek[]
     */
    public static function all(DayOfWeek $first = DayOfWeek::MONDAY): array
    {
        $days = [];
        $current = $first;

        do {
            $days[] = $current;
            $current = $current->plus(1);
        } while ($current !== $first);

        return $days;
    }

    /**
     * Returns a day-of-week instance for Monday.
     *
     * @deprecated Use DayOfWeek::MONDAY instead.
     */
    public static function monday(): DayOfWeek
    {
        return DayOfWeek::MONDAY;
    }

    /**
     * Returns a day-of-week instance for Tuesday.
     *
     * @deprecated Use DayOfWeek::TUESDAY instead.
     */
    public static function tuesday(): DayOfWeek
    {
        return DayOfWeek::TUESDAY;
    }

    /**
     * Returns a day-of-week instance for Wednesday.
     *
     * @deprecated Use DayOfWeek::WEDNESDAY instead.
     */
    public static function wednesday(): DayOfWeek
    {
        return DayOfWeek::WEDNESDAY;
    }

    /**
     * Returns a day-of-week instance for Thursday.
     *
     * @deprecated Use DayOfWeek::THURSDAY instead.
     */
    public static function thursday(): DayOfWeek
    {
        return DayOfWeek::THURSDAY;
    }

    /**
     * Returns a day-of-week instance for Friday.
     *
     * @deprecated Use DayOfWeek::FRIDAY instead.
     */
    public static function friday(): DayOfWeek
    {
        return DayOfWeek::FRIDAY;
    }

    /**
     * Returns a day-of-week instance for Saturday.
     *
     * @deprecated Use DayOfWeek::SATURDAY instead.
     */
    public static function saturday(): DayOfWeek
    {
        return DayOfWeek::SATURDAY;
    }

    /**
     * Returns a day-of-week instance for Sunday.
     *
     * @deprecated Use DayOfWeek::SUNDAY instead.
     */
    public static function sunday(): DayOfWeek
    {
        return DayOfWeek::SUNDAY;
    }

    /**
     * Returns the ISO 8601 value of this DayOfWeek.
     *
     * @deprecated Use DayOfWeek::$value instead.
     *
     * @return int The day-of-week value, from 1 (Monday) to 7 (Sunday).
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * Checks if this day-of-week matches the given day-of-week value.
     *
     * This method accepts DayOfWeek instances in addition to integers, for backward compatibility with v0.5 constants
     * that are now enum instances.
     *
     * @deprecated Use === instead for strict equality between DayOfWeek instances,
     *             or $dayOfWeekEnum->value === $dayOfWeek for equality with a day-of-week integer value.
     *
     * @param DayOfWeek|int $dayOfWeek The day-of-week value to test against.
     *
     * @return bool True if this day-of-week is equal to the given value, false otherwise.
     */
    public function is(DayOfWeek|int $dayOfWeek): bool
    {
        if ($dayOfWeek instanceof DayOfWeek) {
            return $this === $dayOfWeek;
        }

        return $this->value === $dayOfWeek;
    }

    /**
     * Returns whether this DayOfWeek equals another DayOfWeek.
     *
     * Even though of() returns the same instance if the same day is requested several times,
     * do *not* use strict object comparison to compare two DayOfWeek instances,
     * as it is possible to get a different instance for the same day using serialization.
     *
     * @deprecated Use strict equality between DayOfWeek instances instead.
     */
    public function isEqualTo(DayOfWeek $that): bool
    {
        return $this === $that;
    }

    /**
     * Returns whether this DayOfWeek is Monday to Friday.
     */
    public function isWeekday(): bool
    {
        return $this->value <= self::FRIDAY->value;
    }

    /**
     * Returns whether this DayOfWeek is Saturday or Sunday.
     */
    public function isWeekend(): bool
    {
        return $this === self::SATURDAY || $this === self::SUNDAY;
    }

    /**
     * Returns the DayOfWeek that is the specified number of days after this one.
     */
    public function plus(int $days): DayOfWeek
    {
        return DayOfWeek::from((((($this->value - 1 + $days) % 7) + 7) % 7) + 1);
    }

    /**
     * Returns the DayOfWeek that is the specified number of days before this one.
     */
    public function minus(int $days): DayOfWeek
    {
        return $this->plus(-$days);
    }

    /**
     * Serializes as a string using {@see DayOfWeek::toString()}.
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Returns the capitalized English name of this day-of-week.
     */
    public function toString(): string
    {
        return match ($this) {
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
            self::SUNDAY => 'Sunday',
        };
    }
}
