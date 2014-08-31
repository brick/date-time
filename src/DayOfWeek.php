<?php

namespace Brick\DateTime;

use Brick\DateTime\Utility\Cast;

/**
 * A day-of-week, such as Tuesday.
 *
 * This class is immutable.
 */
class DayOfWeek
{
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;
    const SUNDAY    = 7;

    /**
     * The ISO-8601 value for the day of the week, from 1 (Monday) to 7 (Sunday).
     *
     * @var integer
     */
    private $dayOfWeek;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param integer $dayOfWeek The day-of-week value, validated as an integer from 1 to 7.
     */
    private function __construct($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * Returns a cached DayOfWeek instance.
     *
     * @param integer $value The day-of-week value, validated as an integer from 1 to 7.
     *
     * @return DayOfWeek
     */
    private static function get($value)
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
     * @param integer $dayOfWeek The day-of-week value, from 1 (Monday) to 7 (Sunday).
     *
     * @return DayOfWeek The DayOfWeek instance.
     *
     * @throws \InvalidArgumentException
     */
    public static function of($dayOfWeek)
    {
        $dayOfWeek = Cast::toInteger($dayOfWeek);

        Field\DayOfWeek::check($dayOfWeek);

        return DayOfWeek::get($dayOfWeek);
    }

    /**
     * @param TimeZone $timeZone
     *
     * @return DayOfWeek
     */
    public static function now(TimeZone $timeZone)
    {
        return LocalDate::now($timeZone)->getDayOfWeek();
    }

    /**
     * Returns the seven days of the week in an array.
     *
     * @param DayOfWeek $first The day to return first. Optional, defaults to Monday.
     *
     * @return DayOfWeek[]
     */
    public static function getAll(DayOfWeek $first = null)
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
     * Returns the ISO 8601 value of this DayOfWeek.
     *
     * @return integer The day-of-week value, from 1 (Monday) to 7 (Sunday).
     */
    public function getValue()
    {
        return $this->dayOfWeek;
    }

    /**
     * Checks if this day-of-week matches the given day-of-week value.
     *
     * @param integer $dayOfWeek The day-of-week value to test against.
     *
     * @return boolean True if this day-of-week is equal to the given value, false otherwise.
     */
    public function is($dayOfWeek)
    {
        return $this->dayOfWeek == $dayOfWeek;
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
     * @return boolean
     */
    public function isEqualTo(DayOfWeek $that)
    {
        return $this->dayOfWeek === $that->dayOfWeek;
    }

    /**
     * Returns the DayOfWeek that is the specified number of days after this one.
     *
     * @param integer $days
     *
     * @return DayOfWeek
     */
    public function plus($days)
    {
        $days = Cast::toInteger($days);

        return DayOfWeek::get((((($this->dayOfWeek - 1 + $days) % 7) + 7) % 7) + 1);
    }

    /**
     * Returns the DayOfWeek that is the specified number of days before this one.
     *
     * @param integer $days
     *
     * @return DayOfWeek
     */
    public function minus($days)
    {
        $days = Cast::toInteger($days);

        return $this->plus(- $days);
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
        ][$this->dayOfWeek];
    }
}
