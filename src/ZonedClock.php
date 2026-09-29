<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Override;

/**
 * A clock bound to a time zone.
 *
 * This clock reads the current time from an underlying clock, and provides the current date and time in its time zone.
 *
 * To fake the current time in tests, wrap a FixedClock: there is no need to fake this class.
 */
final readonly class ZonedClock implements Clock
{
    /**
     * @param Clock    $clock    The underlying clock.
     * @param TimeZone $timeZone The time zone.
     */
    public function __construct(
        private Clock $clock,
        private TimeZone $timeZone,
    ) {
    }

    /**
     * Returns the underlying clock.
     */
    public function getClock(): Clock
    {
        return $this->clock;
    }

    public function getTimeZone(): TimeZone
    {
        return $this->timeZone;
    }

    #[Override]
    public function getTime(): Instant
    {
        return $this->clock->getTime();
    }

    /**
     * Returns a copy of this clock with a different time zone, and the same underlying clock.
     */
    #[Override]
    public function withTimeZone(TimeZone $timeZone): ZonedClock
    {
        return new ZonedClock($this->clock, $timeZone);
    }

    public function getCurrentZonedDateTime(): ZonedDateTime
    {
        return $this->clock->getTime()->atTimeZone($this->timeZone);
    }

    public function getCurrentLocalDateTime(): LocalDateTime
    {
        return $this->getCurrentZonedDateTime()->getDateTime();
    }

    public function getCurrentLocalDate(): LocalDate
    {
        return $this->getCurrentZonedDateTime()->getDate();
    }

    public function getCurrentLocalTime(): LocalTime
    {
        return $this->getCurrentZonedDateTime()->getTime();
    }
}
