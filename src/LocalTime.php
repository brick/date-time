<?php

namespace Brick\DateTime;

use Brick\DateTime\Field\HourOfDay;
use Brick\DateTime\Field\MinuteOfHour;
use Brick\DateTime\Field\SecondOfMinute;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;
use Brick\DateTime\Utility\Cast;
use Brick\Locale\Locale;

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

    /**
     * The hour, in the range 0 to 23.
     *
     * @var integer
     */
    private $hour;

    /**
     * The minute, in the range 0 to 59.
     *
     * @var integer
     */
    private $minute;

    /**
     * The second, in the range 0 to 59.
     *
     * @var integer
     */
    private $second;

    /**
     * The nanosecond, in the range 0 to 999,999,999.
     *
     * @var integer
     */
    private $nano;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param integer $hour   The hour-of-day, validated as an integer in the range 0 to 23.
     * @param integer $minute The minute-of-hour, validated as an integer in the range 0 to 59.
     * @param integer $second The second-of-minute, validated as an integer in the range 0 to 59.
     * @param integer $nano   The nano-of-second, validated as an integer in the range 0 to 999,999,999.
     */
    private function __construct($hour, $minute, $second, $nano)
    {
        $this->hour   = $hour;
        $this->minute = $minute;
        $this->second = $second;
        $this->nano   = $nano;
    }

    /**
     * @param integer $hour
     * @param integer $minute
     * @param integer $second
     * @param integer $nano
     *
     * @return LocalTime
     *
     * @throws DateTimeException
     */
    public static function of($hour, $minute, $second = 0, $nano = 0)
    {
        $hour   = Cast::toInteger($hour);
        $minute = Cast::toInteger($minute);
        $second = Cast::toInteger($second);
        $nano   = Cast::toInteger($nano);

        Field\HourOfDay::check($hour);
        Field\MinuteOfHour::check($minute);
        Field\SecondOfMinute::check($second);
        Field\NanoOfSecond::check($nano);

        return new LocalTime($hour, $minute, $second, $nano);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return LocalTime
     *
     * @throws DateTimeException      If the time is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result)
    {
        $hour     = $result->getField(HourOfDay::NAME);
        $minute   = $result->getField(MinuteOfHour::NAME);
        $second   = $result->getOptionalField(SecondOfMinute::NAME);
        $fraction = $result->getOptionalField(Field\FractionOfSecond::NAME);

        $nano = substr($fraction . '000000000', 0, 9);

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
    public static function parse($text, DateTimeParser $parser = null)
    {
        if (! $parser) {
            $parser = IsoParsers::localTime();
        }

        return LocalTime::from($parser->parse($text));
    }

    /**
     * Creates a LocalTime instance from a number of seconds since midnight.
     *
     * @param integer $secondOfDay
     * @param integer $nanoOfSecond
     *
     * @return LocalTime
     * @throws DateTimeException
     */
    public static function ofSecondOfDay($secondOfDay, $nanoOfSecond = 0)
    {
        $secondOfDay = Cast::toInteger($secondOfDay);
        $nanoOfSecond = Cast::toInteger($nanoOfSecond);

        Field\SecondOfDay::check($secondOfDay);
        Field\NanoOfSecond::check($nanoOfSecond);

        $hours = Math::div($secondOfDay, self::SECONDS_PER_HOUR);
        $secondOfDay -= $hours * self::SECONDS_PER_HOUR;
        $minutes = Math::div($secondOfDay, self::SECONDS_PER_MINUTE);
        $secondOfDay -= $minutes * self::SECONDS_PER_MINUTE;

        return new LocalTime($hours, $minutes, $secondOfDay, $nanoOfSecond);
    }

    /**
     * Returns the current time, in the given time zone.
     *
     * @param TimeZone $timeZone
     *
     * @return LocalTime
     */
    public static function now(TimeZone $timeZone)
    {
        return ZonedDateTime::now($timeZone)->getTime();
    }

    /**
     * @return LocalTime
     */
    public static function midnight()
    {
        return new LocalTime(0, 0, 0, 0);
    }

    /**
     * @return LocalTime
     */
    public static function noon()
    {
        return new LocalTime(12, 0, 0, 0);
    }

    /**
     * Returns the smallest possible value for LocalTime.
     *
     * @return LocalTime
     */
    public static function min()
    {
        return new LocalTime(0, 0, 0, 0);
    }

    /**
     * Returns the highest possible value for LocalTime.
     *
     * @return LocalTime
     */
    public static function max()
    {
        return new LocalTime(23, 59, 59, 999999999);
    }

    /**
     * @return integer
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * @return integer
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @return integer
     */
    public function getSecond()
    {
        return $this->second;
    }

    /**
     * @return integer
     */
    public function getNano()
    {
        return $this->nano;
    }

    /**
     * Returns a copy of this LocalTime with the hour-of-day value altered.
     *
     * @param integer $hour The new hour-of-day.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the hour-of-day if not valid.
     */
    public function withHour($hour)
    {
        $hour = Cast::toInteger($hour);

        if ($hour === $this->hour) {
            return $this;
        }

        Field\HourOfDay::check($hour);

        return new LocalTime($hour, $this->minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the minute-of-hour value altered.
     *
     * @param integer $minute The new minute-of-hour.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the minute-of-hour if not valid.
     */
    public function withMinute($minute)
    {
        $minute = Cast::toInteger($minute);

        if ($minute === $this->minute) {
            return $this;
        }

        Field\MinuteOfHour::check($minute);

        return new LocalTime($this->hour, $minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the second-of-minute value altered.
     *
     * @param integer $second The new second-of-minute.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the second-of-minute if not valid.
     */
    public function withSecond($second)
    {
        $second = Cast::toInteger($second);

        if ($second === $this->second) {
            return $this;
        }

        Field\SecondOfMinute::check($second);

        return new LocalTime($this->hour, $this->minute, $second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the nano-of-second value altered.
     *
     * @param integer $nano The new nano-of-second.
     *
     * @return LocalTime
     *
     * @throws DateTimeException If the nano-of-second if not valid.
     */
    public function withNano($nano)
    {
        $nano = Cast::toInteger($nano);

        if ($nano === $this->nano) {
            return $this;
        }

        Field\NanoOfSecond::check($nano);

        return new LocalTime($this->hour, $this->minute, $this->second, $nano);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in hours added.
     *
     * This adds the specified number of hours to this time, returning a new time.
     * The calculation wraps around midnight.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param integer $hours The hours to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the hours added.
     */
    public function plusHours($hours)
    {
        $hours = Cast::toInteger($hours);

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
     * @param integer $minutes The minutes to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the minutes added.
     */
    public function plusMinutes($minutes)
    {
        $minutes = Cast::toInteger($minutes);

        if ($minutes === 0) {
            return $this;
        }

        $mofd = $this->hour * self::MINUTES_PER_HOUR + $this->minute;
        $newMofd = (($minutes % self::MINUTES_PER_DAY) + $mofd + self::MINUTES_PER_DAY) % self::MINUTES_PER_DAY;

        if ($mofd === $newMofd) {
            return $this;
        }

        $hour = Math::div($newMofd, self::MINUTES_PER_HOUR);
        $minute = $newMofd % self::MINUTES_PER_HOUR;

        return new LocalTime($hour, $minute, $this->second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in seconds added.
     *
     * @param integer $seconds The seconds to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the seconds added.
     */
    public function plusSeconds($seconds)
    {
        $seconds = Cast::toInteger($seconds);

        if ($seconds === 0) {
            return $this;
        }

        $sofd = $this->hour * self::SECONDS_PER_HOUR + $this->minute * self::SECONDS_PER_MINUTE + $this->second;
        $newSofd = (($seconds % self::SECONDS_PER_DAY) + $sofd + self::SECONDS_PER_DAY) % self::SECONDS_PER_DAY;

        if ($sofd === $newSofd) {
            return $this;
        }

        $hour = Math::div($newSofd, self::SECONDS_PER_HOUR);
        $minute = Math::div($newSofd, self::SECONDS_PER_MINUTE) % self::MINUTES_PER_HOUR;
        $second = $newSofd % self::SECONDS_PER_MINUTE;

        return new LocalTime($hour, $minute, $second, $this->nano);
    }

    /**
     * Returns a copy of this LocalTime with the specified period in nanoseconds added.
     *
     * @param integer $nanos The seconds to add, may be negative.
     *
     * @return LocalTime A LocalTime based on this time with the nanoseconds added.
     */
    public function plusNanos($nanos)
    {
        $nanos = Cast::toInteger($nanos);

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
     * @param integer $hours
     *
     * @return LocalTime
     */
    public function minusHours($hours)
    {
        return $this->plusHours(- $hours);
    }

    /**
     * @param integer $minutes
     *
     * @return LocalTime
     */
    public function minusMinutes($minutes)
    {
        return $this->plusMinutes(- $minutes);
    }

    /**
     * @param integer $seconds
     *
     * @return LocalTime
     */
    public function minusSeconds($seconds)
    {
        return $this->plusSeconds(- $seconds);
    }

    /**
     * @param integer $nanos
     *
     * @return LocalTime
     */
    public function minusNanos($nanos)
    {
        return $this->plusNanos(-$nanos);
    }

    /**
     * Compares this LocalTime with another.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return integer [-1,0,1] If this time is before, on, or after the given time.
     */
    public function compareTo(LocalTime $that)
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
     * @return boolean
     */
    public function isEqualTo(LocalTime $that)
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Checks if this LocalTime is greater than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return boolean
     */
    public function isAfter(LocalTime $that)
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Checks if this LocalTime is less than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return boolean
     */
    public function isBefore(LocalTime $that)
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns a local date-time formed from this time at the specified date.
     *
     * @param LocalDate $date
     *
     * @return LocalDateTime
     */
    public function atDate(LocalDate $date)
    {
        return LocalDateTime::ofDateTime($date, $this);
    }

    /**
     * Returns the time as seconds of day, from 0 to 24 * 60 * 60 - 1.
     *
     * This does not include the nanoseconds.
     *
     * @return integer
     */
    public function toSecondOfDay()
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
    public function __toString()
    {
        if ($this->nano === 0) {
            if ($this->second === 0) {
                return sprintf('%02u:%02u', $this->hour, $this->minute);
            } else {
                return sprintf('%02u:%02u:%02u', $this->hour, $this->minute, $this->second);
            }
        }

        $nanos = rtrim(sprintf('%09u', $this->nano), '0');

        return sprintf('%02u:%02u:%02u.%s', $this->hour, $this->minute, $this->second, $nanos);
    }

    /**
     * @todo only supports HH:MM right now. Automatically switch between HH:MM & HH:MM:SS depending on SS==00?
     * @todo same for nanos?
     *
     * @param \Brick\Locale\Locale $locale
     *
     * @return string
     */
    public function format(Locale $locale)
    {
        $formatter = new \IntlDateFormatter((string) $locale, \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT);
        $formatter->setTimeZone('UTC');

        $datetime = new \DateTime(null, new \DateTimeZone('UTC'));
        $datetime->setTime($this->hour, $this->minute, $this->second);

        return $formatter->format($datetime);
    }

    /**
     * Returns the smallest LocalTime of an array.
     *
     * @param LocalTime[] $times An array of LocalTime objects.
     *
     * @return LocalTime The smallest LocalTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(array $times)
    {
        if (count($times) == 0) {
            throw new DateTimeException('LocalTime::minOf() does not accept less than 1 parameter');
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
     * Returns the highest LocalTime of an array.
     *
     * @param LocalTime[] $times An array of LocalTime objects.
     *
     * @return LocalTime The highest LocalTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(array $times)
    {
        if (count($times) == 0) {
            throw new DateTimeException('LocalTime::maxOf() does not accept less than 1 parameter');
        }

        $max = LocalTime::min();

        foreach ($times as $time) {
            if ($time->isAfter($max)) {
                $max = $time;
            }
        }

        return $max;
    }
}
