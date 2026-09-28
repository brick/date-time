<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * A source of the current instant, independent of any time zone.
 *
 * To get the current date or time in a time zone, or a PSR-20 clock, call withTimeZone().
 */
interface Clock
{
    /**
     * Returns the current time.
     */
    public function getTime(): Instant;

    /**
     * Returns a clock that reads the current time from this clock, in the given time zone.
     */
    public function withTimeZone(TimeZone $timeZone): ZonedClock;
}
