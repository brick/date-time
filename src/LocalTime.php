<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Field\HourOfDay;
use Brick\DateTime\Field\MinuteOfHour;
use Brick\DateTime\Field\SecondOfMinute;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;

/**
 * A time without a time-zone in the ISO-8601 calendar system, such as 10:15:30.
 *
 * This class is immutable.
 */
class LocalTime
{
    const MONTHS_PER_YEAR    = 12;
    const DAYS_PER_WEEK      = 7;
    const HOURS_PER_DAY      = 24;
    const MINUTES_PER_HOUR   = 60;
    const MINUTES_PER_DAY    = 1440;
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_HOUR   = 3600;
    const SECONDS_PER_DAY    = 86400;
    const NANOS_PER_SECOND   = 1000000000;
    const MILLIS_PER_SECOND  = 1000;

    /**
     * The hour, in the range 0 to 23.
     *
     * @var int
     */
    private $hour;

    /**
     * The minute, in the range 0 to 59.
     *
     * @var int
     */
    private $minute;

    /**
     * The second, in the range 0 to 59.
     *
     * @var int
     */
    private $second;

    /**
     * The nanosecond, in the range 0 to 999,999,999.
     *
     * @var int
     */
    private $nano;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param int $hour   The hour-of-day, validated in the range 0 to 23.
     * @param int $minute The minute-of-hour, validated in the range 0 to 59.
     * @param int $second The second-of-minute, validated in the range 0 to 59.
     * @param int $nano   The nano-of-second, validated in the range 0 to 999,999,999.
     */
    private function __construct(int $hour, int $minute, int $second, int $nano)
    {
        $this->hour   = $hour;
        $this->minute = $minute;
        $this->second = $second;
        $this->nano   = $nano;
    }

    /**
     * @param int $hour   The hour, from 0 to 23.
     * @param int $minute The minute, from 0 to 59.
     * @param int $second The second, from 0 to 59.
     * @param int $nano   The nano-of-second, from 0 to 999,999,999.
     *
     * @return LocalTime
     *
     * @throws DateTimeException
     */
    public static function of(int $hour, int $minute, int $second = 0, int $nano = 0) : LocalTime
    {
        Field\HourOfDay::check($hour);
        Field\MinuteOfHour::check($minute);
        Field\SecondOfMinute::check($second);
        Field\NanoOfSecond::check($nano);

        return new LocalTime($hour, $minute, $second, $nano);
    }

    /**
     * Creates a LocalTime instance from a number of seconds since midnight.
     *
     * @param int $secondOfDay  The second-of-day, from 0 to 86,399.
     * @param int $nanoOfSecond The nano-of-second, from 0 to 999,999,999.
     *
     * @return LocalTime
     *
     * @throws DateTimeException
     */
    public static function ofSecondOfDay(int $secondOfDay, int $nanoOfSecond = 0) : LocalTime
    {
        Field\SecondOfDay::check($secondOfDay);
        Field\NanoOfSecond::check($nanoOfSecond);

        $hours = \intdiv($secondOfDay, self::SECONDS_PER_HOUR);
        $secondOfDay -= $hours * self::SECONDS_PER_HOUR;
        $minutes = \intdiv($secondOfDay, self::SECONDS_PER_MINUTE);
        $secondOfDay -= $minutes * self::SECONDS_PER_MINUTE;

        return new LocalTime($hours, $minutes, $secondOfDay, $nanoOfSecond);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return LocalTime
     *
     * @throws DateTimeException      If the time is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result) : LocalTime
    {
        $hour     = $result->getField(HourOfDay::NAME);
        $minute   = $result->getField(MinuteOfHour::NAME);
        $second   = $result->getOptionalField(SecondOfMinute::NAME);
        $fraction = $result->getOptionalField(Field\FractionOfSecond::NAME);

        $nano = \substr($fraction . '000000000', 0, 9);

        return LocalTime::of((int) $hour, (int) $minute, (int) $second, (int) $nano);
    }

    /**
     * Obtains an instance of `LocalTime` from a text string.
     *
     * @param string              $text   The text to parse, such as `10:15`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return LocalTime
     *
     * @throws DateTimeException      If the time is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, DateTimeParser $parser = null) : LocalTime
    {
        if (! $parser) {
            $parser = IsoParsers::localTime();
        }

        return LocalTime::from($parser->parse($text));
    }

    /**
     * Creates a LocalTime from a native DateTime object.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return LocalTime
     */
    public static function fromDateTime(\DateTimeInterface $dateTime) : LocalTime
    {
        return new LocalTime(
            (int) $dateTime->format('G'),
            (int) $dateTime->format('i'),
            (int) $dateTime->format('s'),
            1000 * (int) $dateTime->format('u')
        );
    }

    /**
     * Returns the current local time in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     *
     * @param TimeZone   $timeZone
     * @param Clock|null $clock
     *
     * @return LocalTime
     */
    public static function now(TimeZone $timeZone, Clock $clock = null) : LocalTime
    {
        return ZonedDateTime::now($timeZone, $clock)->getTime();
    }

    /**
     * @return LocalTime
     */
    public static function midnight() : LocalTime
    {
        return new LocalTime(0, 0, 0, 0);
    }

    /**
     * @return LocalTime
     */
    public static function noon() : LocalTime
    {
        return new LocalTime(12, 0, 0, 0);
    }

    /**
     * Returns the smallest possible value for LocalTime.
     *
     * @return LocalTime
     */
    public static function min() : LocalTime
    {
        return new LocalTime(0, 0, 0, 0);
    }

    /**
     * Returns the highest possible value for LocalTime.
     *
     * @return LocalTime
     */
    public static function max() : LocalTime
    {
        return new LocalTime(23, 59, 59, 999999999);
    }

    /**
     * Returns the smallest LocalTime among the given values.
     *
     * @param LocalTime[] $times The LocalTime objects to compare.
     *
     * @return LocalTime The earliest LocalTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(LocalTime ...$times) : LocalTime
    {
        if (! $times) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $min = LocalTime::max();

        foreach ($times as $time) {
            if ($time->isBefore($min)) {
                $min = $time;
            }
        }

        return $min;
    }

    /**
     * Returns the highest LocalTime among the given values.
     *
     * @param LocalTime[] $times The LocalTime objects to compare.
     *
     * @return LocalTime The latest LocalTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(LocalTime ...$times) : LocalTime
    {
        if (! $times) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $max = LocalTime::min();

        foreach ($times as $time) {
            if ($time->isAfter($max)) {
                $max = $time;
            }
        }

        return $max;
    }

    /**
     * @return int
     */
    public function getHour() : int
    {
        return $this->hour;
    }

    /**
     * @return int
     */
    public function getMinute() : int
    {
        return $this->minute;
    }

    /**
     * @return int
     */
    public function getSecond() : int
    {
        return $this->second;
    }

    /**
     * @return int
     */
    public function getNano() : int
    {
        return $this->nano;
    }

    /**
     * Returns a copy of this LocalTime with the hour-of-day value altered.
     *
     * @param int $hour The new hour-of-day.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the hour-of-day if not valid.
     */
    public function withHour(int $hour) : LocalTime
    {
        if ($hour === $this->hour) {
            return $this;
        }

        Field\HourOfDay::check($hour);

        return new LocalTime($hour, $this->minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the minute-of-hour value altered.
     *
     * @param int $minute The new minute-of-hour.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the minute-of-hour if not valid.
     */
    public function withMinute(int $minute) : LocalTime
    {
        if ($minute === $this->minute) {
            return $this;
        }

        Field\MinuteOfHour::check($minute);

        return new LocalTime($this->hour, $minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the second-of-minute value altered.
     *
     * @param int $second The new second-of-minute.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the second-of-minute if not valid.
     */
    public function withSecond(int $second) : LocalTime
    {
        if ($second === $this->second) {
            return $this;
        }

        Field\SecondOfMinute::check($second);

        return new LocalTime($this->hour, $this->minute, $second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the nano-of-second value altered.
     *
     * @param int $nano The new nano-of-second.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the nano-of-second if not valid.
     */
    public function withNano(int $nano) : LocalTime
    {
        if ($nano === $this->nano) {
            return $this;
        }

        Field\NanoOfSecond::check($nano);

        return new LocalTime($this->hour, $this->minute, $this->second, $nano);
    }

    /**
     * Returns a copy of this LocalTime with the specific duration added.
     *
     * The calculation wraps around midnight.
     *
     * @param Duration $duration
     *
     * @return LocalTime
     */
    public function plusDuration(Duration $duration) : LocalTime
    {
        return $this
            ->plusSeconds($duration->getSeconds())
            ->plusNanos($duration->getNanos());
    }

    /**
     * Returns a copy of this LocalTime with the specified period in hours added.
     *
     * This adds the specified number of hours to this time, returning a new time.
     * The calculation wraps around midnight.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $hours The hours to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the hours added.
     */
    public function plusHours(int $hours) : LocalTime
    {
        if ($hours === 0) {
            return $this;
        }

        $hour = (($hours % self::HOURS_PER_DAY) + $this->hour + self::HOURS_PER_DAY) % self::HOURS_PER_DAY;

        return new LocalTime($hour, $this->minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in minutes added.
     *
     * This adds the specified number of minutes to this time, returning a new time.
     * The calculation wraps around midnight.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $minutes The minutes to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the minutes added.
     */
    public function plusMinutes(int $minutes) : LocalTime
    {
        if ($minutes === 0) {
            return $this;
        }

        $mofd = $this->hour * self::MINUTES_PER_HOUR + $this->minute;
        $newMofd = (($minutes % self::MINUTES_PER_DAY) + $mofd + self::MINUTES_PER_DAY) % self::MINUTES_PER_DAY;

        if ($mofd === $newMofd) {
            return $this;
        }

        $hour = \intdiv($newMofd, self::MINUTES_PER_HOUR);
        $minute = $newMofd % self::MINUTES_PER_HOUR;

        return new LocalTime($hour, $minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in seconds added.
     *
     * @param int $seconds The seconds to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the seconds added.
     */
    public function plusSeconds(int $seconds) : LocalTime
    {
        if ($seconds === 0) {
            return $this;
        }

        $sofd = $this->hour * self::SECONDS_PER_HOUR + $this->minute * self::SECONDS_PER_MINUTE + $this->second;
        $newSofd = (($seconds % self::SECONDS_PER_DAY) + $sofd + self::SECONDS_PER_DAY) % self::SECONDS_PER_DAY;

        if ($sofd === $newSofd) {
            return $this;
        }

        $hour = \intdiv($newSofd, self::SECONDS_PER_HOUR);
        $minute = \intdiv($newSofd, self::SECONDS_PER_MINUTE) % self::MINUTES_PER_HOUR;
        $second = $newSofd % self::SECONDS_PER_MINUTE;

        return new LocalTime($hour, $minute, $second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in nanoseconds added.
     *
     * @param int $nanos The seconds to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the nanoseconds added.
     *
     * @throws DateTimeException
     */
    public function plusNanos(int $nanos) : LocalTime
    {
        if ($nanos === 0) {
            return $this;
        }

        $divBase = Math::floorDiv($this->nano, LocalTime::NANOS_PER_SECOND);
        $modBase = Math::floorMod($this->nano, LocalTime::NANOS_PER_SECOND);

        $divPlus = Math::floorDiv($nanos, LocalTime::NANOS_PER_SECOND);
        $modPlus = Math::floorMod($nanos, LocalTime::NANOS_PER_SECOND);

        $diffSeconds = $divBase + $divPlus;
        $nano = $modBase + $modPlus;

        if ($nano >= LocalTime::NANOS_PER_SECOND) {
            $nano -= LocalTime::NANOS_PER_SECOND;
            $diffSeconds++;
        }

        return $this->withNano($nano)->plusSeconds($diffSeconds);
    }

    /**
     * Returns a copy of this LocalTime with the specific duration subtracted.
     *
     * The calculation wraps around midnight.
     *
     * @param Duration $duration
     *
     * @return LocalTime
     */
    public function minusDuration(Duration $duration) : LocalTime
    {
        return $this->plusDuration($duration->negated());
    }

    /**
     * @param int $hours
     *
     * @return LocalTime
     */
    public function minusHours(int $hours) : LocalTime
    {
        return $this->plusHours(- $hours);
    }

    /**
     * @param int $minutes
     *
     * @return LocalTime
     */
    public function minusMinutes(int $minutes) : LocalTime
    {
        return $this->plusMinutes(- $minutes);
    }

    /**
     * @param int $seconds
     *
     * @return LocalTime
     */
    public function minusSeconds(int $seconds) : LocalTime
    {
        return $this->plusSeconds(- $seconds);
    }

    /**
     * @param int $nanos
     *
     * @return LocalTime
     */
    public function minusNanos(int $nanos) : LocalTime
    {
        return $this->plusNanos(-$nanos);
    }

    /**
     * Compares this LocalTime with another.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return int [-1,0,1] If this time is before, on, or after the given time.
     */
    public function compareTo(LocalTime $that) : int
    {
        $seconds = $this->toSecondOfDay() - $that->toSecondOfDay();

        if ($seconds !== 0) {
            return $seconds > 0 ? 1 : -1;
        }

        $nanos = $this->nano - $that->nano;

        if ($nanos !== 0) {
            return $nanos > 0 ? 1 : -1;
        }

        return 0;
    }

    /**
     * Checks if this LocalTime is equal to the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return bool
     */
    public function isEqualTo(LocalTime $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Checks if this LocalTime is less than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return bool
     */
    public function isBefore(LocalTime $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Checks if this LocalTime is less than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return bool
     */
    public function isBeforeOrEqualTo(LocalTime $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * Checks if this LocalTime is greater than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return bool
     */
    public function isAfter(LocalTime $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Checks if this LocalTime is greater than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return bool
     */
    public function isAfterOrEqualTo(LocalTime $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Combines this time with a date to create a LocalDateTime.
     *
     * @param LocalDate $date
     *
     * @return LocalDateTime
     */
    public function atDate(LocalDate $date) : LocalDateTime
    {
        return new LocalDateTime($date, $this);
    }

    /**
     * Returns the time as seconds of day, from 0 to 24 * 60 * 60 - 1.
     *
     * This does not include the nanoseconds.
     *
     * @return int
     */
    public function toSecondOfDay() : int
    {
        return $this->hour * self::SECONDS_PER_HOUR
            + $this->minute * self::SECONDS_PER_MINUTE
            + $this->second;
    }

    /**
     * Returns this time as a string, such as 10:15.
     *
     * The output will be one of the following ISO-8601 formats:
     *
     * * `HH:mm`
     * * `HH:mm:ss`
     * * `HH:mm:ss.nnn`
     *
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     * The nanoseconds value, if present, can be 0 to 9 digits.
     *
     * @return string A string representation of this time.
     */
    public function __toString() : string
    {
        if ($this->nano === 0) {
            if ($this->second === 0) {
                return \sprintf('%02u:%02u', $this->hour, $this->minute);
            } else {
                return \sprintf('%02u:%02u:%02u', $this->hour, $this->minute, $this->second);
            }
        }

        $nanos = \rtrim(\sprintf('%09u', $this->nano), '0');

        return \sprintf('%02u:%02u:%02u.%s', $this->hour, $this->minute, $this->second, $nanos);
    }
}
