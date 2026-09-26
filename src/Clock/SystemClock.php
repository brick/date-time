<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;
use DateTimeImmutable;

/**
 * This clock returns the system time. It is the default clock.
 *
 * This clock has a microsecond precision on most systems.
 */
final class SystemClock implements Clock
{
    private readonly TimeZone $timeZone;

    /**
     * @param TimeZone|null $timeZone The time zone of the dates returned by now(), defaults to UTC.
     */
    public function __construct(?TimeZone $timeZone = null)
    {
        $this->timeZone = $timeZone ?? TimeZone::utc();
    }

    #[\Override]
    public function getTime(): Instant
    {
        [$fraction, $epochSecond] = \explode(' ', microtime());

        $epochSecond = (int) $epochSecond;
        $nanoAdjustment = 10 * (int) \substr($fraction, 2, 8);

        return Instant::of($epochSecond, $nanoAdjustment);
    }

    #[\Override]
    public function now(): DateTimeImmutable
    {
        return $this->getTime()->atTimeZone($this->timeZone)->toNativeDateTimeImmutable();
    }
}
