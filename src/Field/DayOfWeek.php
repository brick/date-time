<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The day-of-week field.
 */
final class DayOfWeek
{
    /**
     * The field name.
     */
    const NAME = 'day-of-week';

    /**
     * @param int $dayOfWeek The day-of-week to check.
     *
     * @return void
     *
     * @throws DateTimeException If the day-of-week is not valid.
     */
    public static function check(int $dayOfWeek)
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw DateTimeException::fieldNotInRange(self::NAME, $dayOfWeek, 1, 7);
        }
    }
}
