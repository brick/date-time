<?php

declare(strict_types=1);

namespace Brick\DateTime;

use DateTimeImmutable;
use Override;
use Psr\Clock\ClockInterface;

/**
 * A clock bound to a time zone.
 *
 * This clock reads the current time from an underlying clock, and provides the current date and time in its time zone.
 * It also implements PSR-20, returning the current date and time in its time zone.
 *
 * To fake the current time in tests, wrap a FixedClock: there is no need to fake this class.
 */
final readonly class ZonedClock implements Clock, ClockInterface
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

    /**
     * Returns the current date and time in this clock's time zone, as a native DateTimeImmutable.
     *
     * Native dates have microsecond precision: nanoseconds are rounded down.
     */
    #[Override]
    public function now(): DateTimeImmutable
    {
        return $this->getCurrentZonedDateTime()->toNativeDateTimeImmutable();
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
