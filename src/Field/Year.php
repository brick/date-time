<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The proleptic year field.
 */
final class Year
{
    /**
     * The field name.
     */
    const NAME = 'year';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '\-?[0-9]{4,9}';

    /**
     * The minimum allowed value.
     */
    const MIN_VALUE = -999999;

    /**
     * The maximum allowed value.
     */
    const MAX_VALUE = 999999;

    /**
     * @param integer $year The year to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the year is not valid.
     */
    public static function check($year)
    {
        if ($year < self::MIN_VALUE || $year > self::MAX_VALUE) {
            throw DateTimeException::fieldNotInRange(self::NAME, $year, self::MIN_VALUE, self::MAX_VALUE);
        }
    }

    /**
     * @param integer $year The year, fully validated.
     *
     * @return boolean
     */
    public static function isLeap($year)
    {
        return (($year & 3) === 0) && (($year % 100) !== 0 || ($year % 400) === 0);
    }
}
