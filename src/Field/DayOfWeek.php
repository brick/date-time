<?php

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
     * @param integer $dayOfWeek The day-of-week to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the day-of-week is not valid.
     */
    public static function check($dayOfWeek)
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw DateTimeException::fieldNotInRange(self::NAME, $dayOfWeek, 1, 7);
        }
    }
}
