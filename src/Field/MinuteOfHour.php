<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The minute-of-hour field.
 */
final class MinuteOfHour
{
    /**
     * The field name.
     */
    public const NAME = 'minute-of-hour';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '[0-9]{2}';

    /**
     * @param int $minuteOfHour The minute-of-hour to check.
     *
     * @throws DateTimeException If the minute-of-hour is not valid.
     */
    public static function check(int $minuteOfHour): void
    {
        if ($minuteOfHour < 0 || $minuteOfHour > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $minuteOfHour, 0, 59);
        }
    }
}
