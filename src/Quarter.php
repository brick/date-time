<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a quarter of the year.
 */
enum Quarter: int implements JsonSerializable
{
    /**
     * January 1 to March 31.
     */
    case Q1 = 1;

    /**
     * April 1 to June 30.
     */
    case Q2 = 2;

    /**
     * July 1 to September 30.
     */
    case Q3 = 3;

    /**
     * October 1 to December 31.
     */
    case Q4 = 4;

    /**
     * Returns the current quarter in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): self
    {
        return LocalDate::now($timeZone, $clock)->getQuarter();
    }

    /**
     * Serializes as an integer.
     */
    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
