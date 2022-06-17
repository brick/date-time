<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

use function assert;
use function is_int;
use function rtrim;
use function str_pad;

use const PHP_INT_MAX;
use const PHP_INT_MIN;
use const STR_PAD_LEFT;

/**
 * Represents a point in time, with a nanosecond precision.
 *
 * Instant represents the computer view of the timeline. It unambiguously represents a point in time,
 * without any calendar concept of date, time or time zone. It is not very meaningful to humans,
 * but can be converted to a `ZonedDateTime` by providing a time zone.
 */
final class Instant implements JsonSerializable
{
    /**
     * The number of seconds since the epoch of 1970-01-01T00:00:00Z.
     */
    private int $epochSecond;

    /**
     * The nanoseconds adjustment to the epoch second, in the range 0 to 999,999,999.
     */
    private int $nano;

    /**
     * Private constructor. Use of() to obtain an Instant.
     *
     * @param int $epochSecond The epoch second.
     * @param int $nano        The nanosecond adjustment, validated in the range 0 to 999,999,999.
     */
    private function __construct(int $epochSecond, int $nano)
    {
        $this->epochSecond = $epochSecond;
        $this->nano = $nano;
    }

    /**
     * Returns an Instant representing a number of seconds and an adjustment in nanoseconds.
     *
     * This method allows an arbitrary number of nanoseconds to be passed in.
     * The factory will alter the values of the second and nanosecond in order
     * to ensure that the stored nanosecond is in the range 0 to 999,999,999.
     * For example, the following will result in the exactly the same duration:
     *
     * * Instant::of(3, 1);
     * * Duration::of(4, -999999999);
     * * Duration::of(2, 1000000001);
     *
     * @param int $epochSecond    The number of seconds since the UNIX epoch of 1970-01-01T00:00:00Z.
     * @param int $nanoAdjustment The adjustment to the epoch second in nanoseconds.
     */
    public static function of(int $epochSecond, int $nanoAdjustment = 0): Instant
    {
        $nanos = $nanoAdjustment % LocalTime::NANOS_PER_SECOND;
        $epochSecond += ($nanoAdjustment - $nanos) / LocalTime::NANOS_PER_SECOND;
        assert(is_int($epochSecond));

        if ($nanos < 0) {
            $nanos += LocalTime::NANOS_PER_SECOND;
            $epochSecond--;
        }

        return new Instant($epochSecond, $nanos);
    }

    public static function epoch(): Instant
    {
        return new Instant(0, 0);
    }

    public static function now(?Clock $clock = null): Instant
    {
        if ($clock === null) {
            $clock = DefaultClock::get();
        }

        return $clock->getTime();
    }

    /**
     * Returns the minimum supported instant.
     *
     * This could be used by an application as a "far past" instant.
     */
    public static function min(): Instant
    {
        return new Instant(PHP_INT_MIN, 0);
    }

    /**
     * Returns the maximum supported instant.
     *
     * This could be used by an application as a "far future" instant.
     */
    public static function max(): Instant
    {
        return new Instant(PHP_INT_MAX, 999999999);
    }

    public function plus(Duration $duration): Instant
    {
        if ($duration->isZero()) {
            return $this;
        }

        $seconds = $this->epochSecond + $duration->getSeconds();
        $nanos = $this->nano + $duration->getNanos();

        return Instant::of($seconds, $nanos);
    }

    public function minus(Duration $duration): Instant
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->plus($duration->negated());
    }

    public function plusSeconds(int $seconds): Instant
    {
        if ($seconds === 0) {
            return $this;
        }

        return new Instant($this->epochSecond + $seconds, $this->nano);
    }

    public function minusSeconds(int $seconds): Instant
    {
        return $this->plusSeconds(-$seconds);
    }

    public function plusMinutes(int $minutes): Instant
    {
        return $this->plusSeconds($minutes * LocalTime::SECONDS_PER_MINUTE);
    }

    public function minusMinutes(int $minutes): Instant
    {
        return $this->plusMinutes(-$minutes);
    }

    public function plusHours(int $hours): Instant
    {
        return $this->plusSeconds($hours * LocalTime::SECONDS_PER_HOUR);
    }

    public function minusHours(int $hours): Instant
    {
        return $this->plusHours(-$hours);
    }

    public function plusDays(int $days): Instant
    {
        return $this->plusSeconds($days * LocalTime::SECONDS_PER_DAY);
    }

    /**
     * Returns a copy of this Instant with the epoch second altered.
     */
    public function withEpochSecond(int $epochSecond): Instant
    {
        if ($epochSecond === $this->epochSecond) {
            return $this;
        }

        return new Instant($epochSecond, $this->nano);
    }

    /**
     * Returns a copy of this Instant with the nano-of-second altered.
     *
     * @throws DateTimeException If the nano-of-second if not valid.
     */
    public function withNano(int $nano): Instant
    {
        if ($nano === $this->nano) {
            return $this;
        }

        Field\NanoOfSecond::check($nano);

        return new Instant($this->epochSecond, $nano);
    }

    public function minusDays(int $days): Instant
    {
        return $this->plusDays(-$days);
    }

    public function getEpochSecond(): int
    {
        return $this->epochSecond;
    }

    public function getNano(): int
    {
        return $this->nano;
    }

    /**
     * Compares this instant with another.
     *
     * @return int [-1,0,1] If this instant is before, on, or after the given instant.
     */
    public function compareTo(Instant $that): int
    {
        $seconds = $this->getEpochSecond() - $that->getEpochSecond();

        if ($seconds !== 0) {
            return $seconds > 0 ? 1 : -1;
        }

        $nanos = $this->getNano() - $that->getNano();

        if ($nanos !== 0) {
            return $nanos > 0 ? 1 : -1;
        }

        return 0;
    }

    /**
     * Returns whether this instant equals another.
     */
    public function isEqualTo(Instant $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this instant is after another.
     */
    public function isAfter(Instant $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Returns whether this instant is after or equal to another.
     */
    public function isAfterOrEqualTo(Instant $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns whether this instant is before another.
     */
    public function isBefore(Instant $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns whether this instant is before or equal to another.
     */
    public function isBeforeOrEqualTo(Instant $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isBetweenInclusive(Instant $from, Instant $to): bool
    {
        return $this->isAfterOrEqualTo($from) && $this->isBeforeOrEqualTo($to);
    }

    public function isBetweenExclusive(Instant $from, Instant $to): bool
    {
        return $this->isAfter($from) && $this->isBefore($to);
    }

    /**
     * Returns whether this instant is in the future, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public function isFuture(?Clock $clock = null): bool
    {
        return $this->isAfter(Instant::now($clock));
    }

    /**
     * Returns whether this instant is in the past, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public function isPast(?Clock $clock = null): bool
    {
        return $this->isBefore(Instant::now($clock));
    }

    /**
     * Returns a ZonedDateTime formed from this instant and the specified time-zone.
     */
    public function atTimeZone(TimeZone $timeZone): ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this, $timeZone);
    }

    /**
     * Returns a decimal representation of the timestamp represented by this instant.
     *
     * The output does not have trailing decimal zeros.
     *
     * Examples: `123456789`, `123456789.5`, `123456789.000000001`.
     */
    public function toDecimal(): string
    {
        $result = (string) $this->epochSecond;

        if ($this->nano !== 0) {
            $nano = (string) $this->nano;
            $nano = str_pad($nano, 9, '0', STR_PAD_LEFT);
            $nano = rtrim($nano, '0');

            $result .= '.' . $nano;
        }

        return $result;
    }

    /**
     * Serializes as a string using {@see Instant::__toString()}.
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return (string) ZonedDateTime::ofInstant($this, TimeZone::utc());
    }
}
