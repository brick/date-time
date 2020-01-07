<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * A day-of-week, such as Tuesday.
 *
 * This class is immutable.
 */
class DayOfWeek implements \JsonSerializable
{
    public const MONDAY    = 1;
    public const TUESDAY   = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY  = 4;
    public const FRIDAY    = 5;
    public const SATURDAY  = 6;
    public const SUNDAY    = 7;

    /**
     * The ISO-8601 value for the day of the week, from 1 (Monday) to 7 (Sunday).
     *
     * @var int
     */
    private $value;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param int $value The day-of-week value, validated from 1 to 7.
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * Returns a cached DayOfWeek instance.
     *
     * @param int $value The day-of-week value, validated from 1 to 7.
     *
     * @return DayOfWeek
     */
    private static function get(int $value) : DayOfWeek
    {
        static $values;

        if (! isset($values[$value])) {
            $values[$value] = new DayOfWeek($value);
        }

        return $values[$value];
    }

    /**
     * Returns an instance of DayOfWeek for the given day-of-week value.
     *
     * @param int $dayOfWeek The day-of-week value, from 1 (Monday) to 7 (Sunday).
     *
     * @return DayOfWeek The DayOfWeek instance.
     *
     * @throws DateTimeException If the day-of-week is not valid.
     */
    public static function of(int $dayOfWeek) : DayOfWeek
    {
        Field\DayOfWeek::check($dayOfWeek);

        return DayOfWeek::get($dayOfWeek);
    }

    /**
     * Returns the current day-of-week in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     *
     * @param TimeZone   $timeZone
     * @param Clock|null $clock
     *
     * @return DayOfWeek
     */
    public static function now(TimeZone $timeZone, Clock $clock = null) : DayOfWeek
    {
        return LocalDate::now($timeZone, $clock)->getDayOfWeek();
    }

    /**
     * Returns the seven days of the week in an array.
     *
     * @param DayOfWeek|null $first The day to return first. Optional, defaults to Monday.
     *
     * @return DayOfWeek[]
     */
    public static function all(DayOfWeek $first = null) : array
    {
        $days = [];
        $first = $first ?: DayOfWeek::get(DayOfWeek::MONDAY);
        $current = $first;

        do {
            $days[] = $current;
            $current = $current->plus(1);
        }
        while (! $current->isEqualTo($first));

        return $days;
    }

    /**
     * Returns a day-of-week instance for Monday.
     *
     * @return DayOfWeek
     */
    public static function monday()
    {
        return DayOfWeek::get(DayOfWeek::MONDAY);
    }

    /**
     * Returns a day-of-week instance for Tuesday.
     *
     * @return DayOfWeek
     */
    public static function tuesday()
    {
        return DayOfWeek::get(DayOfWeek::TUESDAY);
    }

    /**
     * Returns a day-of-week instance for Wednesday.
     *
     * @return DayOfWeek
     */
    public static function wednesday()
    {
        return DayOfWeek::get(DayOfWeek::WEDNESDAY);
    }

    /**
     * Returns a day-of-week instance for Thursday.
     *
     * @return DayOfWeek
     */
    public static function thursday()
    {
        return DayOfWeek::get(DayOfWeek::THURSDAY);
    }

    /**
     * Returns a day-of-week instance for Friday.
     *
     * @return DayOfWeek
     */
    public static function friday()
    {
        return DayOfWeek::get(DayOfWeek::FRIDAY);
    }

    /**
     * Returns a day-of-week instance for Saturday.
     *
     * @return DayOfWeek
     */
    public static function saturday()
    {
        return DayOfWeek::get(DayOfWeek::SATURDAY);
    }

    /**
     * Returns a day-of-week instance for Sunday.
     *
     * @return DayOfWeek
     */
    public static function sunday()
    {
        return DayOfWeek::get(DayOfWeek::SUNDAY);
    }

    /**
     * Returns the ISO 8601 value of this DayOfWeek.
     *
     * @return int The day-of-week value, from 1 (Monday) to 7 (Sunday).
     */
    public function getValue() : int
    {
        return $this->value;
    }

    /**
     * Checks if this day-of-week matches the given day-of-week value.
     *
     * @param int $dayOfWeek The day-of-week value to test against.
     *
     * @return bool True if this day-of-week is equal to the given value, false otherwise.
     */
    public function is(int $dayOfWeek) : bool
    {
        return $this->value === $dayOfWeek;
    }

    /**
     * Returns whether this DayOfWeek equals another DayOfWeek.
     *
     * Even though of() returns the same instance if the same day is requested several times,
     * do *not* use strict object comparison to compare two DayOfWeek instances,
     * as it is possible to get a different instance for the same day using serialization.
     *
     * @param DayOfWeek $that
     *
     * @return bool
     */
    public function isEqualTo(DayOfWeek $that) : bool
    {
        return $this->value === $that->value;
    }

    /**
     * Returns the DayOfWeek that is the specified number of days after this one.
     *
     * @param int $days
     *
     * @return DayOfWeek
     */
    public function plus(int $days) : DayOfWeek
    {
        return DayOfWeek::get((((($this->value - 1 + $days) % 7) + 7) % 7) + 1);
    }

    /**
     * Returns the DayOfWeek that is the specified number of days before this one.
     *
     * @param int $days
     *
     * @return DayOfWeek
     */
    public function minus(int $days) : DayOfWeek
    {
        return $this->plus(- $days);
    }

    /**
     * Serializes as a string using {@see DayOfWeek::__toString()}.
     *
     * @return string
     */
    public function jsonSerialize() : string
    {
        return (string) $this;
    }

    /**
     * Returns the capitalized English name of this day-of-week.
     *
     * @return string
     */
    public function __toString()
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ][$this->value];
    }
}
