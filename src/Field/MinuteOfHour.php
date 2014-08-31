<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The minute-of-hour field.
 */
class MinuteOfHour
{
    /**
     * The field name.
     */
    const NAME = 'minute-of-hour';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '[0-9]{2}';

    /**
     * @param integer $minuteOfHour The minute-of-hour to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the minute-of-hour is not valid.
     */
    public static function check($minuteOfHour)
    {
        if ($minuteOfHour < 0 || $minuteOfHour > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $minuteOfHour, 0, 59);
        }
    }
}
