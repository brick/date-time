<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a quarter of the year.
 */
enum Quarter: int implements JsonSerializable
{
    case FIRST = 1;
    case SECOND = 2;
    case THIRD = 3;
    case FOURTH = 4;

    /**
     * Returns the current day-of-week in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): self
    {
        return LocalDate::now($timeZone, $clock)->getQuarter();
    }

    /**
     * Serializes as an integer using {@see DayOfWeek::toInteger()}.
     */
    public function jsonSerialize(): int
    {
        return $this->toInteger();
    }

    /**
     * Returns the number of the quarter {1,4}.
     */
    public function toInteger(): int
    {
        return $this->value;
    }
}
