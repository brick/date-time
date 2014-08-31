<?php

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;

/**
 * A time-zone. This is the parent class for `TimeZoneOffset` and `TimeZoneRegion`.
 *
 * * `TimeZoneOffset` represents a fixed offset from UTC such as `+02:00`.
 * * `TimeZoneRegion` represents a geographical region such as `Europe/London`.
 */
abstract class TimeZone
{
    /**
     * Obtains an instance of `TimeZone` from a string representation.
     *
     * @param string $text
     *
     * @return TimeZone
     *
     * @throws \Brick\DateTime\Parser\DateTimeParseException
     */
    public static function parse($text)
    {
        $text = (string) $text;

        if ($text === 'Z' || $text === 'z') {
            return TimeZoneOffset::utc();
        }

        if ($text === '') {
            throw new DateTimeParseException('The string is empty.');
        }

        if ($text[0] === '+' || $text[0] === '-') {
            return TimeZoneOffset::parse($text);
        }

        return TimeZoneRegion::parse($text);
    }

    /**
     * @return TimeZoneOffset
     */
    public static function utc()
    {
        return TimeZoneOffset::utc();
    }

    /**
     * Returns the unique time-zone ID.
     *
     * @return string
     */
    abstract public function getId();

    /**
     * Returns the offset from UTC at the given instant.
     *
     * @param ReadableInstant $pointInTime The instant.
     *
     * @return integer The offset from UTC in seconds.
     */
    abstract public function getOffset(ReadableInstant $pointInTime);

    /**
     * @param TimeZone $other
     *
     * @return boolean
     */
    public function isEqualTo(TimeZone $other)
    {
        return $this->getId() === $other->getId();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * @param \DateTimeZone $dateTimeZone
     *
     * @return TimeZone
     */
    public static function fromDateTimeZone(\DateTimeZone $dateTimeZone)
    {
        return TimeZone::parse($dateTimeZone->getName());
    }

    /**
     * Returns an equivalent native `DateTimeZone` object for this TimeZone.
     *
     * @return \DateTimeZone The native DateTimeZone object.
     */
    abstract public function toDateTimeZone();
}
