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
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Stringable;

use function intdiv;
use function rtrim;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * A time without a time-zone in the ISO-8601 calendar system, such as 10:15:30.
 *
 * This class is immutable.
 */
final class LocalTime implements JsonSerializable, Stringable
{
    public const MONTHS_PER_YEAR = 12;
    public const DAYS_PER_WEEK = 7;
    public const HOURS_PER_DAY = 24;
    public const MINUTES_PER_HOUR = 60;
    public const MINUTES_PER_DAY = 1440;
    public const SECONDS_PER_MINUTE = 60;
    public const SECONDS_PER_HOUR = 3600;
    public const SECONDS_PER_DAY = 86400;
    public const NANOS_PER_SECOND = 1_000_000_000;
    public const NANOS_PER_MILLI = 1_000_000;
    public const MILLIS_PER_SECOND = 1000;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param int $hour   The hour-of-day, validated in the range 0 to 23.
     * @param int $minute The minute-of-hour, validated in the range 0 to 59.
     * @param int $second The second-of-minute, validated in the range 0 to 59.
     * @param int $nano   The nano-of-second, validated in the range 0 to 999,999,999.
     */
    private function __construct(
        private readonly int $hour,
        private readonly int $minute,
        private readonly int $second,
        private readonly int $nano,
    ) {
    }

    /**
     * @param int $hour   The hour, from 0 to 23.
     * @param int $minute The minute, from 0 to 59.
     * @param int $second The second, from 0 to 59.
     * @param int $nano   The nano-of-second, from 0 to 999,999,999.
     *
     * @throws DateTimeException
     */
    public static function of(int $hour, int $minute, int $second = 0, int $nano = 0): LocalTime
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
     * @throws DateTimeException
     */
    public static function ofSecondOfDay(int $secondOfDay, int $nanoOfSecond = 0): LocalTime
    {
        Field\SecondOfDay::check($secondOfDay);
        Field\NanoOfSecond::check($nanoOfSecond);

        $hours = intdiv($secondOfDay, self::SECONDS_PER_HOUR);
        $secondOfDay -= $hours * self::SECONDS_PER_HOUR;
        $minutes = intdiv($secondOfDay, self::SECONDS_PER_MINUTE);
        $secondOfDay -= $minutes * self::SECONDS_PER_MINUTE;

        return new LocalTime($hours, $minutes, $secondOfDay, $nanoOfSecond);
    }

    /**
     * @throws DateTimeException      If the time is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): LocalTime
    {
        $hour = $result->getField(HourOfDay::NAME);
        $minute = $result->getField(MinuteOfHour::NAME);
        $second = $result->getOptionalField(SecondOfMinute::NAME);
        $fraction = $result->getOptionalField(Field\FractionOfSecond::NAME);

        $nano = str_pad($fraction, 9, '0');

        return LocalTime::of((int) $hour, (int) $minute, (int) $second, (int) $nano);
    }

    /**
     * Obtains an instance of `LocalTime` from a text string.
     *
     * @param string              $text   The text to parse, such as `10:15`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the time is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): LocalTime
    {
        if ($parser === null) {
            $parser = IsoParsers::localTime();
        }

        return LocalTime::from($parser->parse($text));
    }

    /**
     * Creates a LocalTime from a native DateTime or DateTimeImmutable object.
     */
    public static function fromNativeDateTime(DateTimeInterface $dateTime): LocalTime
    {
        return new LocalTime(
            (int) $dateTime->format('G'),
            (int) $dateTime->format('i'),
            (int) $dateTime->format('s'),
            1000 * (int) $dateTime->format('u'),
        );
    }

    /**
     * Returns the current local time in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): LocalTime
    {
        return ZonedDateTime::now($timeZone, $clock)->getTime();
    }

    public static function midnight(): LocalTime
    {
        return self::min();
    }

    public static function noon(): LocalTime
    {
        /** @var LocalTime|null $noon */
        static $noon = null;

        return $noon ??= new LocalTime(12, 0, 0, 0);
    }

    /**
     * Returns the smallest possible value for LocalTime.
     */
    public static function min(): LocalTime
    {
        /** @var LocalTime|null $min */
        static $min = null;

        return $min ??= new LocalTime(0, 0, 0, 0);
    }

    /**
     * Returns the highest possible value for LocalTime.
     */
    public static function max(): LocalTime
    {
        /** @var LocalTime|null $max */
        static $max = null;

        return $max ??= new LocalTime(23, 59, 59, 999_999_999);
    }

    /**
     * Returns the smallest LocalTime among the given values.
     *
     * @param LocalTime ...$times The LocalTime objects to compare.
     *
     * @return LocalTime The earliest LocalTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(LocalTime ...$times): LocalTime
    {
        if ($times === []) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $min = null;

        foreach ($times as $time) {
            if ($min === null || $time->isBefore($min)) {
                $min = $time;
            }
        }

        return $min;
    }

    /**
     * Returns the highest LocalTime among the given values.
     *
     * @param LocalTime ...$times The LocalTime objects to compare.
     *
     * @return LocalTime The latest LocalTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(LocalTime ...$times): LocalTime
    {
        if ($times === []) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $max = null;

        foreach ($times as $time) {
            if ($max === null || $time->isAfter($max)) {
                $max = $time;
            }
        }

        return $max;
    }

    public function getHour(): int
    {
        return $this->hour;
    }

    public function getMinute(): int
    {
        return $this->minute;
    }

    public function getSecond(): int
    {
        return $this->second;
    }

    public function getNano(): int
    {
        return $this->nano;
    }

    /**
     * Returns a copy of this LocalTime with the hour-of-day value altered.
     *
     * @param int $hour The new hour-of-day.
     *
     * @throws DateTimeException If the hour-of-day if not valid.
     */
    public function withHour(int $hour): LocalTime
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
     * @throws DateTimeException If the minute-of-hour if not valid.
     */
    public function withMinute(int $minute): LocalTime
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
     * @throws DateTimeException If the second-of-minute if not valid.
     */
    public function withSecond(int $second): LocalTime
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
     * @throws DateTimeException If the nano-of-second if not valid.
     */
    public function withNano(int $nano): LocalTime
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
     */
    public function plusDuration(Duration $duration): LocalTime
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
    public function plusHours(int $hours): LocalTime
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
    public function plusMinutes(int $minutes): LocalTime
    {
        if ($minutes === 0) {
            return $this;
        }

        $mofd = $this->hour * self::MINUTES_PER_HOUR + $this->minute;
        $newMofd = (($minutes % self::MINUTES_PER_DAY) + $mofd + self::MINUTES_PER_DAY) % self::MINUTES_PER_DAY;

        if ($mofd === $newMofd) {
            return $this;
        }

        $hour = intdiv($newMofd, self::MINUTES_PER_HOUR);
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
    public function plusSeconds(int $seconds): LocalTime
    {
        if ($seconds === 0) {
            return $this;
        }

        $sofd = $this->hour * self::SECONDS_PER_HOUR + $this->minute * self::SECONDS_PER_MINUTE + $this->second;
        $newSofd = (($seconds % self::SECONDS_PER_DAY) + $sofd + self::SECONDS_PER_DAY) % self::SECONDS_PER_DAY;

        if ($sofd === $newSofd) {
            return $this;
        }

        $hour = intdiv($newSofd, self::SECONDS_PER_HOUR);
        $minute = intdiv($newSofd, self::SECONDS_PER_MINUTE) % self::MINUTES_PER_HOUR;
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
    public function plusNanos(int $nanos): LocalTime
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
     */
    public function minusDuration(Duration $duration): LocalTime
    {
        return $this->plusDuration($duration->negated());
    }

    public function minusHours(int $hours): LocalTime
    {
        return $this->plusHours(-$hours);
    }

    public function minusMinutes(int $minutes): LocalTime
    {
        return $this->plusMinutes(-$minutes);
    }

    public function minusSeconds(int $seconds): LocalTime
    {
        return $this->plusSeconds(-$seconds);
    }

    public function minusNanos(int $nanos): LocalTime
    {
        return $this->plusNanos(-$nanos);
    }

    /**
     * Compares this LocalTime with another.
     *
     * @param LocalTime $that The time to compare to.
     *
     * @return int [-1,0,1] If this time is before, on, or after the given time.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(LocalTime $that): int
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
     */
    public function isEqualTo(LocalTime $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Checks if this LocalTime is less than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     */
    public function isBefore(LocalTime $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Checks if this LocalTime is less than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     */
    public function isBeforeOrEqualTo(LocalTime $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * Checks if this LocalTime is greater than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     */
    public function isAfter(LocalTime $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Checks if this LocalTime is greater than the specified time.
     *
     * @param LocalTime $that The time to compare to.
     */
    public function isAfterOrEqualTo(LocalTime $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Combines this time with a date to create a LocalDateTime.
     */
    public function atDate(LocalDate $date): LocalDateTime
    {
        return new LocalDateTime($date, $this);
    }

    /**
     * Returns the time as seconds of day, from 0 to 24 * 60 * 60 - 1.
     *
     * This does not include the nanoseconds.
     */
    public function toSecondOfDay(): int
    {
        return $this->hour * self::SECONDS_PER_HOUR
            + $this->minute * self::SECONDS_PER_MINUTE
            + $this->second;
    }

    /**
     * Converts this LocalTime to a native DateTime object.
     *
     * The result is a DateTime with date 0000-01-01 in the UTC time-zone.
     *
     * Note that the native DateTime object supports a precision up to the microsecond,
     * so the nanoseconds are rounded down to the nearest microsecond.
     */
    public function toNativeDateTime(): DateTime
    {
        return $this->atDate(LocalDate::of(0, Month::JANUARY, 1))->toNativeDateTime();
    }

    /**
     * Converts this LocalTime to a native DateTimeImmutable object.
     *
     * The result is a DateTimeImmutable with date 0000-01-01 in the UTC time-zone.
     *
     * Note that the native DateTimeImmutable object supports a precision up to the microsecond,
     * so the nanoseconds are rounded down to the nearest microsecond.
     */
    public function toNativeDateTimeImmutable(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->toNativeDateTime());
    }

    /**
     * Serializes as a string using {@see LocalTime::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this time.
     *
     * The output will be one of the following formats:
     *
     * * `HH:mm`
     * * `HH:mm:ss`
     * * `HH:mm:ss.nnn`
     *
     * The format used will be the shortest that outputs the full value of
     * the time where the omitted parts are implied to be zero.
     * The nanoseconds value, if present, can be 0 to 9 digits.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        // This code is optimized for high performance
        return ($this->hour < 10 ? '0' . $this->hour : $this->hour)
            . ':'
            . ($this->minute < 10 ? '0' . $this->minute : $this->minute)
            . ($this->second !== 0 || $this->nano !== 0 ? ':' . ($this->second < 10 ? '0' . $this->second : $this->second) : '')
            . ($this->nano !== 0 ? '.' . rtrim(str_pad((string) $this->nano, 9, '0', STR_PAD_LEFT), '0') : '');
    }

    /**
     * {@see LocalTime::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
