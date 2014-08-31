<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The total number of seconds in a time-zone offset, from -64800 to 64800 (-18:00 to +18:00).
 *
 * The offset is relative to UTC/Greenwich.
 */
class TimeZoneOffsetTotalSeconds
{
    /**
     * The field name.
     */
    const NAME = 'time-zone-offset-total-seconds';

    /**
     * The absolute maximum seconds of the time-zone offset.
     */
    const MAX_SECONDS = 64800;

    /**
     * @param integer $offsetSeconds The offset-seconds to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the offset-seconds is not valid.
     */
    public static function check($offsetSeconds)
    {
        if ($offsetSeconds < -self::MAX_SECONDS || $offsetSeconds > self::MAX_SECONDS) {
            throw DateTimeException::fieldNotInRange(self::NAME, $offsetSeconds, -self::MAX_SECONDS, self::MAX_SECONDS);
        }
    }
}
