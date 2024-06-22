<?php

declare(strict_types=1);

namespace Brick\DateTime;

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

/**
 * A date-time without a time-zone in the ISO-8601 calendar system, such as 2007-12-03T10:15:30.
 *
 * This class is immutable.
 */
final class LocalDateTime implements JsonSerializable, Stringable
{
    public function __construct(
        private readonly LocalDate $date,
        private readonly LocalTime $time,
    ) {
    }

    /**
     * @param int       $year   The year, from MIN_YEAR to MAX_YEAR.
     * @param int|Month $month  The month-of-year, from 1 (January) to 12 (December).
     * @param int       $day    The day-of-month, from 1 to 31.
     * @param int       $hour   The hour-of-day, from 0 to 23.
     * @param int       $minute The minute-of-hour, from 0 to 59.
     * @param int       $second The second-of-minute, from 0 to 59.
     * @param int       $nano   The nano-of-second, from 0 to 999,999,999.
     *
     * @throws DateTimeException If the date or time is not valid.
     */
    public static function of(int $year, int|Month $month, int $day, int $hour = 0, int $minute = 0, int $second = 0, int $nano = 0): LocalDateTime
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of($hour, $minute, $second, $nano);

        return new LocalDateTime($date, $time);
    }

    /**
     * Returns the current local date-time in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): LocalDateTime
    {
        return ZonedDateTime::now($timeZone, $clock)->getDateTime();
    }

    /**
     * @throws DateTimeException      If the date-time is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): LocalDateTime
    {
        return new LocalDateTime(
            LocalDate::from($result),
            LocalTime::from($result),
        );
    }

    /**
     * Obtains an instance of `LocalDateTime` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-12-03T10:15:30`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the date-time is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): LocalDateTime
    {
        if ($parser === null) {
            $parser = IsoParsers::localDateTime();
        }

        return LocalDateTime::from($parser->parse($text));
    }

    /**
     * Creates a LocalDateTime from a native DateTime or DateTimeImmutable object.
     */
    public static function fromNativeDateTime(DateTimeInterface $dateTime): LocalDateTime
    {
        return new LocalDateTime(
            LocalDate::fromNativeDateTime($dateTime),
            LocalTime::fromNativeDateTime($dateTime),
        );
    }

    /**
     * Returns the smallest possible value for LocalDateTime.
     */
    public static function min(): LocalDateTime
    {
        /** @var LocalDateTime|null $min */
        static $min = null;

        return $min ??= new LocalDateTime(LocalDate::min(), LocalTime::min());
    }

    /**
     * Returns the highest possible value for LocalDateTime.
     */
    public static function max(): LocalDateTime
    {
        /** @var LocalDateTime|null $max */
        static $max = null;

        return $max ??= new LocalDateTime(LocalDate::max(), LocalTime::max());
    }

    /**
     * Returns the smallest LocalDateTime among the given values.
     *
     * @param LocalDateTime ...$times The LocalDateTime objects to compare.
     *
     * @return LocalDateTime The earliest LocalDateTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(LocalDateTime ...$times): LocalDateTime
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
     * Returns the highest LocalDateTime among the given values.
     *
     * @param LocalDateTime ...$times The LocalDateTime objects to compare.
     *
     * @return LocalDateTime The latest LocalDateTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(LocalDateTime ...$times): LocalDateTime
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

    public function getDate(): LocalDate
    {
        return $this->date;
    }

    public function getTime(): LocalTime
    {
        return $this->time;
    }

    public function getYear(): int
    {
        return $this->date->getYear();
    }

    /**
     * Returns the month-of-year as a Month enum.
     */
    public function getMonth(): Month
    {
        return $this->date->getMonth();
    }

    /**
     * Returns the month-of-year value from 1 to 12.
     *
     * @return int<1, 12>
     */
    public function getMonthValue(): int
    {
        return $this->date->getMonthValue();
    }

    /**
     * @return int<1, 31>
     */
    public function getDayOfMonth(): int
    {
        return $this->date->getDayOfMonth();
    }

    public function getDayOfWeek(): DayOfWeek
    {
        return $this->date->getDayOfWeek();
    }

    /**
     * @return int<1, 366>
     */
    public function getDayOfYear(): int
    {
        return $this->date->getDayOfYear();
    }

    public function getHour(): int
    {
        return $this->time->getHour();
    }

    public function getMinute(): int
    {
        return $this->time->getMinute();
    }

    public function getSecond(): int
    {
        return $this->time->getSecond();
    }

    public function getNano(): int
    {
        return $this->time->getNano();
    }

    /**
     * Returns a copy of this LocalDateTime with the date altered.
     */
    public function withDate(LocalDate $date): LocalDateTime
    {
        if ($date->isEqualTo($this->date)) {
            return $this;
        }

        return new LocalDateTime($date, $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the time altered.
     */
    public function withTime(LocalTime $time): LocalDateTime
    {
        if ($time->isEqualTo($this->time)) {
            return $this;
        }

        return new LocalDateTime($this->date, $time);
    }

    /**
     * Returns a copy of this LocalDateTime with the year altered.
     *
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     *
     * @throws DateTimeException If the year is outside the valid range.
     */
    public function withYear(int $year): LocalDateTime
    {
        $date = $this->date->withYear($year);

        if ($date === $this->date) {
            return $this;
        }

        return new LocalDateTime($date, $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the month-of-year altered.
     *
     * If the day-of-month is invalid for the month and year, it will be changed to the last valid day of the month.
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function withMonth(int|Month $month): LocalDateTime
    {
        $date = $this->date->withMonth($month);

        if ($date === $this->date) {
            return $this;
        }

        return new LocalDateTime($date, $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the day-of-month altered.
     *
     * If the resulting date is invalid, an exception is thrown.
     *
     * @throws DateTimeException If the day is invalid for the current year and month.
     */
    public function withDay(int $day): LocalDateTime
    {
        $date = $this->date->withDay($day);

        if ($date === $this->date) {
            return $this;
        }

        return new LocalDateTime($date, $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the hour-of-day altered.
     *
     * @throws DateTimeException If the hour is invalid.
     */
    public function withHour(int $hour): LocalDateTime
    {
        $time = $this->time->withHour($hour);

        if ($time === $this->time) {
            return $this;
        }

        return new LocalDateTime($this->date, $time);
    }

    /**
     * Returns a copy of this LocalDateTime with the minute-of-hour altered.
     *
     * @throws DateTimeException If the minute-of-hour if not valid.
     */
    public function withMinute(int $minute): LocalDateTime
    {
        $time = $this->time->withMinute($minute);

        if ($time === $this->time) {
            return $this;
        }

        return new LocalDateTime($this->date, $time);
    }

    /**
     * Returns a copy of this LocalDateTime with the second-of-minute altered.
     *
     * @throws DateTimeException If the second-of-minute if not valid.
     */
    public function withSecond(int $second): LocalDateTime
    {
        $time = $this->time->withSecond($second);

        if ($time === $this->time) {
            return $this;
        }

        return new LocalDateTime($this->date, $time);
    }

    /**
     * Returns a copy of this LocalDateTime with the nano-of-second altered.
     *
     * @throws DateTimeException If the nano-of-second if not valid.
     */
    public function withNano(int $nano): LocalDateTime
    {
        $time = $this->time->withNano($nano);

        if ($time === $this->time) {
            return $this;
        }

        return new LocalDateTime($this->date, $time);
    }

    /**
     * Returns a zoned date-time formed from this date-time and the specified time-zone.
     *
     * @param TimeZone $zone The zime-zone to use.
     *
     * @return ZonedDateTime The zoned date-time formed from this date-time.
     */
    public function atTimeZone(TimeZone $zone): ZonedDateTime
    {
        return ZonedDateTime::of($this, $zone);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified Period added.
     */
    public function plusPeriod(Period $period): LocalDateTime
    {
        $date = $this->date->plusPeriod($period);

        if ($date === $this->date) {
            return $this;
        }

        return new LocalDateTime($date, $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specific Duration added.
     */
    public function plusDuration(Duration $duration): LocalDateTime
    {
        if ($duration->isZero()) {
            return $this;
        }

        $days = Math::floorDiv($this->time->toSecondOfDay() + $duration->getSeconds(), LocalTime::SECONDS_PER_DAY);

        return new LocalDateTime($this->date->plusDays($days), $this->time->plusDuration($duration));
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in years added.
     *
     * @throws DateTimeException If the resulting year is out of range.
     */
    public function plusYears(int $years): LocalDateTime
    {
        if ($years === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->plusYears($years), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in months added.
     */
    public function plusMonths(int $months): LocalDateTime
    {
        if ($months === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->plusMonths($months), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): LocalDateTime
    {
        if ($weeks === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->plusWeeks($weeks), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in days added.
     */
    public function plusDays(int $days): LocalDateTime
    {
        if ($days === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->plusDays($days), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in hours added.
     */
    public function plusHours(int $hours): LocalDateTime
    {
        if ($hours === 0) {
            return $this;
        }

        return $this->plusWithOverflow($hours, 0, 0, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in minutes added.
     */
    public function plusMinutes(int $minutes): LocalDateTime
    {
        if ($minutes === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, $minutes, 0, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in seconds added.
     */
    public function plusSeconds(int $seconds): LocalDateTime
    {
        if ($seconds === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, $seconds, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in nanoseconds added.
     */
    public function plusNanos(int $nanos): LocalDateTime
    {
        if ($nanos === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, 0, $nanos, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified Period subtracted.
     */
    public function minusPeriod(Period $period): LocalDateTime
    {
        return $this->plusPeriod($period->negated());
    }

    /**
     * Returns a copy of this LocalDateTime with the specific Duration subtracted.
     */
    public function minusDuration(Duration $duration): LocalDateTime
    {
        return $this->plusDuration($duration->negated());
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in years subtracted.
     */
    public function minusYears(int $years): LocalDateTime
    {
        if ($years === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->minusYears($years), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in months subtracted.
     */
    public function minusMonths(int $months): LocalDateTime
    {
        if ($months === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->minusMonths($months), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in weeks subtracted.
     */
    public function minusWeeks(int $weeks): LocalDateTime
    {
        if ($weeks === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->minusWeeks($weeks), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in days subtracted.
     */
    public function minusDays(int $days): LocalDateTime
    {
        if ($days === 0) {
            return $this;
        }

        return new LocalDateTime($this->date->minusDays($days), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in hours subtracted.
     */
    public function minusHours(int $hours): LocalDateTime
    {
        if ($hours === 0) {
            return $this;
        }

        return $this->plusWithOverflow($hours, 0, 0, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in minutes subtracted.
     */
    public function minusMinutes(int $minutes): LocalDateTime
    {
        if ($minutes === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, $minutes, 0, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in seconds subtracted.
     */
    public function minusSeconds(int $seconds): LocalDateTime
    {
        if ($seconds === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, $seconds, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in nanoseconds subtracted.
     */
    public function minusNanos(int $nanos): LocalDateTime
    {
        if ($nanos === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, 0, $nanos, -1);
    }

    /**
     * Compares this date-time to another date-time.
     *
     * @param LocalDateTime $that The date-time to compare to.
     *
     * @return int [-1,0,1] If this date-time is before, on, or after the given date-time.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(LocalDateTime $that): int
    {
        $cmp = $this->date->compareTo($that->date);

        if ($cmp !== 0) {
            return $cmp;
        }

        return $this->time->compareTo($that->time);
    }

    public function isEqualTo(LocalDateTime $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isBefore(LocalDateTime $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(LocalDateTime $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isAfter(LocalDateTime $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(LocalDateTime $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns whether this LocalDateTime is in the future, in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public function isFuture(TimeZone $timeZone, ?Clock $clock = null): bool
    {
        return $this->isAfter(LocalDateTime::now($timeZone, $clock));
    }

    /**
     * Returns whether this LocalDateTime is in the past, in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public function isPast(TimeZone $timeZone, ?Clock $clock = null): bool
    {
        return $this->isBefore(LocalDateTime::now($timeZone, $clock));
    }

    /**
     * Converts this LocalDateTime to a native DateTime object.
     *
     * The result is a DateTime in the UTC time-zone.
     *
     * Note that the native DateTime object supports a precision up to the microsecond,
     * so the nanoseconds are rounded down to the nearest microsecond.
     */
    public function toNativeDateTime(): DateTime
    {
        return $this->atTimeZone(TimeZone::utc())->toNativeDateTime();
    }

    /**
     * Converts this LocalDateTime to a native DateTimeImmutable object.
     *
     * The result is a DateTimeImmutable in the UTC time-zone.
     *
     * Note that the native DateTimeImmutable object supports a precision up to the microsecond,
     * so the nanoseconds are rounded down to the nearest microsecond.
     */
    public function toNativeDateTimeImmutable(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->toNativeDateTime());
    }

    /**
     * Serializes as a string using {@see LocalDateTime::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this date time.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        return $this->date . 'T' . $this->time;
    }

    /**
     * {@see LocalDateTime::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns a copy of this `LocalDateTime` with the specified period added.
     *
     * @param int $hours   The hours to add. May be negative.
     * @param int $minutes The minutes to add. May be negative.
     * @param int $seconds The seconds to add. May be negative.
     * @param int $nanos   The nanos to add. May be negative.
     * @param int $sign    The sign, validated as `1` to add or `-1` to subtract.
     *
     * @return LocalDateTime The combined result.
     */
    private function plusWithOverflow(int $hours, int $minutes, int $seconds, int $nanos, int $sign): LocalDateTime
    {
        $totDays =
            intdiv($hours, LocalTime::HOURS_PER_DAY) +
            intdiv($minutes, LocalTime::MINUTES_PER_DAY) +
            intdiv($seconds, LocalTime::SECONDS_PER_DAY);
        $totDays *= $sign;

        $totSeconds =
            ($seconds % LocalTime::SECONDS_PER_DAY) +
            ($minutes % LocalTime::MINUTES_PER_DAY) * LocalTime::SECONDS_PER_MINUTE +
            ($hours % LocalTime::HOURS_PER_DAY) * LocalTime::SECONDS_PER_HOUR;

        $curSoD = $this->time->toSecondOfDay();
        $totSeconds = $totSeconds * $sign + $curSoD;

        $totNanos = $nanos * $sign + $this->time->getNano();
        $totSeconds += Math::floorDiv($totNanos, LocalTime::NANOS_PER_SECOND);
        $newNano = Math::floorMod($totNanos, LocalTime::NANOS_PER_SECOND);

        $totDays += Math::floorDiv($totSeconds, LocalTime::SECONDS_PER_DAY);
        $newSoD = Math::floorMod($totSeconds, LocalTime::SECONDS_PER_DAY);

        $newDate = $this->date->plusDays($totDays);
        $newTime = ($newSoD === $curSoD ? $this->time : LocalTime::ofSecondOfDay($newSoD, $newNano));

        return new LocalDateTime($newDate, $newTime);
    }
}
