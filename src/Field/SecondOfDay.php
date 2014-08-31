<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The second-of-day field.
 */
class SecondOfDay
{
    /**
     * The field name.
     */
    const NAME = 'second-of-day';

    /**
     * @param integer $secondOfDay The second-of-day to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the second-of-day is not valid.
     */
    public static function check($secondOfDay)
    {
        if ($secondOfDay < 0 || $secondOfDay > 86399) {
            throw DateTimeException::fieldNotInRange(self::NAME, $secondOfDay, 0, 86399);
        }
    }
}
