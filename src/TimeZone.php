<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use DateTimeZone;

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
     * @throws DateTimeParseException
     */
    public static function parse(string $text): TimeZone
    {
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

    public static function utc(): TimeZoneOffset
    {
        return TimeZoneOffset::utc();
    }

    /**
     * Returns the unique time-zone ID.
     */
    abstract public function getId(): string;

    /**
     * Returns the offset from UTC at the given instant.
     *
     * @param Instant $pointInTime The instant.
     *
     * @return int The offset from UTC in seconds.
     */
    abstract public function getOffset(Instant $pointInTime): int;

    public function isEqualTo(TimeZone $other): bool
    {
        return $this->getId() === $other->getId();
    }

    /**
     * @deprecated please use fromNativeDateTimeZone instead
     */
    public static function fromDateTimeZone(DateTimeZone $dateTimeZone): TimeZone
    {
        return self::fromNativeDateTimeZone($dateTimeZone);
    }

    public static function fromNativeDateTimeZone(DateTimeZone $dateTimeZone): TimeZone
    {
        return TimeZone::parse($dateTimeZone->getName());
    }

    /**
     * Returns an equivalent native `DateTimeZone` object for this TimeZone.
     *
     * @deprecated please use toNativeDateTimeZone instead
     */
    abstract public function toDateTimeZone(): DateTimeZone;

    /**
     * Returns an equivalent native `DateTimeZone` object for this TimeZone.
     */
    public function toNativeDateTimeZone(): DateTimeZone
    {
        /**
         * @psalm-suppress DeprecatedMethod
         */
        return $this->toDateTimeZone();
    }

    public function __toString(): string
    {
        return $this->getId();
    }
}
