<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use DateTimeZone;

/**
 * A time-zone offset from Greenwich/UTC, such as `+02:00`.
 */
final class TimeZoneOffset extends TimeZone
{
    /**
     * The string representation of this time-zone offset.
     *
     * This is generated on-the-fly, and will be null before the first call to getId().
     *
     * @psalm-var non-empty-string|null
     */
    private ?string $id = null;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param int $totalSeconds The total offset in seconds, validated from -64800 to +64800.
     */
    private function __construct(
        private readonly int $totalSeconds,
    ) {
    }

    /**
     * Obtains an instance of `TimeZoneOffset` using an offset in hours, minutes and seconds. Seconds are only supported since PHP 8.1.7.
     *
     * The total number of seconds must not exceed 64,800 seconds.
     *
     * @param int $hours   The time-zone offset in hours.
     * @param int $minutes The time-zone offset in minutes, from 0 to 59, sign matching hours.
     * @param int $seconds The time-zone offset in seconds, from 0 to 59, sign matching hours and minute. This is only supported since PHP 8.1.7.
     *
     * @throws DateTimeException If the values are not in range or the signs don't match.
     */
    public static function of(int $hours, int $minutes = 0, int $seconds = 0): TimeZoneOffset
    {
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
     * @param int $totalSeconds The total offset in seconds.
     *
     * @throws DateTimeException
     */
    public static function ofTotalSeconds(int $totalSeconds): TimeZoneOffset
    {
        Field\TimeZoneOffsetTotalSeconds::check($totalSeconds);

        return new TimeZoneOffset($totalSeconds);
    }

    public static function utc(): TimeZoneOffset
    {
        /** @var TimeZoneOffset|null $utc */
        static $utc = null;

        return $utc ??= new TimeZoneOffset(0);
    }

    /**
     * @throws DateTimeException      If the offset is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): TimeZoneOffset
    {
        $sign = $result->getField(Field\TimeZoneOffsetSign::NAME);

        if ($sign === 'Z' || $sign === 'z') {
            return TimeZoneOffset::utc();
        }

        $hour = $result->getField(Field\TimeZoneOffsetHour::NAME);
        $minute = $result->getField(Field\TimeZoneOffsetMinute::NAME);
        $second = $result->getOptionalField(Field\TimeZoneOffsetSecond::NAME);

        $hour = (int) $hour;
        $minute = (int) $minute;
        $second = (int) $second;

        if ($sign === '-') {
            $hour = -$hour;
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
     * @throws DateTimeParseException
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): TimeZoneOffset
    {
        if ($parser === null) {
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
     * @return int The total time-zone offset amount in seconds.
     */
    public function getTotalSeconds(): int
    {
        return $this->totalSeconds;
    }

    public function getId(): string
    {
        if ($this->id === null) {
            if ($this->totalSeconds < 0) {
                $this->id = '-' . LocalTime::ofSecondOfDay(-$this->totalSeconds);
            } elseif ($this->totalSeconds > 0) {
                $this->id = '+' . LocalTime::ofSecondOfDay($this->totalSeconds);
            } else {
                $this->id = 'Z';
            }
        }

        return $this->id;
    }

    public function getOffset(Instant $pointInTime): int
    {
        return $this->totalSeconds;
    }

    public function toNativeDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone($this->getId());
    }
}
