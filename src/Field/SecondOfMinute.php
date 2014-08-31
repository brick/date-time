<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The second-of-minute field.
 */
class SecondOfMinute
{
    /**
     * The field name.
     */
    const NAME = 'second-of-minute';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '[0-9]{2}';

    /**
     * @param integer $secondOfMinute The second-of-minute to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the second-of-minute is not valid.
     */
    public static function check($secondOfMinute)
    {
        if ($secondOfMinute < 0 || $secondOfMinute > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $secondOfMinute, 0, 59);
        }
    }
}
