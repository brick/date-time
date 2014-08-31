<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The hour-of-day field.
 */
class HourOfDay
{
    /**
     * The field name.
     */
    const NAME = 'hour-of-day';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '[0-9]{2}';

    /**
     * @param integer $hourOfDay The hour-of-day to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the hour-of-day is not valid.
     */
    public static function check($hourOfDay)
    {
        if ($hourOfDay < 0 || $hourOfDay > 23) {
            throw DateTimeException::fieldNotInRange(self::NAME, $hourOfDay, 0, 23);
        }
    }
}
