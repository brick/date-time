<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * This exception is used to indicate problems with creating, querying and manipulating date-time objects.
 */
class DateTimeException extends \RuntimeException
{
    /**
     * @param string $field  The tested field.
     * @param int    $value  The actual value.
     * @param int    $min    The minimum allowed value.
     * @param int    $max    The maximum allowed value.
     *
     * @psalm-pure
     */
    public static function fieldNotInRange(string $field, int $value, int $min, int $max) : self
    {
        return new DateTimeException("Invalid $field: $value is not in the range $min to $max.");
    }

    /**
     * @psalm-pure
     */
    public static function unknownTimeZoneRegion(string $region) : self
    {
        return new self(\sprintf('Unknown time zone region "%s".', $region));
    }
}
