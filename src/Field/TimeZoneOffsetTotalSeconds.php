<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The total number of seconds in a time-zone offset, from -64800 to 64800 (-18:00 to +18:00).
 *
 * The offset is relative to UTC/Greenwich.
 */
final class TimeZoneOffsetTotalSeconds
{
    /**
     * The field name.
     */
    public const NAME = 'time-zone-offset-total-seconds';

    /**
     * The absolute maximum seconds of the time-zone offset.
     */
    public const MAX_SECONDS = 64800;

    /**
     * @param int $offsetSeconds The offset-seconds to check.
     *
     * @throws DateTimeException If the offset-seconds is not valid.
     *
     * @psalm-pure
     */
    public static function check(int $offsetSeconds) : void
    {
        if ($offsetSeconds < -self::MAX_SECONDS || $offsetSeconds > self::MAX_SECONDS) {
            throw DateTimeException::fieldNotInRange(self::NAME, $offsetSeconds, -self::MAX_SECONDS, self::MAX_SECONDS);
        }
    }
}
