<?php

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\Locale\Locale;

/**
 * A date-time with a time-zone in the ISO-8601 calendar system.
 *
 * A ZonedDateTime can be viewed as a LocalDateTime along with a time zone
 * and targets a specific point in time.
 */
class ZonedDateTime extends ReadableInstant
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
     * A native DateTime object to perform some of the calculations.
     *
     * DateTime does not support fractions of seconds, so this object is the equivalent
     * of this ZonedDateTime with the fraction truncated.
     *
     * @var \DateTime
     */
    private $dateTime;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param LocalDateTime  $localDateTime
     * @param TimeZoneOffset $offset
     * @param TimeZone       $zone
     * @param \DateTime      $dt
     */
    private function __construct(LocalDateTime $localDateTime, TimeZoneOffset $offset, TimeZone $zone, \DateTime $dt)
    {
        $this->localDateTime  = $localDateTime;
        $this->timeZone       = $zone;
        $this->timeZoneOffset = $offset;
        $this->dateTime       = $dt;
    }

    /**
     * @param LocalDateTime       $dateTime
     * @param TimeZone            $timeZone
     * @param TimeZoneOffset|null $preferredOffset
     *
     * @return ZonedDateTime
     *
     * @todo preferredOffset
     */
    public static function of(LocalDateTime $dateTime, TimeZone $timeZone, TimeZoneOffset $preferredOffset = null)
    {
        $dtz = $timeZone->toDateTimeZone();
        $dt = new \DateTime((string) $dateTime, $dtz);

        if ($timeZone instanceof TimeZoneOffset) {
            $timeZoneOffset = $timeZone;
        } else {
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dtz->getOffset($dt));
        }

        return new ZonedDateTime($dateTime, $timeZoneOffset, $timeZone, $dt);
    }

    /**
     * Creates a DateTime representing the current time, in the given time zone.
     *
     * @param TimeZone $timeZone
     *
     * @return ZonedDateTime
     */
    public static function now(TimeZone $timeZone)
    {
        return ZonedDateTime::ofInstant(Instant::now(), $timeZone);
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
    public static function from(DateTimeParseResult $result)
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
            $timeZone,
            $timeZoneOffset
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
    public static function parse($text, DateTimeParser $parser = null)
    {
        if (! $parser) {
            $parser = IsoParsers::zonedDateTime();
        }

        return ZonedDateTime::from($parser->parse($text));
    }

    /**
     * Creates a ZonedDateTime from an instant and a time zone.
     *
     * @param Instant  $instant  The instant.
     * @param TimeZone $timeZone The time zone.
     *
     * @return ZonedDateTime
     */
    public static function ofInstant(Instant $instant, TimeZone $timeZone)
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
            $timeZoneOffset = TimeZoneOffset::ofTotalSeconds($dateTimeZone->getOffset($dateTime));
        }

        return new ZonedDateTime($localDateTime, $timeZoneOffset, $timeZone, $dateTime);
    }

    /**
     * Creates a ZonedDateTime on the specific date & time zone, at midnight.
     *
     * @param LocalDate $date     The date.
     * @param TimeZone  $timeZone The time zone.
     *
     * @return ZonedDateTime
     */
    public static function createFromDate(LocalDate $date, TimeZone $timeZone)
    {
        return ZonedDateTime::createFromDateAndTime($date, LocalTime::midnight(), $timeZone);
    }

    /**
     * Creates a ZonedDateTime on the specific date, time & time zone.
     *
     * @param LocalDate $date     The date.
     * @param LocalTime $time     The time.
     * @param TimeZone  $timeZone The time zone.
     *
     * @return ZonedDateTime
     *
     * @todo fromLocalDateTime() ? fromLocal() ? ofLocal() ? of()?
     */
    public static function createFromDateAndTime(LocalDate $date, LocalTime $time, TimeZone $timeZone)
    {
        return ZonedDateTime::of(LocalDateTime::ofDateTime($date, $time), $timeZone);
    }

    /**
     * Returns the `LocalDateTime` part of this `ZonedDateTime`.
     *
     * @return LocalDateTime
     */
    public function getDateTime()
    {
        return $this->localDateTime;
    }

    /**
     * Returns the `LocalDate` part of this `ZonedDateTime`.
     *
     * @return LocalDate
     */
    public function getDate()
    {
        return $this->localDateTime->getDate();
    }

    /**
     * Returns the `LocalTime` part of this `ZonedDateTime`.
     *
     * @return LocalTime
     */
    public function getTime()
    {
        return $this->localDateTime->getTime();
    }

    /**
     * @return integer
     */
    public function getYear()
    {
        return $this->localDateTime->getYear();
    }

    /**
     * @return integer
     */
    public function getMonth()
    {
        return $this->localDateTime->getMonth();
    }

    /**
     * @return integer
     */
    public function getDay()
    {
        return $this->localDateTime->getDay();
    }

    /**
     * @return DayOfWeek
     */
    public function getDayOfWeek()
    {
        return $this->localDateTime->getDayOfWeek();
    }

    /**
     * @return integer
     */
    public function getDayOfYear()
    {
        return $this->localDateTime->getDayOfYear();
    }

    /**
     * @return integer
     */
    public function getHour()
    {
        return $this->localDateTime->getHour();
    }

    /**
     * @return integer
     */
    public function getMinute()
    {
        return $this->localDateTime->getMinute();
    }

    /**
     * @return integer
     */
    public function getSecond()
    {
        return $this->localDateTime->getSecond();
    }

    /**
     * Returns the time-zone, region or offset.
     *
     * @return TimeZone
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Returns the time-zone offset.
     *
     * @return TimeZoneOffset
     */
    public function getTimeZoneOffset()
    {
        return $this->timeZoneOffset;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstant()
    {
        return Instant::of($this->dateTime->getTimestamp(), $this->localDateTime->getNano());
    }

    /**
     * Returns a copy of this ZonedDateTime with a different date.
     *
     * @param LocalDate $date
     *
     * @return ZonedDateTime
     */
    public function withDate(LocalDate $date)
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
    public function withTime(LocalTime $time)
    {
        return ZonedDateTime::of($this->localDateTime->withTime($time), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the year altered.
     *
     * @param integer $year
     *
     * @return ZonedDateTime
     */
    public function withYear($year)
    {
        return ZonedDateTime::of($this->localDateTime->withYear($year), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the month-of-year altered.
     *
     * @param integer $month
     *
     * @return ZonedDateTime
     */
    public function withMonth($month)
    {
        return ZonedDateTime::of($this->localDateTime->withMonth($month), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the day-of-month altered.
     *
     * @param integer $day
     *
     * @return ZonedDateTime
     */
    public function withDay($day)
    {
        return ZonedDateTime::of($this->localDateTime->withDay($day), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the hour-of-day altered.
     *
     * @param integer $hour
     *
     * @return ZonedDateTime
     */
    public function withHour($hour)
    {
        return ZonedDateTime::of($this->localDateTime->withHour($hour), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the minute-of-hour altered.
     *
     * @param integer $minute
     *
     * @return ZonedDateTime
     */
    public function withMinute($minute)
    {
        return ZonedDateTime::of($this->localDateTime->withMinute($minute), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the second-of-minute altered.
     *
     * @param integer $second
     *
     * @return ZonedDateTime
     */
    public function withSecond($second)
    {
        return ZonedDateTime::of($this->localDateTime->withSecond($second), $this->timeZone);
    }

    /**
     * Returns a copy of this `ZonedDateTime` with a different time-zone,
     * retaining the local date-time if possible.
     *
     * @param TimeZone $timeZone The time-zone to change to.
     *
     * @return ZonedDateTime
     */
    public function withTimeZoneSameLocal(TimeZone $timeZone)
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
    public function withTimeZoneSameInstant(TimeZone $timeZone)
    {
        return ZonedDateTime::ofInstant($this->getInstant(), $timeZone);
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
    public function withFixedOffsetTimeZone()
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
    public function plusPeriod(Period $period)
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
    public function plusDuration(Duration $duration)
    {
        return ZonedDateTime::ofInstant($this->getInstant()->plus($duration), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in years added.
     *
     * @param integer $years
     *
     * @return ZonedDateTime
     */
    public function plusYears($years)
    {
        return ZonedDateTime::of($this->localDateTime->plusYears($years), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in months added.
     *
     * @param integer $months
     *
     * @return ZonedDateTime
     */
    public function plusMonths($months)
    {
        return ZonedDateTime::of($this->localDateTime->plusMonths($months), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in weeks added.
     *
     * @param integer $weeks
     *
     * @return ZonedDateTime
     */
    public function plusWeeks($weeks)
    {
        return ZonedDateTime::of($this->localDateTime->plusWeeks($weeks), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in days added.
     *
     * @param integer $days
     *
     * @return ZonedDateTime
     */
    public function plusDays($days)
    {
        return ZonedDateTime::of($this->localDateTime->plusDays($days), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in hours added.
     *
     * @param integer $hours
     *
     * @return ZonedDateTime
     */
    public function plusHours($hours)
    {
        return ZonedDateTime::of($this->localDateTime->plusHours($hours), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in minutes added.
     *
     * @param integer $minutes
     *
     * @return ZonedDateTime
     */
    public function plusMinutes($minutes)
    {
        return ZonedDateTime::of($this->localDateTime->plusMinutes($minutes), $this->timeZone);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in seconds added.
     *
     * @param integer $seconds
     *
     * @return ZonedDateTime
     */
    public function plusSeconds($seconds)
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
    public function minusPeriod(Period $period)
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
    public function minusDuration(Duration $duration)
    {
        return $this->plusDuration($duration->negated());
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in years subtracted.
     *
     * @param integer $years
     *
     * @return ZonedDateTime
     */
    public function minusYears($years)
    {
        return $this->plusYears(- $years);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in months subtracted.
     *
     * @param integer $months
     *
     * @return ZonedDateTime
     */
    public function minusMonths($months)
    {
        return $this->plusMonths(- $months);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in weeks subtracted.
     *
     * @param integer $weeks
     *
     * @return ZonedDateTime
     */
    public function minusWeeks($weeks)
    {
        return $this->plusWeeks(- $weeks);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in days subtracted.
     *
     * @param integer $days
     *
     * @return ZonedDateTime
     */
    public function minusDays($days)
    {
        return $this->plusDays(- $days);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in hours subtracted.
     *
     * @param integer $hours
     *
     * @return ZonedDateTime
     */
    public function minusHours($hours)
    {
        return $this->plusHours(- $hours);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in minutes subtracted.
     *
     * @param integer $minutes
     *
     * @return ZonedDateTime
     */
    public function minusMinutes($minutes)
    {
        return $this->plusMinutes(- $minutes);
    }

    /**
     * Returns a copy of this ZonedDateTime with the specified period in seconds subtracted.
     *
     * @param integer $seconds
     *
     * @return ZonedDateTime
     */
    public function minusSeconds($seconds)
    {
        return $this->plusSeconds(- $seconds);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $string = $this->localDateTime . $this->timeZoneOffset;

        if ($this->timeZone instanceof TimeZoneRegion) {
            $string .= '[' . $this->timeZone . ']';
        }

        return $string;
    }

    /**
     * @param \Brick\Locale\Locale $locale
     *
     * @return string
     */
    public function format(Locale $locale)
    {
        return $this->getDateTime()->format($locale);
    }
}
