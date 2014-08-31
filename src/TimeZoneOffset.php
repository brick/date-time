<?php

namespace Brick\DateTime;

use Brick\DateTime\Field\DateTimeField;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Cast;

/**
 * A time-zone offset from Greenwich/UTC, such as `+02:00`.
 */
class TimeZoneOffset extends TimeZone
{
    /**
     * @var integer
     */
    private $totalSeconds;

    /**
     * The string representation of this time-zone offset.
     *
     * This is generated on-the-fly, and will be null before the first call to getId().
     *
     * @var string|null
     */
    private $id = null;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param integer $totalSeconds The total offset in seconds, validated as an integer from -64800 to +64800.
     */
    private function __construct($totalSeconds)
    {
        $this->totalSeconds = $totalSeconds;
    }

    /**
     * Obtains an instance of `TimeZoneOffset` using an offset in hours, minutes and seconds.
     *
     * The total number of seconds must not exceed 64,800 seconds.
     *
     * @param integer $hours   The time-zone offset in hours.
     * @param integer $minutes The time-zone offset in minutes, from 0 to 59, sign matching hours.
     * @param integer $seconds The time-zone offset in seconds, from 0 to 59, sign matching hours and minute.
     *
     * @return TimeZoneOffset
     *
     * @throws DateTimeException If the values are not in range or the signs don't match.
     */
    public static function of($hours, $minutes = 0, $seconds = 0)
    {
        $hours = Cast::toInteger($hours);
        $minutes = Cast::toInteger($minutes);
        $seconds = Cast::toInteger($seconds);

        Field\TimeZoneOffsetHour::check($hours);
        Field\TimeZoneOffsetMinute::check($minutes);
        Field\TimeZoneOffsetSecond::check($seconds);

        $err = ($hours > 0 && ($minutes < 0 || $seconds < 0))
            || ($hours < 0 && ($minutes > 0 || $seconds > 0))
            || ($minutes > 0 && $seconds < 0)
            || ($minutes < 0 && $seconds > 0);

        if ($err) {
            throw new DateTimeException('Time zone offset hours, minutes and seconds must have the same sign');
        }

        $totalSeconds = $hours * LocalTime::SECONDS_PER_HOUR
            + $minutes * LocalTime::SECONDS_PER_MINUTE
            + $seconds;

        Field\TimeZoneOffsetTotalSeconds::check($totalSeconds);

        return new TimeZoneOffset($totalSeconds);
    }

    /**
     * Obtains an instance of `TimeZoneOffset` specifying the total offset in seconds.
     *
     * The offset must be in the range `-18:00` to `+18:00`, which corresponds to -64800 to +64800.
     *
     * @param integer $totalSeconds The total offset in seconds.
     *
     * @return TimeZoneOffset
     *
     * @throws DateTimeException
     */
    public static function ofTotalSeconds($totalSeconds)
    {
        $totalSeconds = Cast::toInteger($totalSeconds);

        Field\TimeZoneOffsetTotalSeconds::check($totalSeconds);

        return new TimeZoneOffset($totalSeconds);
    }

    /**
     * @return TimeZoneOffset
     */
    public static function utc()
    {
        return new TimeZoneOffset(0);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return TimeZoneOffset
     *
     * @throws DateTimeException      If the offset is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result)
    {
        $sign = $result->getField(Field\TimeZoneOffsetSign::NAME);

        if ($sign === 'Z' || $sign === 'z') {
            return TimeZoneOffset::utc();
        }

        $hour   = $result->getField(Field\TimeZoneOffsetHour::NAME);
        $minute = $result->getField(Field\TimeZoneOffsetMinute::NAME);
        $second = $result->getOptionalField(Field\TimeZoneOffsetSecond::NAME);

        $hour   = (int) $hour;
        $minute = (int) $minute;
        $second = (int) $second;

        if ($sign === '-') {
            $hour   = -$hour;
            $minute = -$minute;
            $second = -$second;
        }

        return self::of($hour, $minute, $second);
    }

    /**
     * Parses a time-zone offset.
     *
     * The following ISO 8601 formats are accepted:
     * 
     * * `Z` - for UTC
     * * `±hh:mm`
     * * `±hh:mm:ss`
     *
     * Note that ± means either the plus or minus symbol.
     *
     * @param string              $text
     * @param DateTimeParser|null $parser
     *
     * @return TimeZoneOffset
     *
     * @throws DateTimeParseException
     */
    public static function parse($text, DateTimeParser $parser = null)
    {
        if (! $parser) {
            $parser = IsoParsers::timeZoneOffset();
        }

        return TimeZoneOffset::from($parser->parse($text));
    }

    /**
     * Returns the total time-zone offset in seconds.
     *
     * This is the primary way to access the offset amount.
     * It returns the total of the hours, minutes and seconds fields as a
     * single offset that can be added to a time.
     *
     * @return integer The total time-zone offset amount in seconds.
     */
    public function getTotalSeconds()
    {
        return $this->totalSeconds;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        if ($this->id === null) {
            if ($this->totalSeconds < 0) {
                $this->id = '-' . LocalTime::ofSecondOfDay(- $this->totalSeconds);
            } elseif ($this->totalSeconds > 0) {
                $this->id = '+' . LocalTime::ofSecondOfDay($this->totalSeconds);
            } else {
                $this->id = 'Z';
            }
        }

        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getOffset(ReadableInstant $pointInTime)
    {
        return $this->totalSeconds;
    }

    /**
     * {@inheritdoc}
     */
    public function toDateTimeZone()
    {
        return new \DateTimeZone($this->getId());
    }
}
