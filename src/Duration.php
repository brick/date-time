<?php

namespace Brick\DateTime;

use Brick\DateTime\Utility\Math;
use Brick\DateTime\Utility\Time;
use Brick\DateTime\Utility\Cast;

/**
 * Represents a duration of time measured in seconds.
 *
 * This class is immutable.
 *
 * @todo format() method to output something like 3 hours, 4 minutes and 30 seconds
 */
class Duration
{
    /**
     * The duration in seconds.
     *
     * @var integer
     */
    private $seconds;

    /**
     * The nanoseconds adjustment to the duration, in the range 0 to 999,999,999.
     *
     * A duration of -1 nanoseconds is stored as -1 seconds plus 999,999,999 nanoseconds.
     *
     * @var integer
     */
    private $nanos;

    /**
     * Private constructor. Use one of the factory methods to obtain a Duration.
     *
     * @param integer $seconds The duration in seconds, validated as an integer.
     * @param integer $nanos   The nanoseconds adjustment, validated as an integer in the range 0 to 999,999,999.
     */
    private function __construct($seconds, $nanos = 0)
    {
        $this->seconds = $seconds;
        $this->nanos   = $nanos;
    }

    /**
     * Returns a zero length Duration.
     *
     * @return \Brick\DateTime\Duration
     */
    public static function zero()
    {
        return new Duration(0);
    }

    /**
     * Obtains an instance of `Duration` by parsing a text string.
     *
     * This will parse the ISO-8601 duration format `PnDTnHnMn.nS`
     * which is the format returned by `__toString()`.
     *
     * All of the values (days, hours, minutes, seconds, nanoseconds) are optional,
     * but the duration must at least contain one of the (days, hours, minutes, seconds) values.
     *
     * A day is considered to by 24 hours, or 86400 seconds.
     *
     * The 'T' separator must only be present if one the (hours, minutes, seconds) values are present.
     *
     * Each of the (days, hours, minutes, seconds) values can optionally be preceded with a '+' or '-' sign.
     * The whole string can also start with an optional '+' or '-' sign, which will further affect all the fields.
     *
     * @param string $text
     *
     * @return \Brick\DateTime\Duration
     *
     * @throws \Brick\DateTime\Parser\DateTimeParseException
     */
    public static function parse($text)
    {
        $pattern =
            '/^' .
            '([\-\+]?)' .
            'P' .
            '(?:([\-\+]?[0-9]+)D)?' .
            '(?:(T)' .
            '(?:([\-\+]?[0-9]+)H)?' .
            '(?:([\-\+]?[0-9]+)M)?' .
            '(?:([\-\+]?[0-9]+)(?:\.([0-9]{1,9}))?S)?' .
            ')?' .
            '()$/i';

        if (preg_match($pattern, $text, $matches) !== 1) {
            throw Parser\DateTimeParseException::invalidDuration($text);
        }

        list (, $sign, $days, $t, $hours, $minutes, $seconds, $nanos) = $matches;

        if ($hours === '' && $minutes === '' && $seconds === '') {
            if ($days === '' || $t === 'T') {
                throw Parser\DateTimeParseException::invalidDuration($text);
            }
        }

        $allNegative = ($sign === '-');
        $secondsNegative = ($seconds !== '' && $seconds[0] === '-');

        $nanos = str_pad($nanos, 9, '0', STR_PAD_RIGHT);

        $days    = (int) $days;
        $hours   = (int) $hours;
        $minutes = (int) $minutes;
        $seconds = (int) $seconds;
        $nanos   = (int) $nanos;

        if ($secondsNegative) {
            $nanos = -$nanos;
        }

        if ($allNegative) {
            $days    = -$days;
            $hours   = -$hours;
            $minutes = -$minutes;
            $seconds = -$seconds;
            $nanos   = -$nanos;
        }

        $seconds +=
            LocalTime::SECONDS_PER_DAY * $days +
            LocalTime::SECONDS_PER_HOUR * $hours +
            LocalTime::SECONDS_PER_MINUTE * $minutes;

        if ($nanos < 0) {
            $nanos += LocalTime::NANOS_PER_SECOND;
            $seconds--;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a Duration representing a number of seconds and an adjustment in nanoseconds.
     *
     * This method allows an arbitrary number of nanoseconds to be passed in.
     * The factory will alter the values of the second and nanosecond in order
     * to ensure that the stored nanosecond is in the range 0 to 999,999,999.
     * For example, the following will result in the exactly the same duration:
     *
     * * Duration::ofSeconds(3, 1);
     * * Duration::ofSeconds(4, -999999999);
     * * Duration::ofSeconds(2, 1000000001);
     *
     * @param integer $seconds        The number of seconds of the duration.
     * @param integer $nanoAdjustment The adjustment to the duration in nanoseconds.
     *
     * @return \Brick\DateTime\Duration
     */
    public static function ofSeconds($seconds, $nanoAdjustment = 0)
    {
        $seconds = Cast::toInteger($seconds);
        $nanoAdjustment = Cast::toInteger($nanoAdjustment);

        $nanoseconds = $nanoAdjustment % LocalTime::NANOS_PER_SECOND;
        $seconds += ($nanoAdjustment - $nanoseconds) / LocalTime::NANOS_PER_SECOND;

        if ($nanoseconds < 0) {
            $nanoseconds += LocalTime::NANOS_PER_SECOND;
            $seconds--;
        }

        return new Duration($seconds, $nanoseconds);
    }

    /**
     * Returns a Duration from a number of minutes.
     *
     * @param integer $minutes
     *
     * @return \Brick\DateTime\Duration
     */
    public static function ofMinutes($minutes)
    {
        return new Duration(60 * Cast::toInteger($minutes));
    }

    /**
     * Returns a Duration from a number of hours.
     *
     * @param integer $hours
     *
     * @return \Brick\DateTime\Duration
     */
    public static function ofHours($hours)
    {
        return new Duration(3600 * Cast::toInteger($hours));
    }

    /**
     * Returns a Duration from a number of days.
     *
     * @param integer $days
     *
     * @return \Brick\DateTime\Duration
     */
    public static function ofDays($days)
    {
        return new Duration(86400 * Cast::toInteger($days));
    }

    /**
     * Returns a Duration representing the time elapsed between two instants.
     *
     * A Duration represents a directed distance between two points on the time-line.
     * As such, this method will return a negative duration if the end is before the start.
     *
     * @param \Brick\DateTime\ReadableInstant $startInclusive The start instant, inclusive.
     * @param \Brick\DateTime\ReadableInstant $endExclusive   The end instant, exclusive.
     *
     * @return \Brick\DateTime\Duration
     */
    public static function between(ReadableInstant $startInclusive, ReadableInstant $endExclusive)
    {
        $startInclusive = $startInclusive->getInstant();
        $endExclusive = $endExclusive->getInstant();

        $seconds = $endExclusive->getEpochSecond() - $startInclusive->getEpochSecond();
        $nanos = $endExclusive->getNano() - $startInclusive->getNano();

        return Duration::ofSeconds($seconds, $nanos);
    }

    /**
     * Returns whether this Duration is zero length.
     *
     * @return boolean
     */
    public function isZero()
    {
        return $this->seconds === 0 && $this->nanos === 0;
    }

    /**
     * Returns whether this Duration is positive, excluding zero.
     *
     * @return boolean
     */
    public function isPositive()
    {
        return $this->seconds > 0 || ($this->seconds === 0 && $this->nanos !== 0);
    }

    /**
     * Returns whether this Duration is positive or zero.
     *
     * @return boolean
     */
    public function isPositiveOrZero()
    {
        return $this->seconds >= 0;
    }

    /**
     * Returns whether this Duration is negative, excluding zero.
     *
     * @return boolean
     */
    public function isNegative()
    {
        return $this->seconds < 0;
    }

    /**
     * Returns whether this Duration is negative or zero.
     *
     * @return boolean
     */
    public function isNegativeOrZero()
    {
        return $this->seconds < 0 || ($this->seconds === 0 && $this->nanos === 0);
    }

    /**
     * Returns a copy of this Duration with the specified duration added.
     *
     * @param \Brick\DateTime\Duration $duration
     *
     * @return \Brick\DateTime\Duration
     */
    public function plus(Duration $duration)
    {
        if ($duration->isZero()) {
            return $this;
        }

        $seconds = $this->seconds + $duration->seconds;
        $nanos = $this->nanos + $duration->nanos;

        if ($nanos >= LocalTime::NANOS_PER_SECOND) {
            $nanos -= LocalTime::NANOS_PER_SECOND;
            $seconds++;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this Duration with the specified duration in seconds added.
     *
     * @param integer $seconds
     *
     * @return \Brick\DateTime\Duration
     */
    public function plusSeconds($seconds)
    {
        $seconds = Cast::toInteger($seconds);

        if ($seconds === 0) {
            return $this;
        }

        return new Duration($this->seconds + $seconds, $this->nanos);
    }

    /**
     * Returns a copy of this Duration with the specified duration in minutes added.
     *
     * @param integer $minutes
     *
     * @return \Brick\DateTime\Duration
     */
    public function plusMinutes($minutes)
    {
        $minutes = Cast::toInteger($minutes);

        return $this->plusSeconds($minutes * LocalTime::SECONDS_PER_MINUTE);
    }

    /**
     * Returns a copy of this Duration with the specified duration in hours added.
     *
     * @param integer $hours
     *
     * @return \Brick\DateTime\Duration
     */
    public function plusHours($hours)
    {
        $hours = Cast::toInteger($hours);

        return $this->plusSeconds($hours * LocalTime::SECONDS_PER_HOUR);
    }

    /**
     * Returns a copy of this Duration with the specified duration in days added.
     *
     * @param integer $days
     *
     * @return \Brick\DateTime\Duration
     */
    public function plusDays($days)
    {
        $days = Cast::toInteger($days);

        return $this->plusSeconds($days * LocalTime::SECONDS_PER_DAY);
    }

    /**
     * Returns a copy of this Duration with the specified duration added.
     *
     * @param \Brick\DateTime\Duration $duration
     *
     * @return \Brick\DateTime\Duration
     */
    public function minus(Duration $duration)
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->plus($duration->negated());
    }

    /**
     * Returns a copy of this Duration with the specified duration in seconds subtracted.
     *
     * @param integer $seconds
     *
     * @return \Brick\DateTime\Duration
     */
    public function minusSeconds($seconds)
    {
        $seconds = Cast::toInteger($seconds);

        return $this->plusSeconds(-$seconds);
    }

    /**
     * Returns a copy of this Duration with the specified duration in minutes subtracted.
     *
     * @param integer $minutes
     *
     * @return \Brick\DateTime\Duration
     */
    public function minusMinutes($minutes)
    {
        $minutes = Cast::toInteger($minutes);

        return $this->plusMinutes(-$minutes);
    }

    /**
     * Returns a copy of this Duration with the specified duration in hours subtracted.
     *
     * @param integer $hours
     *
     * @return \Brick\DateTime\Duration
     */
    public function minusHours($hours)
    {
        $hours = Cast::toInteger($hours);

        return $this->plusHours(-$hours);
    }

    /**
     * Returns a copy of this Duration with the specified duration in days subtracted.
     *
     * @param integer $days
     *
     * @return \Brick\DateTime\Duration
     */
    public function minusDays($days)
    {
        $days = Cast::toInteger($days);

        return $this->plusDays(-$days);
    }

    /**
     * Returns a copy of this Duration multiplied by the given value.
     *
     * @param integer $multiplicand
     *
     * @return \Brick\DateTime\Duration
     */
    public function multipliedBy($multiplicand)
    {
        $multiplicand = Cast::toInteger($multiplicand);

        if ($multiplicand === 1) {
            return $this;
        }

        $seconds = $this->seconds * $multiplicand;
        $totalnanos = $this->nanos * $multiplicand;

        $nanoseconds = $totalnanos % LocalTime::NANOS_PER_SECOND;
        $seconds += ($totalnanos - $nanoseconds) / LocalTime::NANOS_PER_SECOND;

        return new Duration($seconds, $nanoseconds);
    }

    /**
     * Returns a copy of this Duration divided by the given value.
     *
     * If this yields an inexact result, the result will be rounded down.
     *
     * @param integer $divisor
     *
     * @return \Brick\DateTime\Duration
     */
    public function dividedBy($divisor)
    {
        $divisor = Cast::toInteger($divisor);

        if ($divisor === 0) {
            throw new DateTimeException('Cannot divide a Duration by zero.');
        }

        if ($divisor === 1) {
            return $this;
        }

        $seconds = $this->seconds;
        $nanos = $this->nanos;

        if ($seconds < 0 && $nanos !== 0) {
            $seconds++;
            $nanos -= LocalTime::NANOS_PER_SECOND;
        }

        $seconds = Math::div($seconds, $divisor, $remainder);
        $nanos = Math::div($nanos, $divisor, $r1);

        $nanos += $remainder * Math::div(LocalTime::NANOS_PER_SECOND, $divisor, $r2);
        $nanos += Math::div($r1 + $remainder * $r2, $divisor);

        if ($nanos < 0) {
            $seconds--;
            $nanos = LocalTime::NANOS_PER_SECOND + $nanos;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this Duration with the length negated.
     *
     * @return \Brick\DateTime\Duration
     */
    public function negated()
    {
        if ($this->isZero()) {
            return $this;
        }

        $seconds = -$this->seconds;
        $nanos = $this->nanos;

        if ($nanos !== 0) {
            $nanos = LocalTime::NANOS_PER_SECOND - $nanos;
            $seconds--;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this Duration with a positive length.
     *
     * @return \Brick\DateTime\Duration
     */
    public function abs()
    {
        return $this->isNegative() ? $this->negated() : $this;
    }

    /**
     * Compares this Duration to the specified duration.
     *
     * @param \Brick\DateTime\Duration $that The other duration to compare to.
     *
     * @return integer [-1,0,1] If this duration is less than, equal to, or greater than the given duration.
     */
    public function compareTo(Duration $that)
    {
        $seconds = $this->seconds - $that->seconds;

        if ($seconds !== 0) {
            return $seconds > 0 ? 1 : -1;
        }

        $nanos = $this->nanos - $that->nanos;

        if ($nanos !== 0) {
            return $nanos > 0 ? 1 : -1;
        }

        return 0;
    }

    /**
     * Checks if this Duration is equal to the specified duration.
     *
     * @param \Brick\DateTime\Duration $that
     *
     * @return boolean
     */
    public function isEqualTo(Duration $that)
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Checks if this Duration is greater than the specified duration.
     *
     * @param \Brick\DateTime\Duration $that
     *
     * @return boolean
     */
    public function isGreaterThan(Duration $that)
    {
        return $this->compareTo($that) > 0;
    }

    /**
     * Checks if this Duration is less than the specified duration.
     *
     * @param \Brick\DateTime\Duration $that
     *
     * @return boolean
     */
    public function isLessThan(Duration $that)
    {
        return $this->compareTo($that) < 0;
    }

    /**
     * Returns the total length in seconds of this Duration.
     *
     * @return integer
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    /**
     * Returns the nanoseconds adjustment of this Duration, in the range 0 to 999,999,999.
     *
     * @return integer
     */
    public function getNanos()
    {
        return $this->nanos;
    }

    /**
     * Returns an ISO-8601 string representation of this duration.
     *
     * The format of the returned string will be PTnHnMn.nS, where n is
     * the relevant hours, minutes, seconds or nanoseconds part of the duration.
     *
     * If a section has a zero value, it is omitted, unless the whole duration is zero.
     * The hours, minutes and seconds will all have the same sign.
     *
     * Note that multiples of 24 hours are not output as days to avoid confusion with Period.
     *
     * @return string
     */
    public function __toString()
    {
        $seconds = $this->seconds;
        $nanos   = $this->nanos;

        if ($seconds === 0 && $nanos === 0) {
            return 'PT0S';
        }

        $negative = ($seconds < 0);

        if ($seconds < 0 && $nanos !== 0) {
            $seconds++;
            $nanos = LocalTime::NANOS_PER_SECOND - $nanos;
        }

        $hours = Math::div($seconds, LocalTime::SECONDS_PER_HOUR);
        $minutes = Math::div($seconds % LocalTime::SECONDS_PER_HOUR, LocalTime::SECONDS_PER_MINUTE);
        $seconds = $seconds % LocalTime::SECONDS_PER_MINUTE;

        $string = 'PT';

        if ($hours !== 0) {
            $string .= $hours . 'H';
        }
        if ($minutes !== 0) {
            $string .= $minutes . 'M';
        }

        if ($seconds === 0 && $nanos === 0) {
            return $string;
        }

        $string .= (($seconds === 0 && $negative) ? '-0' : $seconds);

        if ($nanos !== 0) {
            $string .= '.' . rtrim(sprintf('%09d', $nanos), '0');
        }

        return $string . 'S';
    }
}
