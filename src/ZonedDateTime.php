<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;

/**
 * A date-time with a time-zone in the ISO-8601 calendar system.
 *
 * A ZonedDateTime can be viewed as a LocalDateTime along with a time zone
 * and targets a specific point in time.
 */
class ZonedDateTime implements \JsonSerializable
{
    /**
     * The local date-time.
     *
     * @var LocalDateTime
     */
    private $localDateTime;

    /**
     * The time-zone offset from UTC/Greenwich.
     *
     * @var TimeZoneOffset
     */
    private $timeZoneOffset;

    /**
     * The time-zone.
     *
     * It is either a TimeZoneRegion if this ZonedDateTime is region-based,
     * or the same instance as the offset if this ZonedDateTime is offset-based.
     *
     * @var TimeZone
     */
    private $timeZone;

    /**
     * The instant represented by this ZonedDateTime.
     *
     * @var Instant
     */
    private $instant;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param LocalDateTime  $localDateTime
     * @param TimeZoneOffset $offset
     * @param TimeZone       $zone
     * @param Instant        $instant
     */
    private function __construct(LocalDateTime $localDateTime, TimeZoneOffset $offset, TimeZone $zone, Instant $instant)
    {
        $this->localDateTime  = $localDateTime;
        $this->timeZone       = $zone;
        $this->timeZoneOffset = $offset;
        $this->instant        = $instant;
    }

    /**
     * Creates a ZonedDateTime from a LocalDateTime and a TimeZone.
     *
     * This resolves the local date-time to an instant on the time-line.
     *
     * When a TimeZoneOffset is used, the local date-time can be converted to an instant without ambiguity.
     *
     * When a TimeZoneRegion is used, Daylight Saving Time can make the conversion more complex.
     * There are 3 cases:
     *
     * - Normal: when there is only one valid offset for the date-time. The conversion is then as straightforward
     *   as when using a TimeZoneOffset. This is fortunately the case for the vast majority of the year.
     * - Gap: when there is no valid offset for the date-time. This happens when the clock jumps forward
     *   typically due to a DST transition from "winter" to "summer". The date-times between the two times
     *   of the transition do not exist.
     * - Overlap: when there are two valid offets for the date-time. This happens when the clock is set back
     *   typically due to a DST transition from "summer" to "winter". The date-times between the two times
     *   of the transition can be resolved to two different offsets, representing two different instants
     *   on the time-line.
     *
     * The strategy for resolving gaps and overlaps is the following:
     *
     * - If the local date-time falls in the middle of a gap, then the resulting date-time will be shifted forward
     *   by the length of the gap, and the later offset, typically "summer" time, will be used.
     * - If the local date-time falls in the middle of an overlap, then the offset closest to UTC will be used.
     *
     * @param LocalDateTime $dateTime
     * @param TimeZone      timeZone
     *
     * @return ZonedDateTime
     */
    public static function of(LocalDateTime $dateTime, TimeZone $timeZone) : ZonedDateTime
    {
        $dtz = $timeZone->toDateTimeZone();
        $dt = new \DateTime((string) $dateTime->withNano(0), $dtz);

        $instant = Instant::of($dt->getTimestamp(), $dateTime->getNano());

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dt->getOffset());
        }

        // The time can be affected if the date-time is not valid for the given time-zone due to a DST transition,
        // so we have to re-compute the local date-time from the DateTime object.
        // DateTime does not support nanos of seconds, so we just copy the nanos back from the original date-time.
        $dateTime = LocalDateTime::parse($dt->format('Y-m-d\TH:i:s'))->withNano($dateTime->getNano());

        return new ZonedDateTime($dateTime, $timeZoneOffset, $timeZone, $instant);
    }

    /**
     * Creates a ZonedDateTime from an instant and a time zone.
     *
     * This resolves the instant to a date and time without ambiguity.
     *
     * @param Instant  $instant  The instant.
     * @param TimeZone $timeZone The time zone.
     *
     * @return ZonedDateTime
     */
    public static function ofInstant(Instant $instant, TimeZone $timeZone) : ZonedDateTime
    {
        $dateTimeZone = $timeZone->toDateTimeZone();

        // We need to pass a DateTimeZone to avoid a PHP warning...
        $dateTime = new \DateTime('@' . $instant->getEpochSecond(), $dateTimeZone);

        // ... but this DateTimeZone is ignored because of the timestamp, so we set it again.
        $dateTime->setTimezone($dateTimeZone);

        $localDateTime = LocalDateTime::parse($dateTime->format('Y-m-d\TH:i:s'));
        $localDateTime = $localDateTime->withNano($instant->getNano());

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dateTime->getOffset());
        }

        return new ZonedDateTime($localDateTime, $timeZoneOffset, $timeZone, $instant);
    }

    /**
     * Returns the current date-time in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     *
     * @param TimeZone   $timeZone
     * @param Clock|null $clock
     *
     * @return ZonedDateTime
     */
    public static function now(TimeZone $timeZone, Clock $clock = null) : ZonedDateTime
    {
        return ZonedDateTime::ofInstant(Instant::now($clock), $timeZone);
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a set of date-time fields.
     *
     * This method is only useful to parsers.
     *
     * @param DateTimeParseResult $result
     *
     * @return ZonedDateTime
     *
     * @throws DateTimeException      If the zoned date-time is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result) : ZonedDateTime
    {
        $localDateTime = LocalDateTime::from($result);

        $timeZoneOffset = TimeZoneOffset::from($result);

        if ($result->hasField(Field\TimeZoneRegion::NAME)) {
            $timeZone = TimeZoneRegion::from($result);
        } else {
            $timeZone = $timeZoneOffset;
        }

        return ZonedDateTime::of(
            $localDateTime,
            $timeZone
        );
    }

    /**
     * Obtains an instance of `ZonedDateTime` from a text string.
     *
     * Valid examples:
     * - `2007-12-03T10:15:30:45Z`
     * - `2007-12-03T10:15:30+01:00`
     * - `2007-12-03T10:15:30+01:00[Europe/Paris]`
     *
     * @param string              $text   The text to parse.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return ZonedDateTime
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, DateTimeParser $parser = null) : ZonedDateTime
    {
        if (! $parser) {
            $parser = IsoParsers::zonedDateTime();
        }

        return ZonedDateTime::from($parser->parse($text));
    }
    /**
     * Creates a ZonedDateTime from a native DateTime or DateTimeImmutable object.
     *
     * @param \DateTimeInterface $dateTime
     *
     * @return ZonedDateTime
     *
     * @throws DateTimeException If the DateTime object has no timezone.
     */
    public static function fromDateTime(\DateTimeInterface $dateTime) : ZonedDateTime
    {
        $localDateTime = LocalDateTime::fromDateTime($dateTime);

        $dateTimeZone = $dateTime->getTimezone();

        if ($dateTimeZone === false) {
            throw new DateTimeException('This DateTime object has no timezone.');
        }

        $timeZone = TimeZone::fromDateTimeZone($dateTimeZone);

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dateTime->getOffset());
        }

        $instant = Instant::of($dateTime->getTimestamp(), $localDateTime->getNano());

        return new ZonedDateTime($localDateTime, $timeZoneOffset, $timeZone, $instant);
    }

    /**
     * Returns the `LocalDateTime` part of this `ZonedDateTime`.
     *
     * @return LocalDateTime
     */
    public function getDateTime() : LocalDateTime
    {
        return $this->localDateTime;
    }

    /**
     * Returns the `LocalDate` part of this `ZonedDateTime`.
     *
     * @return LocalDate
     */
    public function getDate() : LocalDate
    {
        return $this->localDateTime->getDate();
    }

    /**
     * Returns the `LocalTime` part of this `ZonedDateTime`.
     *
     * @return LocalTime
     */
    public function getTime() : LocalTime
    {
        return $this->localDateTime->getTime();
    }

    /**
     * @return int
     */
    public function getYear() : int
    {
        return $this->localDateTime->getYear();
    }

    /**
     * @return int
     */
    public function getMonth() : int
    {
        return $this->localDateTime->getMonth();
    }

    /**
     * @return int
     */
    public function getDay() : int
    {
        return $this->localDateTime->getDay();
    }

    /**
     * @return DayOfWeek
     */
    public function getDayOfWeek() : DayOfWeek
    {
        return $this->localDateTime->getDayOfWeek();
    }

    /**
     * @return int
     */
    public function getDayOfYear() : int
    {
        return $this->localDateTime->getDayOfYear();
    }

    /**
     * @return int
     */
    public function getHour() : int
    {
        return $this->localDateTime->getHour();
    }

    /**
     * @return int
     */
    public function getMinute() : int
    {
        return $this->localDateTime->getMinute();
    }

    /**
     * @return int
     */
    public function getSecond() : int
    {
        return $this->localDateTime->getSecond();
    }

    /**
     * @return int
     */
    public function getEpochSecond() : int
    {
        return $this->instant->getEpochSecond();
    }

    /**
     * @return int
     */
    public function getNano() : int
    {
        return $this->instant->getNano();
    }

    /**
     * Returns the time-zone, region or offset.
     *
     * @return TimeZone
     */
    public function getTimeZone() : TimeZone
    {
        return $this->timeZone;
    }

    /**
     * Returns the time-zone offset.
     *
     * @return TimeZoneOffset
     */
    public function getTimeZoneOffset() : TimeZoneOffset
    {
        return $this->timeZoneOffset;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstant() : Instant
    {
        return $this->instant;
    }

    /**
     * Returns a copy of this ZonedDateTime with a different date.
     *
     * @param LocalDate $date
     *
     * @return ZonedDateTime
     */
    public function withDate(LocalDate $date) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withDate($date), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with a different time.
     *
     * @param LocalTime $time
     *
     * @return ZonedDateTime
     */
    public function withTime(LocalTime $time) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withTime($time), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the year altered.
     *
     * @param int $year
     *
     * @return ZonedDateTime
     */
    public function withYear(int $year) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withYear($year), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the month-of-year altered.
     *
     * @param int $month
     *
     * @return ZonedDateTime
     */
    public function withMonth(int $month) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withMonth($month), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the day-of-month altered.
     *
     * @param int $day
     *
     * @return ZonedDateTime
     */
    public function withDay(int $day) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withDay($day), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the hour-of-day altered.
     *
     * @param int $hour
     *
     * @return ZonedDateTime
     */
    public function withHour(int $hour) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withHour($hour), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the minute-of-hour altered.
     *
     * @param int $minute
     *
     * @return ZonedDateTime
     */
    public function withMinute(int $minute) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withMinute($minute), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the second-of-minute altered.
     *
     * @param int $second
     *
     * @return ZonedDateTime
     */
    public function withSecond(int $second) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withSecond($second), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the nano-of-second altered.
     *
     * @param int $nano
     *
     * @return ZonedDateTime
     */
    public function withNano(int $nano) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->withNano($nano), $this->timeZone);
    }

    /**
     * Returns a copy of this `ZonedDateTime` with a different time-zone,
     * retaining the local date-time if possible.
     *
     * @param TimeZone $timeZone The time-zone to change to.
     *
     * @return ZonedDateTime
     */
    public function withTimeZoneSameLocal(TimeZone $timeZone) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime, $timeZone);
    }

    /**
     * Returns a copy of this date-time with a different time-zone, retaining the instant.
     *
     * @param TimeZone $timeZone
     *
     * @return ZonedDateTime
     */
    public function withTimeZoneSameInstant(TimeZone $timeZone) : ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this->instant, $timeZone);
    }

    /**
     * Returns a copy of this date-time with the time-zone set to the offset.
     *
     * This returns a zoned date-time where the time-zone is the same as `getOffset()`.
     * The local date-time, offset and instant of the result will be the same as in this date-time.
     *
     * Setting the date-time to a fixed single offset means that any future
     * calculations, such as addition or subtraction, have no complex edge cases
     * due to time-zone rules.
     * This might also be useful when sending a zoned date-time across a network,
     * as most protocols, such as ISO-8601, only handle offsets, and not region-based time zones.
     *
     * @return ZonedDateTime
     */
    public function withFixedOffsetTimeZone() : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime, $this->timeZoneOffset);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified Period added.
     *
     * @param Period $period
     *
     * @return ZonedDateTime
     */
    public function plusPeriod(Period $period) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusPeriod($period), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified Duration added.
     *
     * @param Duration $duration
     *
     * @return ZonedDateTime
     */
    public function plusDuration(Duration $duration) : ZonedDateTime
    {
        return ZonedDateTime::ofInstant($this->instant->plus($duration), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in years added.
     *
     * @param int $years
     *
     * @return ZonedDateTime
     */
    public function plusYears(int $years) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusYears($years), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in months added.
     *
     * @param int $months
     *
     * @return ZonedDateTime
     */
    public function plusMonths(int $months) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusMonths($months), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in weeks added.
     *
     * @param int $weeks
     *
     * @return ZonedDateTime
     */
    public function plusWeeks(int $weeks) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusWeeks($weeks), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in days added.
     *
     * @param int $days
     *
     * @return ZonedDateTime
     */
    public function plusDays(int $days) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusDays($days), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in hours added.
     *
     * @param int $hours
     *
     * @return ZonedDateTime
     */
    public function plusHours(int $hours) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusHours($hours), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in minutes added.
     *
     * @param int $minutes
     *
     * @return ZonedDateTime
     */
    public function plusMinutes(int $minutes) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusMinutes($minutes), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in seconds added.
     *
     * @param int $seconds
     *
     * @return ZonedDateTime
     */
    public function plusSeconds(int $seconds) : ZonedDateTime
    {
        return ZonedDateTime::of($this->localDateTime->plusSeconds($seconds), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified Period subtracted.
     *
     * @param Period $period
     *
     * @return ZonedDateTime
     */
    public function minusPeriod(Period $period) : ZonedDateTime
    {
        return $this->plusPeriod($period->negated());
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified Duration subtracted.
     *
     * @param Duration $duration
     *
     * @return ZonedDateTime
     */
    public function minusDuration(Duration $duration) : ZonedDateTime
    {
        return $this->plusDuration($duration->negated());
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in years subtracted.
     *
     * @param int $years
     *
     * @return ZonedDateTime
     */
    public function minusYears(int $years) : ZonedDateTime
    {
        return $this->plusYears(- $years);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in months subtracted.
     *
     * @param int $months
     *
     * @return ZonedDateTime
     */
    public function minusMonths(int $months) : ZonedDateTime
    {
        return $this->plusMonths(- $months);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in weeks subtracted.
     *
     * @param int $weeks
     *
     * @return ZonedDateTime
     */
    public function minusWeeks(int $weeks) : ZonedDateTime
    {
        return $this->plusWeeks(- $weeks);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in days subtracted.
     *
     * @param int $days
     *
     * @return ZonedDateTime
     */
    public function minusDays(int $days) : ZonedDateTime
    {
        return $this->plusDays(- $days);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in hours subtracted.
     *
     * @param int $hours
     *
     * @return ZonedDateTime
     */
    public function minusHours(int $hours) : ZonedDateTime
    {
        return $this->plusHours(- $hours);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in minutes subtracted.
     *
     * @param int $minutes
     *
     * @return ZonedDateTime
     */
    public function minusMinutes(int $minutes) : ZonedDateTime
    {
        return $this->plusMinutes(- $minutes);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in seconds subtracted.
     *
     * @param int $seconds
     *
     * @return ZonedDateTime
     */
    public function minusSeconds(int $seconds) : ZonedDateTime
    {
        return $this->plusSeconds(- $seconds);
    }

    /**
     * Compares this ZonedDateTime with another.
     *
     * The comparison is performed on the instant.
     *
     * @param ZonedDateTime $that
     *
     * @return int [-1,0,1] If this zoned date-time is before, on, or after the given one.
     */
    public function compareTo(ZonedDateTime $that) : int
    {
        return $this->instant->compareTo($that->instant);
    }

    /**
     * Returns whether this ZonedDateTime equals another.
     *
     * @param ZonedDateTime $that
     *
     * @return bool
     */
    public function isEqualTo(ZonedDateTime $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this ZonedDateTime is after another.
     *
     * @param ZonedDateTime $that
     *
     * @return bool
     */
    public function isAfter(ZonedDateTime $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Returns whether this ZonedDateTime is after or equal to another.
     *
     * @param ZonedDateTime $that
     *
     * @return bool
     */
    public function isAfterOrEqualTo(ZonedDateTime $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns whether this ZonedDateTime is before another.
     *
     * @param ZonedDateTime $that
     *
     * @return bool
     */
    public function isBefore(ZonedDateTime $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns whether this ZonedDateTime is before or equal to another.
     *
     * @param ZonedDateTime $that
     *
     * @return bool
     */
    public function isBeforeOrEqualTo(ZonedDateTime $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * @param ZonedDateTime $from
     * @param ZonedDateTime $to
     *
     * @return bool
     */
    public function isBetweenInclusive(ZonedDateTime $from, ZonedDateTime $to) : bool
    {
        return $this->isAfterOrEqualTo($from) && $this->isBeforeOrEqualTo($to);
    }

    /**
     * @param ZonedDateTime $from
     * @param ZonedDateTime $to
     *
     * @return bool
     */
    public function isBetweenExclusive(ZonedDateTime $from, ZonedDateTime $to) : bool
    {
        return $this->isAfter($from) && $this->isBefore($to);
    }

    /**
     * Returns whether this ZonedDateTime is in the future, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     *
     * @param Clock|null $clock
     *
     * @return bool
     */
    public function isFuture(Clock $clock = null) : bool
    {
        return $this->instant->isFuture($clock);
    }

    /**
     * Returns whether this ZonedDateTime is in the past, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     *
     * @param Clock|null $clock
     *
     * @return bool
     */
    public function isPast(Clock $clock = null) : bool
    {
        return $this->instant->isPast($clock);
    }

    /**
     * Converts this ZonedDateTime to a native DateTime object.
     *
     * Note that the native DateTime object supports a precision up to the microsecond,
     * so the nanoseconds are rounded down to the nearest microsecond.
     *
     * @return \DateTime
     */
    public function toDateTime() : \DateTime
    {
        $second = $this->localDateTime->getSecond();

        // round down to the microsecond
        $nano = $this->localDateTime->getNano();
        $nano = 1000 * intdiv($nano, 1000);

        $dateTime = (string) $this->localDateTime->withNano($nano);
        $dateTimeZone = $this->timeZone->toDateTimeZone();

        $format = 'Y-m-d\TH:i';

        if ($second !== 0 || $nano !== 0) {
            $format .= ':s';

            if ($nano !== 0) {
                $format .= '.u';
            }
        }

        return \DateTime::createFromFormat($format, $dateTime, $dateTimeZone);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function toDateTimeImmutable() : \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromMutable($this->toDateTime());
    }

    /**
     * Serializes as a string using {@see ZonedDateTime::__toString()}.
     *
     * @return string
     */
    public function jsonSerialize() : string
    {
        return (string) $this;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        $string = $this->localDateTime . $this->timeZoneOffset;

        if ($this->timeZone instanceof TimeZoneRegion) {
            $string .= '[' . $this->timeZone . ']';
        }

        return $string;
    }
}
