<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use DateTimeImmutable;
use DateTimeZone;
use Stringable;

use const PHP_VERSION_ID;

/**
 * A time-zone. This is the parent class for `TimeZoneOffset` and `TimeZoneRegion`.
 *
 * * `TimeZoneOffset` represents a fixed offset from UTC such as `+02:00`.
 * * `TimeZoneRegion` represents a geographical region such as `Europe/London`.
 */
abstract class TimeZone implements Stringable
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
     *
     * @psalm-return non-empty-string
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

    public static function fromNativeDateTimeZone(DateTimeZone $dateTimeZone): TimeZone
    {
        $parsed = TimeZone::parse($dateTimeZone->getName());

        /**
         * PHP >= 8.1.7 supports sub-minute offsets, but truncates the seconds in getName(). Only getOffset() returns
         * the correct offset including seconds, so let's use it to make a correction if we have an offset-based TZ.
         * This has been fixed in PHP 8.1.20 and PHP 8.2.7.
         */
        if ($parsed instanceof TimeZoneOffset
            && (
                (PHP_VERSION_ID >= 8_01_07 && PHP_VERSION_ID < 8_01_20)
                || (PHP_VERSION_ID >= 8_02_00 && PHP_VERSION_ID < 8_02_07)
            )
        ) {
            return TimeZoneOffset::ofTotalSeconds($dateTimeZone->getOffset(new DateTimeImmutable()));
        }

        return $parsed;
    }

    /**
     * Returns an equivalent native `DateTimeZone` object for this TimeZone.
     */
    abstract public function toNativeDateTimeZone(): DateTimeZone;

    /**
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->getId();
    }
}
