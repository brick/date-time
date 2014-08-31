<?php

namespace Brick\DateTime;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Utility\Time;
use Brick\DateTime\Utility\Cast;

/**
 * Represents a point in time, with a 1 second precision.
 *
 * Instant represents the computer view of the timeline. It unambiguously represents a point in time,
 * without any calendar concept of date, time or time zone.
 *
 * Instant is easily and unambiguously persistable as an integer. It is not very meaningful to humans,
 * but can be converted to a `ZonedDateTime` by providing a time zone.
 */
class Instant extends ReadableInstant
{
    /**
     * The number of seconds since the epoch of 1970-01-01T00:00:00Z.
     *
     * @var integer
     */
    private $epochSecond;

    /**
     * The nanoseconds adjustment to the epoch second, in the range 0 to 999,999,999.
     *
     * @var integer
     */
    private $nano;

    /**
     * Private constructor. Use of() to obtain an Instant.
     *
     * @param integer $epochSecond The epoch second, validated as an integer.
     * @param integer $nano        The nanosecond adjustment, validated as an integer in the range 0 to 999,999,999.
     */
    private function __construct($epochSecond, $nano)
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
     * @param integer $epochSecond    The number of seconds since the UNIX epoch of 1970-01-01T00:00:00Z.
     * @param integer $nanoAdjustment The adjustment to the epoch second in nanoseconds.
     *
     * @return Instant
     *
     * @throws \InvalidArgumentException If the parameters are not valid integers.
     */
    public static function of($epochSecond, $nanoAdjustment = 0)
    {
        $seconds = Cast::toInteger($epochSecond);
        $nanoAdjustment = Cast::toInteger($nanoAdjustment);

        $nanos = $nanoAdjustment % LocalTime::NANOS_PER_SECOND;
        $seconds += ($nanoAdjustment - $nanos) / LocalTime::NANOS_PER_SECOND;

        if ($nanos < 0) {
            $nanos += LocalTime::NANOS_PER_SECOND;
            $seconds--;
        }

        return new Instant($seconds, $nanos);
    }

    /**
     * @return Instant
     */
    public static function epoch()
    {
        return new Instant(0, 0);
    }

    /**
     * @return Instant
     */
    public static function now()
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
    public static function min()
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
    public static function max()
    {
        return new Instant(PHP_INT_MAX, 999999999);
    }

    /**
     * @param Duration $duration
     *
     * @return Instant
     */
    public function plus(Duration $duration)
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
    public function minus(Duration $duration)
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->plus($duration->negated());
    }

    /**
     * @param integer $seconds
     *
     * @return Instant
     */
    public function plusSeconds($seconds)
    {
        $seconds = Cast::toInteger($seconds);

        if ($seconds === 0) {
            return $this;
        }

        return new Instant($this->epochSecond + $seconds, $this->nano);
    }

    /**
     * @param integer $seconds
     *
     * @return Instant
     */
    public function minusSeconds($seconds)
    {
        $seconds = Cast::toInteger($seconds);

        return $this->plusSeconds(-$seconds);
    }

    /**
     * @param integer $minutes
     *
     * @return Instant
     */
    public function plusMinutes($minutes)
    {
        $minutes = Cast::toInteger($minutes);

        return $this->plusSeconds($minutes * LocalTime::SECONDS_PER_MINUTE);
    }

    /**
     * @param integer $minutes
     *
     * @return Instant
     */
    public function minusMinutes($minutes)
    {
        $minutes = Cast::toInteger($minutes);

        return $this->plusMinutes(-$minutes);
    }

    /**
     * @param integer $hours
     *
     * @return Instant
     */
    public function plusHours($hours)
    {
        $hours = Cast::toInteger($hours);

        return $this->plusSeconds($hours * LocalTime::SECONDS_PER_HOUR);
    }

    /**
     * @param integer $hours
     *
     * @return Instant
     */
    public function minusHours($hours)
    {
        $hours = Cast::toInteger($hours);

        return $this->plusHours(-$hours);
    }

    /**
     * @param integer $days
     *
     * @return Instant
     */
    public function plusDays($days)
    {
        $days = Cast::toInteger($days);

        return $this->plusSeconds($days * LocalTime::SECONDS_PER_DAY);
    }

    /**
     * @param integer $days
     *
     * @return Instant
     */
    public function minusDays($days)
    {
        $days = Cast::toInteger($days);

        return $this->plusDays(-$days);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstant()
    {
        return $this;
    }

    /**
     * @return integer
     */
    public function getEpochSecond()
    {
        return $this->epochSecond;
    }

    /**
     * @return integer
     */
    public function getNano()
    {
        return $this->nano;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) ZonedDateTime::ofInstant($this, TimeZone::utc());
    }
}
