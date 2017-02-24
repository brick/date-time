<?php

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
     * @return DateTimeException
     */
    public static function fieldNotInRange(string $field, int $value, int $min, int $max) : self
    {
        return new DateTimeException("Invalid $field: $value is not in the range $min to $max.");
    }

    /**
     * @param DateTimeAccessor $accessor
     * @param string           $field
     *
     * @return DateTimeException
     */
    public static function unsupportedField(DateTimeAccessor $accessor, string $field) : self
    {
        return new DateTimeException(sprintf('Field %s is not supported by %s.', $field, get_class($accessor)));
    }

    /**
     * @param string $region
     *
     * @return DateTimeException
     */
    public static function unknownTimeZoneRegion(string $region) : self
    {
        return new self(sprintf('Unknown time zone region "%s".', $region));
    }
}
