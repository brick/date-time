<?php

namespace Brick\DateTime;

use Brick\DateTime\Clock\Clock;

/**
 * Represents a point in time, with a nanosecond precision.
 *
 * Instant represents the computer view of the timeline. It unambiguously represents a point in time,
 * without any calendar concept of date, time or time zone. It is not very meaningful to humans,
 * but can be converted to a `ZonedDateTime` by providing a time zone.
 */
class Instant extends ReadableInstant
{
    /**
     * The number of seconds since the epoch of 1970-01-01T00:00:00Z.
     *
     * @var int
     */
    private $epochSecond;

    /**
     * The nanoseconds adjustment to the epoch second, in the range 0 to 999,999,999.
     *
     * @var int
     */
    private $nano;

    /**
     * Private constructor. Use of() to obtain an Instant.
     *
     * @param int $epochSecond The epoch second.
     * @param int $nano        The nanosecond adjustment, validated in the range 0 to 999,999,999.
     */
    private function __construct(int $epochSecond, int $nano)
    {
        $this->epochSecond = $epochSecond;
        $this->nano        = $nano;
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
     *
     * @return Instant
     */
    public static function of(int $epochSecond, int $nanoAdjustment = 0) : Instant
    {
        $nanos = $nanoAdjustment % LocalTime::NANOS_PER_SECOND;
        $epochSecond += ($nanoAdjustment - $nanos) / LocalTime::NANOS_PER_SECOND;

        if ($nanos < 0) {
            $nanos += LocalTime::NANOS_PER_SECOND;
            $epochSecond--;
        }

        return new Instant($epochSecond, $nanos);
    }

    /**
     * @return Instant
     */
    public static function epoch() : Instant
    {
        return new Instant(0, 0);
    }

    /**
     * @return Instant
     */
    public static function now() : Instant
    {
        return Clock::getDefault()->getTime();
    }

    /**
     * Returns the minimum supported instant.
     *
     * This could be used by an application as a "far past" instant.
     *
     * @return Instant
     */
    public static function min() : Instant
    {
        return new Instant(~ PHP_INT_MAX, 0);
    }

    /**
     * Returns the maximum supported instant.
     *
     * This could be used by an application as a "far future" instant.
     *
     * @return Instant
     */
    public static function max() : Instant
    {
        return new Instant(PHP_INT_MAX, 999999999);
    }

    /**
     * @param Duration $duration
     *
     * @return Instant
     */
    public function plus(Duration $duration) : Instant
    {
        if ($duration->isZero()) {
            return $this;
        }

        $seconds = $this->epochSecond + $duration->getSeconds();
        $nanos = $this->nano + $duration->getNanos();

        if ($nanos >= LocalTime::NANOS_PER_SECOND) {
            $nanos -= LocalTime::NANOS_PER_SECOND;
            $seconds++;
        }

        return new Instant($seconds, $nanos);
    }

    /**
     * @param Duration $duration
     *
     * @return Instant
     */
    public function minus(Duration $duration) : Instant
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->plus($duration->negated());
    }

    /**
     * @param int $seconds
     *
     * @return Instant
     */
    public function plusSeconds(int $seconds) : Instant
    {
        if ($seconds === 0) {
            return $this;
        }

        return new Instant($this->epochSecond + $seconds, $this->nano);
    }

    /**
     * @param int $seconds
     *
     * @return Instant
     */
    public function minusSeconds(int $seconds) : Instant
    {
        return $this->plusSeconds(-$seconds);
    }

    /**
     * @param int $minutes
     *
     * @return Instant
     */
    public function plusMinutes(int $minutes) : Instant
    {
        return $this->plusSeconds($minutes * LocalTime::SECONDS_PER_MINUTE);
    }

    /**
     * @param int $minutes
     *
     * @return Instant
     */
    public function minusMinutes(int $minutes) : Instant
    {
        return $this->plusMinutes(-$minutes);
    }

    /**
     * @param int $hours
     *
     * @return Instant
     */
    public function plusHours(int $hours) : Instant
    {
        return $this->plusSeconds($hours * LocalTime::SECONDS_PER_HOUR);
    }

    /**
     * @param int $hours
     *
     * @return Instant
     */
    public function minusHours(int $hours) : Instant
    {
        return $this->plusHours(-$hours);
    }

    /**
     * @param int $days
     *
     * @return Instant
     */
    public function plusDays(int $days) : Instant
    {
        return $this->plusSeconds($days * LocalTime::SECONDS_PER_DAY);
    }

    /**
     * @param int $days
     *
     * @return Instant
     */
    public function minusDays(int $days) : Instant
    {
        return $this->plusDays(-$days);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstant() : Instant
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getEpochSecond() : int
    {
        return $this->epochSecond;
    }

    /**
     * @return int
     */
    public function getNano() : int
    {
        return $this->nano;
    }

    /**
     * Returns a ZonedDateTime formed from this instant and the specified time-zone.
     *
     * @param TimeZone $timeZone
     *
     * @return ZonedDateTime
     */
    public function atTimeZone(TimeZone $timeZone) : ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this, $timeZone);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string) ZonedDateTime::ofInstant($this, TimeZone::utc());
    }
}
