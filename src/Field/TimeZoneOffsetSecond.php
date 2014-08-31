<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The second part of the time-zone offset.
 */
class TimeZoneOffsetSecond
{
    /**
     * The field name.
     */
    const NAME = 'time-zone-offset-second';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = SecondOfMinute::PATTERN;

    /**
     * @param integer $offsetSecond The offset-second to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the offset-second is not valid.
     */
    public static function check($offsetSecond)
    {
        if ($offsetSecond < -59 || $offsetSecond > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $offsetSecond, -59, 59);
        }
    }
}
