<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The hour part of the time-zone offset.
 */
final class TimeZoneOffsetHour
{
    /**
     * The field name.
     */
    public const NAME = 'time-zone-offset-hour';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = HourOfDay::PATTERN;

    /**
     * @param int $offsetHour The offset-hour to check.
     *
     * @throws DateTimeException If the offset-hour is not valid.
     */
    public static function check(int $offsetHour): void
    {
        if ($offsetHour < -18 || $offsetHour > 18) {
            throw DateTimeException::fieldNotInRange(self::NAME, $offsetHour, -18, 18);
        }
    }
}
