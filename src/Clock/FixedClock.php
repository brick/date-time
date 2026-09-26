<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use DateTimeImmutable;
use Override;

/**
 * This clock always returns the same instant. It is typically used for testing.
 */
final class FixedClock implements Clock
{
    private readonly TimeZone $timeZone;

    /**
     * @param Instant       $instant  The time to set the clock at.
     * @param TimeZone|null $timeZone The time zone of the dates returned by now(), defaults to UTC.
     */
    public function __construct(
        private Instant $instant,
        ?TimeZone $timeZone = null,
    ) {
        $this->timeZone = $timeZone ?? TimeZone::utc();
    }

    #[Override]
    public function getTime(): Instant
    {
        return $this->instant;
    }

    #[Override]
    public function now(): DateTimeImmutable
    {
        return $this->instant->atTimeZone($this->timeZone)->toNativeDateTimeImmutable();
    }

    public function setTime(Instant $instant): void
    {
        $this->instant = $instant;
    }

    /**
     * Moves the clock by a number of seconds and/or nanos.
     */
    public function move(int $seconds, int $nanos = 0): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->instant = $this->instant->plus($duration);
    }
}
