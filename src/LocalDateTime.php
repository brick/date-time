<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;

/**
 * A date-time without a time-zone in the ISO-8601 calendar system, such as 2007-12-03T10:15:30.
 *
 * This class is immutable.
 */
class LocalDateTime
{
    /**
     * @var LocalDate
     */
    private $date;

    /**
     * @var LocalTime
     */
    private $time;

    /**
     * Class constructor.
     *
     * @param LocalDate $date
     * @param LocalTime $time
     */
    public function __construct(LocalDate $date, LocalTime $time)
    {
        $this->date = $date;
        $this->time = $time;
    }

    /**
     * @param int $year   The year, from MIN_YEAR to MAX_YEAR.
     * @param int $month  The month-of-year, from 1 (January) to 12 (December).
     * @param int $day    The day-of-month, from 1 to 31.
     * @param int $hour   The hour-of-day, from 0 to 23.
     * @param int $minute The minute-of-hour, from 0 to 59.
     * @param int $second The second-of-minute, from 0 to 59.
     * @param int $nano   The nano-of-second, from 0 to 999,999,999.
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the date or time is not valid.
     */
    public static function of(int $year, int $month, int $day, int $hour = 0, int $minute = 0, int $second = 0, int $nano = 0) : LocalDateTime
    {
        $date = LocalDate::of($year, $month, $day);
        $time = LocalTime::of($hour, $minute, $second, $nano);

        return new LocalDateTime($date, $time);
    }

    /**
     * @param TimeZone $timeZone
     *
     * @return LocalDateTime
     */
    public static function now(TimeZone $timeZone) : LocalDateTime
    {
        return ZonedDateTime::now($timeZone)->getDateTime();
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException      If the date-time is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result) : LocalDateTime
    {
        return new LocalDateTime(
            LocalDate::from($result),
            LocalTime::from($result)
        );
    }

    /**
     * Obtains an instance of `LocalDateTime` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-12-03T10:15:30`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException      If the date-time is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, DateTimeParser $parser = null) : LocalDateTime
    {
        if (! $parser) {
            $parser = IsoParsers::localDateTime();
        }

        return LocalDateTime::from($parser->parse($text));
    }

    /**
     * Returns the smallest possible value for LocalDateTime.
     *
     * @return LocalDateTime
     */
    public static function min() : LocalDateTime
    {
        return new LocalDateTime(LocalDate::min(), LocalTime::min());
    }

    /**
     * Returns the highest possible value for LocalDateTime.
     *
     * @return LocalDateTime
     */
    public static function max() : LocalDateTime
    {
        return new LocalDateTime(LocalDate::max(), LocalTime::max());
    }

    /**
     * Returns the smallest LocalDateTime among the given values.
     *
     * @param LocalDateTime[] $times The LocalDateTime objects to compare.
     *
     * @return LocalDateTime The earliest LocalDateTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(LocalDateTime ...$times) : LocalDateTime
    {
        if (! $times) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $min = LocalDateTime::max();

        foreach ($times as $time) {
            if ($time->isBefore($min)) {
                $min = $time;
            }
        }

        return $min;
    }

    /**
     * Returns the highest LocalDateTime among the given values.
     *
     * @param LocalDateTime[] $times The LocalDateTime objects to compare.
     *
     * @return LocalDateTime The latest LocalDateTime object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(LocalDateTime ...$times) : LocalDateTime
    {
        if (! $times) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $max = LocalDateTime::min();

        foreach ($times as $time) {
            if ($time->isAfter($max)) {
                $max = $time;
            }
        }

        return $max;
    }

    /**
     * @return LocalDate
     */
    public function getDate() : LocalDate
    {
        return $this->date;
    }

    /**
     * @return LocalTime
     */
    public function getTime() : LocalTime
    {
        return $this->time;
    }

    /**
     * @return int
     */
    public function getYear() : int
    {
        return $this->date->getYear();
    }

    /**
     * @return int
     */
    public function getMonth() : int
    {
        return $this->date->getMonth();
    }

    /**
     * @return int
     */
    public function getDay() : int
    {
        return $this->date->getDay();
    }

    /**
     * @return DayOfWeek
     */
    public function getDayOfWeek() : DayOfWeek
    {
        return $this->date->getDayOfWeek();
    }

    /**
     * @return int
     */
    public function getDayOfYear() : int
    {
        return $this->date->getDayOfYear();
    }

    /**
     * @return int
     */
    public function getHour() : int
    {
        return $this->time->getHour();
    }

    /**
     * @return int
     */
    public function getMinute() : int
    {
        return $this->time->getMinute();
    }

    /**
     * @return int
     */
    public function getSecond() : int
    {
        return $this->time->getSecond();
    }

    /**
     * @return int
     */
    public function getNano() : int
    {
        return $this->time->getNano();
    }

    /**
     * Returns a copy of this date-time with the new date and time, checking
     * to see if a new object is in fact required.
     *
     * @param LocalDate $date
     * @param LocalTime $time
     *
     * @return LocalDateTime
     */
    private function with(LocalDate $date, LocalTime $time) : LocalDateTime
    {
        if ($date->isEqualTo($this->date) && $time->isEqualTo($this->time)) {
            return $this;
        }

        return new LocalDateTime($date, $time);
    }

    /**
     * Returns a copy of this LocalDateTime with the date altered.
     *
     * @param LocalDate $date
     *
     * @return LocalDateTime
     */
    public function withDate(LocalDate $date) : LocalDateTime
    {
        if ($date->isEqualTo($this->date)) {
            return $this;
        }

        return new LocalDateTime($date, $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the time altered.
     *
     * @param LocalTime $time
     *
     * @return LocalDateTime
     */
    public function withTime(LocalTime $time) : LocalDateTime
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
     * @param int $year
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the year is outside the valid range.
     */
    public function withYear(int $year) : LocalDateTime
    {
        return $this->with($this->date->withYear($year), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the month-of-year altered.
     *
     * If the day-of-month is invalid for the month and year, it will be changed to the last valid day of the month.
     *
     * @param int $month
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function withMonth(int $month) : LocalDateTime
    {
        return $this->with($this->date->withMonth($month), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the day-of-month altered.
     *
     * If the resulting date is invalid, an exception is thrown.
     *
     * @param int $day
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the day is invalid for the current year and month.
     */
    public function withDay(int $day) : LocalDateTime
    {
        return $this->with($this->date->withDay($day), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the hour-of-day altered.
     *
     * @param int $hour
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the hour is invalid.
     */
    public function withHour(int $hour) : LocalDateTime
    {
        return $this->with($this->date, $this->time->withHour($hour));
    }

    /**
     * Returns a copy of this LocalDateTime with the minute-of-hour altered.
     *
     * @param int $minute
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the minute-of-hour if not valid.
     */
    public function withMinute(int $minute) : LocalDateTime
    {
        return $this->with($this->date, $this->time->withMinute($minute));
    }

    /**
     * Returns a copy of this LocalDateTime with the second-of-minute altered.
     *
     * @param int $second
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the second-of-minute if not valid.
     */
    public function withSecond(int $second) : LocalDateTime
    {
        return $this->with($this->date, $this->time->withSecond($second));
    }

    /**
     * Returns a copy of this LocalDateTime with the nano-of-second altered.
     *
     * @param int $nano
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the nano-of-second if not valid.
     */
    public function withNano(int $nano) : LocalDateTime
    {
        return $this->with($this->date, $this->time->withNano($nano));
    }

    /**
     * Returns a zoned date-time formed from this date-time and the specified time-zone.
     *
     * @param TimeZone $zone The zime-zone to use.
     *
     * @return ZonedDateTime The zoned date-time formed from this date-time.
     */
    public function atTimeZone(TimeZone $zone) : ZonedDateTime
    {
        return ZonedDateTime::of($this, $zone);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified Period added.
     *
     * @param Period $period
     *
     * @return LocalDateTime
     */
    public function plusPeriod(Period $period) : LocalDateTime
    {
        return $this->with($this->date->plusPeriod($period), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specific Duration added.
     *
     * @param Duration $duration
     *
     * @return LocalDateTime
     */
    public function plusDuration(Duration $duration) : LocalDateTime
    {
        $days = Math::floorDiv($duration->getSeconds(), LocalTime::SECONDS_PER_DAY);

        return $this->with($this->date->plusDays($days), $this->time->plusDuration($duration));
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in years added.
     *
     * @param int $years
     *
     * @return LocalDateTime
     *
     * @throws DateTimeException If the resulting year is out of range.
     */
    public function plusYears(int $years) : LocalDateTime
    {
        return $this->with($this->date->plusYears($years), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in months added.
     *
     * @param int $months
     *
     * @return LocalDateTime
     */
    public function plusMonths(int $months) : LocalDateTime
    {
        return $this->with($this->date->plusMonths($months), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in weeks added.
     *
     * @param int $weeks
     *
     * @return LocalDateTime
     */
    public function plusWeeks(int $weeks) : LocalDateTime
    {
        return $this->with($this->date->plusWeeks($weeks), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in days added.
     *
     * @param int $days
     *
     * @return LocalDateTime
     */
    public function plusDays(int $days) : LocalDateTime
    {
        return $this->with($this->date->plusDays($days), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in hours added.
     *
     * @param int $hours
     *
     * @return LocalDateTime
     */
    public function plusHours(int $hours) : LocalDateTime
    {
        if ($hours === 0) {
            return $this;
        }

        return $this->plusWithOverflow($hours, 0, 0, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in minutes added.
     *
     * @param int $minutes
     *
     * @return LocalDateTime
     */
    public function plusMinutes(int $minutes) : LocalDateTime
    {
        if ($minutes === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, $minutes, 0, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in seconds added.
     *
     * @param int $seconds
     *
     * @return LocalDateTime
     */
    public function plusSeconds(int $seconds) : LocalDateTime
    {
        if ($seconds === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, $seconds, 0, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in nanoseconds added.
     *
     * @param int $nanos
     *
     * @return LocalDateTime
     */
    public function plusNanos(int $nanos) : LocalDateTime
    {
        if ($nanos === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, 0, $nanos, 1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified Period subtracted.
     *
     * @param Period $period
     *
     * @return LocalDateTime
     */
    public function minusPeriod(Period $period) : LocalDateTime
    {
        return $this->with($this->date->minusPeriod($period), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specific Duration subtracted.
     *
     * @param Duration $duration
     *
     * @return LocalDateTime
     */
    public function minusDuration(Duration $duration) : LocalDateTime
    {
        return $this->plusDuration($duration->negated());
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in years subtracted.
     *
     * @param int $years
     *
     * @return LocalDateTime
     */
    public function minusYears(int $years) : LocalDateTime
    {
        return $this->with($this->date->minusYears($years), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in months subtracted.
     *
     * @param int $months
     *
     * @return LocalDateTime
     */
    public function minusMonths(int $months) : LocalDateTime
    {
        return $this->with($this->date->minusMonths($months), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in weeks subtracted.
     *
     * @param int $weeks
     *
     * @return LocalDateTime
     */
    public function minusWeeks(int $weeks) : LocalDateTime
    {
        return $this->with($this->date->minusWeeks($weeks), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in days subtracted.
     *
     * @param int $days
     *
     * @return LocalDateTime
     */
    public function minusDays(int $days) : LocalDateTime
    {
        return $this->with($this->date->minusDays($days), $this->time);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in hours subtracted.
     *
     * @param int $hours
     *
     * @return LocalDateTime
     */
    public function minusHours(int $hours) : LocalDateTime
    {
        if ($hours === 0) {
            return $this;
        }

        return $this->plusWithOverflow($hours, 0, 0, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in minutes subtracted.
     *
     * @param int $minutes
     *
     * @return LocalDateTime
     */
    public function minusMinutes(int $minutes) : LocalDateTime
    {
        if ($minutes === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, $minutes, 0, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in seconds subtracted.
     *
     * @param int $seconds
     *
     * @return LocalDateTime
     */
    public function minusSeconds(int $seconds) : LocalDateTime
    {
        if ($seconds === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, $seconds, 0, -1);
    }

    /**
     * Returns a copy of this LocalDateTime with the specified period in nanoseconds subtracted.
     *
     * @param int $nanos
     *
     * @return LocalDateTime
     */
    public function minusNanos(int $nanos) : LocalDateTime
    {
        if ($nanos === 0) {
            return $this;
        }

        return $this->plusWithOverflow(0, 0, 0, $nanos, -1);
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
    private function plusWithOverflow(int $hours, int $minutes, int $seconds, int $nanos, int $sign) : LocalDateTime
    {
        $totDays =
            \intdiv($hours, LocalTime::HOURS_PER_DAY) +
            \intdiv($minutes, LocalTime::MINUTES_PER_DAY) +
            \intdiv($seconds, LocalTime::SECONDS_PER_DAY);
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

        $newTime = ($newSoD === $curSoD ? $this->time : LocalTime::ofSecondOfDay($newSoD, $newNano));

        return $this->with($this->date->plusDays($totDays), $newTime);
    }

    /**
     * Compares this date-time to another date-time.
     *
     * @param LocalDateTime $that The date-time to compare to.
     *
     * @return int [-1,0,1] If this date-time is before, on, or after the given date-time.
     */
    public function compareTo(LocalDateTime $that) : int
    {
        return $this->date->compareTo($that->date) ?: $this->time->compareTo($that->time);
    }

    /**
     * @param LocalDateTime $that
     *
     * @return bool
     */
    public function isEqualTo(LocalDateTime $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * @param LocalDateTime $that
     *
     * @return bool
     */
    public function isBefore(LocalDateTime $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * @param LocalDateTime $that
     *
     * @return bool
     */
    public function isBeforeOrEqualTo(LocalDateTime $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * @param LocalDateTime $that
     *
     * @return bool
     */
    public function isAfter(LocalDateTime $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * @param LocalDateTime $that
     *
     * @return bool
     */
    public function isAfterOrEqualTo(LocalDateTime $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->date . 'T' . $this->time;
    }
}
