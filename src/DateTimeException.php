<?php

namespace Brick\DateTime;

/**
 * This exception is used to indicate problems with creating, querying
 * and manipulating date-time objects.
 */
class DateTimeException extends \RuntimeException
{
    /**
     * @param string  $field  The tested field.
     * @param integer $value  The actual value.
     * @param integer $min    The minimum allowed value.
     * @param integer $max    The maximum allowed value.
     *
     * @return DateTimeException
     */
    public static function fieldNotInRange($field, $value, $min, $max)
    {
        return new DateTimeException("Invalid $field: $value is not in the range $min to $max");
    }

    /**
     * @param string $region
     *
     * @return DateTimeException
     */
    public static function unknownTimeZoneRegion($region)
    {
        return new self(sprintf('Unknown time zone region "%s".', $region));
    }
}
