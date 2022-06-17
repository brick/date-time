<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The hour-of-day field.
 */
final class HourOfDay
{
    /**
     * The field name.
     */
    public const NAME = 'hour-of-day';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '[0-9]{2}';

    /**
     * @param int $hourOfDay The hour-of-day to check.
     *
     * @throws DateTimeException If the hour-of-day is not valid.
     */
    public static function check(int $hourOfDay): void
    {
        if ($hourOfDay < 0 || $hourOfDay > 23) {
            throw DateTimeException::fieldNotInRange(self::NAME, $hourOfDay, 0, 23);
        }
    }
}
