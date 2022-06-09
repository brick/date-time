<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use DateTimeZone;

/**
 * A geographical region where the same time-zone rules apply, such as `Europe/London`.
 */
final class TimeZoneRegion extends TimeZone
{
    private DateTimeZone $zone;

    /**
     * Private constructor. Use a factory method to obtain an instance.
     *
     * @param DateTimeZone $zone
     */
    private function __construct(DateTimeZone $zone)
    {
        $this->zone = $zone;
    }

    /**
     * @param string $id The region id.
     *
     * @throws DateTimeException If the region id is invalid.
     */
    public static function of(string $id) : TimeZoneRegion
    {
        if ($id === '' || $id === 'Z' || $id === 'z' || $id[0] === '+' || $id[0] === '-') {
            // DateTimeZone would accept offsets, but TimeZoneRegion targets regions only.
            throw DateTimeException::unknownTimeZoneRegion($id);
        }

        try {
            return new TimeZoneRegion(new DateTimeZone($id));
        } catch (\Exception $e) {
            throw DateTimeException::unknownTimeZoneRegion($id);
        }
    }

    /**
     * @throws DateTimeException      If the region is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result) : TimeZoneRegion
    {
        $region = $result->getField(Field\TimeZoneRegion::NAME);

        return TimeZoneRegion::of($region);
    }

    /**
     * Returns all the available time-zone identifiers.
     *
     * @param bool $includeObsolete Whether to include obsolete time-zone identifiers. Defaults to false.
     *
     * @return string[] An array of time-zone identifiers.
     */
    public static function getAllIdentifiers(bool $includeObsolete = false) : array
    {
        $identifiers = DateTimeZone::listIdentifiers(
            $includeObsolete
                ? DateTimeZone::ALL_WITH_BC
                : DateTimeZone::ALL
        );

        \assert(\is_array($identifiers));

        return $identifiers;
    }

    /**
     * Returns the time-zone identifiers for the given country.
     *
     * If the country code is not known, an empty array is returned.
     *
     * @param string $countryCode The ISO 3166-1 two-letter country code.
     *
     * @return string[] An array of time-zone identifiers.
     */
    public static function getIdentifiersForCountry(string $countryCode) : array
    {
        $identifiers = DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $countryCode);

        \assert(\is_array($identifiers));

        return $identifiers;
    }

    /**
     * Parses a region id, such as 'Europe/London'.
     *
     * @throws DateTimeParseException
     */
    public static function parse(string $text, ?DateTimeParser $parser = null) : TimeZoneRegion
    {
        if (! $parser) {
            $parser = IsoParsers::timeZoneRegion();
        }

        return TimeZoneRegion::from($parser->parse($text));
    }

    public function getId() : string
    {
        return $this->zone->getName();
    }

    public function getOffset(Instant $pointInTime) : int
    {
        $dateTime = new \DateTime('@' . $pointInTime->getEpochSecond(), new DateTimeZone('UTC'));

        return $this->zone->getOffset($dateTime);
    }

    /**
     * @deprecated please use toNativeDateTimeZone instead
     */
    public function toDateTimeZone() : DateTimeZone
    {
        return clone $this->zone;
    }
}
