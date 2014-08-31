<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The minute part of the time-zone offset.
 */
class TimeZoneOffsetMinute
{
    /**
     * The field name.
     */
    const NAME = 'time-zone-offset-minute';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = MinuteOfHour::PATTERN;

    /**
     * @param integer $offsetMinute The offset-minute to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the offset-minute is not valid.
     */
    public static function check($offsetMinute)
    {
        if ($offsetMinute < -59 || $offsetMinute > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $offsetMinute, -59, 59);
        }
    }
}
