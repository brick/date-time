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
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Returns the capitalized English name of this day-of-week.
     *
     * @psalm-return non-empty-string
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
