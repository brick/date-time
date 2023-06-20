<?php

declare(strict_types=1);

namespace Brick\DateTime;

use RuntimeException;

use function sprintf;

/**
 * This exception is used to indicate problems with creating, querying and manipulating date-time objects.
 */
class DateTimeException extends RuntimeException
{
    /**
     * @param string $field The tested field.
     * @param int    $value The actual value.
     * @param int    $min   The minimum allowed value.
     * @param int    $max   The maximum allowed value.
     */
    public static function fieldNotInRange(string $field, int $value, int $min, int $max): self
    {
        return new self("Invalid $field: $value is not in the range $min to $max.");
    }

    public static function timeZoneOffsetSecondsMustBeMultipleOf60(int $offsetSeconds): self
    {
        return new self(sprintf('The time zone offset of %d seconds is not a multiple of 60. Sub-minute offsets are only supported in PHP 8.1.7 and above.', $offsetSeconds));
    }

    public static function unknownTimeZoneRegion(string $region): self
    {
        return new self(sprintf('Unknown time zone region "%s".', $region));
    }
}
