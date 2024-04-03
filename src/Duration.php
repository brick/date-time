<?php

declare(strict_types=1);

namespace Brick\DateTime;

use ArithmeticError;
use Brick\DateTime\Utility\Math;
use JsonSerializable;
use Stringable;

use function assert;
use function intdiv;
use function is_int;
use function preg_match;
use function rtrim;
use function str_pad;

use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * Represents a duration of time measured in seconds.
 *
 * This class is immutable.
 */
final class Duration implements JsonSerializable, Stringable
{
    /**
     * Private constructor. Use one of the factory methods to obtain a Duration.
     *
     * @param int $seconds The duration in seconds.
     * @param int $nanos   The nanoseconds adjustment to the duration, validated in the range 0 to 999,999,999.
     *                     A duration of -1 nanoseconds is stored as -1 seconds plus 999,999,999 nanoseconds.
     */
    private function __construct(
        private readonly int $seconds,
        private readonly int $nanos = 0,
    ) {
    }

    /**
     * Returns a zero length Duration.
     */
    public static function zero(): Duration
    {
        /** @var Duration|null $zero */
        static $zero = null;

        return $zero ??= new Duration(0);
    }

    /**
     * Obtains an instance of `Duration` by parsing a text string.
     *
     * This will parse the ISO-8601 duration format `PnDTnHnMn.nS`
     * which is the format returned by `__toString()`.
     *
     * All the values (days, hours, minutes, seconds, nanoseconds) are optional,
     * but the duration must at least contain one of the (days, hours, minutes, seconds) values.
     *
     * A day is considered to by 24 hours, or 86400 seconds.
     *
     * The 'T' separator must only be present if one the (hours, minutes, seconds) values are present.
     *
     * Each of the (days, hours, minutes, seconds) values can optionally be preceded with a '+' or '-' sign.
     * The whole string can also start with an optional '+' or '-' sign, which will further affect all the fields.
     *
     * @throws Parser\DateTimeParseException
     */
    public static function parse(string $text): Duration
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

        [, $sign, $days, $t, $hours, $minutes, $seconds, $nanos] = $matches;

        if ($hours === '' && $minutes === '' && $seconds === '') {
            if ($days === '' || $t === 'T') {
                throw Parser\DateTimeParseException::invalidDuration($text);
            }
        }

        $allNegative = ($sign === '-');
        $secondsNegative = ($seconds !== '' && $seconds[0] === '-');

        $nanos = str_pad($nanos, 9, '0', STR_PAD_RIGHT);

        $days = (int) $days;
        $hours = (int) $hours;
        $minutes = (int) $minutes;
        $seconds = (int) $seconds;
        $nanos = (int) $nanos;

        if ($secondsNegative) {
            $nanos = -$nanos;
        }

        if ($allNegative) {
            $days = -$days;
            $hours = -$hours;
            $minutes = -$minutes;
            $seconds = -$seconds;
            $nanos = -$nanos;
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
     * For example, the following will result in exactly the same duration:
     *
     * * Duration::ofSeconds(3, 1);
     * * Duration::ofSeconds(4, -999_999_999);
     * * Duration::ofSeconds(2, 1_000_000_001);
     *
     * @param int $seconds        The number of seconds of the duration.
     * @param int $nanoAdjustment The adjustment to the duration in nanoseconds.
     */
    public static function ofSeconds(int $seconds, int $nanoAdjustment = 0): Duration
    {
        $nanoseconds = $nanoAdjustment % LocalTime::NANOS_PER_SECOND;
        $seconds += ($nanoAdjustment - $nanoseconds) / LocalTime::NANOS_PER_SECOND;
        assert(is_int($seconds));

        if ($nanoseconds < 0) {
            $nanoseconds += LocalTime::NANOS_PER_SECOND;
            $seconds--;
        }

        return new Duration($seconds, $nanoseconds);
    }

    /**
     * Returns a Duration from a number of milliseconds.
     */
    public static function ofMillis(int $millis): Duration
    {
        $seconds = intdiv($millis, LocalTime::MILLIS_PER_SECOND);
        $nanos = ($millis % LocalTime::MILLIS_PER_SECOND) * LocalTime::NANOS_PER_MILLI;

        return self::ofSeconds($seconds, $nanos);
    }

    /**
     * Returns a Duration from a number of nanoseconds.
     */
    public static function ofNanos(int $nanos): Duration
    {
        return self::ofSeconds(0, $nanos);
    }

    /**
     * Returns a Duration from a number of minutes.
     */
    public static function ofMinutes(int $minutes): Duration
    {
        return new Duration(60 * $minutes);
    }

    /**
     * Returns a Duration from a number of hours.
     */
    public static function ofHours(int $hours): Duration
    {
        return new Duration(3600 * $hours);
    }

    /**
     * Returns a Duration from a number of days.
     */
    public static function ofDays(int $days): Duration
    {
        return new Duration(86400 * $days);
    }

    /**
     * Returns a Duration representing the time elapsed between two instants.
     *
     * A Duration represents a directed distance between two points on the time-line.
     * As such, this method will return a negative duration if the end is before the start.
     *
     * @param Instant $startInclusive The start instant, inclusive.
     * @param Instant $endExclusive   The end instant, exclusive.
     */
    public static function between(Instant $startInclusive, Instant $endExclusive): Duration
    {
        $seconds = $endExclusive->getEpochSecond() - $startInclusive->getEpochSecond();
        $nanos = $endExclusive->getNano() - $startInclusive->getNano();

        return Duration::ofSeconds($seconds, $nanos);
    }

    /**
     * Returns whether this Duration is zero length.
     */
    public function isZero(): bool
    {
        return $this->seconds === 0 && $this->nanos === 0;
    }

    /**
     * Returns whether this Duration is positive, excluding zero.
     */
    public function isPositive(): bool
    {
        return $this->seconds > 0 || ($this->seconds === 0 && $this->nanos !== 0);
    }

    /**
     * Returns whether this Duration is positive or zero.
     */
    public function isPositiveOrZero(): bool
    {
        return $this->seconds >= 0;
    }

    /**
     * Returns whether this Duration is negative, excluding zero.
     */
    public function isNegative(): bool
    {
        return $this->seconds < 0;
    }

    /**
     * Returns whether this Duration is negative or zero.
     */
    public function isNegativeOrZero(): bool
    {
        return $this->seconds < 0 || ($this->seconds === 0 && $this->nanos === 0);
    }

    /**
     * Returns a copy of this Duration with the specified duration added.
     */
    public function plus(Duration $duration): Duration
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
     */
    public function plusSeconds(int $seconds): Duration
    {
        if ($seconds === 0) {
            return $this;
        }

        return new Duration($this->seconds + $seconds, $this->nanos);
    }

    /**
     * Returns a copy of this Duration with the specified duration in minutes added.
     */
    public function plusMinutes(int $minutes): Duration
    {
        return $this->plusSeconds($minutes * LocalTime::SECONDS_PER_MINUTE);
    }

    /**
     * Returns a copy of this Duration with the specified duration in hours added.
     */
    public function plusHours(int $hours): Duration
    {
        return $this->plusSeconds($hours * LocalTime::SECONDS_PER_HOUR);
    }

    /**
     * Returns a copy of this Duration with the specified duration in days added.
     */
    public function plusDays(int $days): Duration
    {
        return $this->plusSeconds($days * LocalTime::SECONDS_PER_DAY);
    }

    /**
     * Returns a copy of this Duration with the specified duration added.
     */
    public function minus(Duration $duration): Duration
    {
        if ($duration->isZero()) {
            return $this;
        }

        return $this->plus($duration->negated());
    }

    /**
     * Returns a copy of this Duration with the specified duration in seconds subtracted.
     */
    public function minusSeconds(int $seconds): Duration
    {
        return $this->plusSeconds(-$seconds);
    }

    /**
     * Returns a copy of this Duration with the specified duration in minutes subtracted.
     */
    public function minusMinutes(int $minutes): Duration
    {
        return $this->plusMinutes(-$minutes);
    }

    /**
     * Returns a copy of this Duration with the specified duration in hours subtracted.
     */
    public function minusHours(int $hours): Duration
    {
        return $this->plusHours(-$hours);
    }

    /**
     * Returns a copy of this Duration with the specified duration in days subtracted.
     */
    public function minusDays(int $days): Duration
    {
        return $this->plusDays(-$days);
    }

    /**
     * Returns a copy of this Duration multiplied by the given value.
     */
    public function multipliedBy(int $multiplicand): Duration
    {
        if ($multiplicand === 1) {
            return $this;
        }

        $seconds = $this->seconds * $multiplicand;
        $totalnanos = $this->nanos * $multiplicand;

        return Duration::ofSeconds($seconds, $totalnanos);
    }

    /**
     * Returns a copy of this Duration divided by the given value.
     *
     * If this yields an inexact result, the result will be rounded down.
     *
     * @throws DateTimeException If the divisor is zero.
     */
    public function dividedBy(int $divisor): Duration
    {
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

        $remainder = $seconds % $divisor;
        $seconds = intdiv($seconds, $divisor);

        $r1 = $nanos % $divisor;
        $nanos = intdiv($nanos, $divisor);

        $r2 = LocalTime::NANOS_PER_SECOND % $divisor;
        $nanos += $remainder * intdiv(LocalTime::NANOS_PER_SECOND, $divisor);
        $nanos += intdiv($r1 + $remainder * $r2, $divisor);

        if ($nanos < 0) {
            $seconds--;
            $nanos = LocalTime::NANOS_PER_SECOND + $nanos;
        }

        return new Duration($seconds, $nanos);
    }

    /**
     * Returns a copy of this Duration with the length negated.
     */
    public function negated(): Duration
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
     */
    public function abs(): Duration
    {
        return $this->isNegative() ? $this->negated() : $this;
    }

    /**
     * Compares this Duration to the specified duration.
     *
     * @param Duration $that The other duration to compare to.
     *
     * @return int [-1,0,1] If this duration is less than, equal to, or greater than the given duration.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(Duration $that): int
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
     */
    public function isEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Checks if this Duration is greater than the specified duration.
     */
    public function isGreaterThan(Duration $that): bool
    {
        return $this->compareTo($that) > 0;
    }

    /**
     * Checks if this Duration is greater than or equal to the specified duration.
     */
    public function isGreaterThanOrEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Checks if this Duration is less than the specified duration.
     */
    public function isLessThan(Duration $that): bool
    {
        return $this->compareTo($that) < 0;
    }

    /**
     * Checks if this Duration is less than or equal to the specified duration.
     */
    public function isLessThanOrEqualTo(Duration $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * Returns the total length in seconds of this Duration.
     */
    public function getSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Returns the nanoseconds adjustment of this Duration, in the range 0 to 999,999,999.
     */
    public function getNanos(): int
    {
        return $this->nanos;
    }

    /**
     * Returns the total number of milliseconds in this Duration.
     *
     * The result is rounded towards negative infinity.
     *
     * @todo deprecate in favour of toMillis() - caution: rounding is different
     */
    public function getTotalMillis(): int
    {
        $millis = $this->seconds * 1000;
        $millis += intdiv($this->nanos, 1_000_000);

        return $millis;
    }

    /**
     * Returns the total number of microseconds in this Duration.
     *
     * The result is rounded towards negative infinity.
     */
    public function getTotalMicros(): int
    {
        $micros = $this->seconds * 1_000_000;
        $micros += intdiv($this->nanos, 1000);

        return $micros;
    }

    public function getTotalNanos(): int
    {
        $nanos = $this->seconds * 1_000_000_000;
        $nanos += $this->nanos;

        return $nanos;
    }

    /**
     * Gets the number of days in this duration.
     *
     * This returns the total number of days in the duration by dividing the number of seconds by 86400.
     * This is based on the standard definition of a day as 24 hours.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toDays(): int
    {
        return intdiv($this->seconds, LocalTime::SECONDS_PER_DAY);
    }

    /**
     * Extracts the number of days in the duration.
     *
     * This return the same value as `toDays()`, and is provided solely for consistency.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toDaysPart(): int
    {
        return $this->toDays();
    }

    /**
     * Gets the number of hours in this duration.
     *
     * This returns the total number of hours in the duration by dividing the number of seconds by 3600.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toHours(): int
    {
        return intdiv($this->seconds, LocalTime::SECONDS_PER_HOUR);
    }

    /**
     * Extracts the number of hours part in the duration.
     *
     * This returns the number of remaining hours when dividing `toHours()` by hours in a day.
     * This is based on the standard definition of a day as 24 hours.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toHoursPart(): int
    {
        return $this->toHours() % 24;
    }

    /**
     * Gets the number of minutes in this duration.
     *
     * This returns the total number of minutes in the duration by dividing the number of seconds by 60.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toMinutes(): int
    {
        return intdiv($this->seconds, LocalTime::SECONDS_PER_MINUTE);
    }

    /**
     * Extracts the number of minutes part in the duration.
     *
     * This returns the number of remaining minutes when dividing `toMinutes()` by minutes in an hour.
     * This is based on the standard definition of an hour as 60 minutes.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toMinutesPart(): int
    {
        return $this->toMinutes() % LocalTime::MINUTES_PER_HOUR;
    }

    /**
     * Gets the number of seconds in this duration.
     *
     * This returns the total number of whole seconds in the duration.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toSeconds(): int
    {
        return $this->seconds;
    }

    /**
     * Extracts the number of seconds part in the duration.
     *
     * This returns the remaining seconds when dividing `toSeconds()` by seconds in a minute.
     * This is based on the standard definition of a minute as 60 seconds.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toSecondsPart(): int
    {
        return $this->toSeconds() % LocalTime::SECONDS_PER_MINUTE;
    }

    /**
     * Converts this duration to the total length in milliseconds.
     *
     * If this duration is too large to fit in an integer, then an exception is thrown.
     *
     * If this duration has greater than millisecond precision, then the conversion will drop any excess precision
     * information as though the amount in nanoseconds was subject to integer division by one million.
     *
     * @throws ArithmeticError
     */
    public function toMillis(): int
    {
        $tempSeconds = $this->seconds;
        $tempNanos = $this->nanos;

        if ($tempSeconds < 0) {
            $tempSeconds++;
            $tempNanos -= LocalTime::NANOS_PER_SECOND;
        }

        $millis = Math::multiplyExact($tempSeconds, LocalTime::MILLIS_PER_SECOND);
        $millis = Math::addExact($millis, intdiv($tempNanos, LocalTime::NANOS_PER_MILLI));

        return $millis;
    }

    /**
     * Extracts the number of milliseconds part of the duration.
     *
     * This returns the milliseconds part by dividing the number of nanoseconds by 1,000,000.
     * The length of the duration is stored using two fields - seconds and nanoseconds.
     * The nanoseconds part is a value from 0 to 999,999,999 that is an adjustment to the length in seconds.
     * The total duration is defined by calling `getNanos()` and `getSeconds()`.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toMillisPart(): int
    {
        return intdiv($this->nanos, LocalTime::NANOS_PER_MILLI);
    }

    /**
     * Converts this duration to the total length in nanoseconds.
     *
     * If this duration is too large to fit in an integer, then an exception is thrown.
     *
     * @throws ArithmeticError
     */
    public function toNanos(): int
    {
        $tempSeconds = $this->seconds;
        $tempNanos = $this->nanos;

        if ($tempSeconds < 0) {
            $tempSeconds++;
            $tempNanos -= LocalTime::NANOS_PER_SECOND;
        }

        $totalNanos = Math::multiplyExact($tempSeconds, LocalTime::NANOS_PER_SECOND);
        $totalNanos = Math::addExact($totalNanos, $tempNanos);

        return $totalNanos;
    }

    /**
     * Gets the nanoseconds part within seconds of the duration.
     *
     * The length of the duration is stored using two fields - seconds and nanoseconds.
     * The nanoseconds part is a value from 0 to 999,999,999 that is an adjustment to the length in seconds.
     * The total duration is defined by calling `getNanos()` and `getSeconds()`.
     *
     * This instance is immutable and unaffected by this method call.
     */
    public function toNanosPart(): int
    {
        return $this->nanos;
    }

    /**
     * Serializes as a string using {@see Duration::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this duration.
     *
     * The format of the returned string will be PTnHnMn.nS, where n is
     * the relevant hours, minutes, seconds or nanoseconds part of the duration.
     *
     * If a section has a zero value, it is omitted, unless the whole duration is zero.
     * The hours, minutes and seconds will all have the same sign.
     *
     * Note that multiples of 24 hours are not output as days to avoid confusion with Period.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        $seconds = $this->seconds;
        $nanos = $this->nanos;

        if ($seconds === 0 && $nanos === 0) {
            return 'PT0S';
        }

        $negative = ($seconds < 0);

        if ($seconds < 0 && $nanos !== 0) {
            $seconds++;
            $nanos = LocalTime::NANOS_PER_SECOND - $nanos;
        }

        $hours = intdiv($seconds, LocalTime::SECONDS_PER_HOUR);
        $minutes = intdiv($seconds % LocalTime::SECONDS_PER_HOUR, LocalTime::SECONDS_PER_MINUTE);
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
            $string .= '.' . rtrim(str_pad((string) $nanos, 9, '0', STR_PAD_LEFT), '0');
        }

        return $string . 'S';
    }

    /**
     * {@see Duration::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
