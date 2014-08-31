<?php

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The nano-of-second field.
 */
class NanoOfSecond
{
    /**
     * The field name.
     */
    const NAME = 'nano-of-second';

    /**
     * @param integer $nanoOfSecond The nano-of-second to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the nano-of-second is not valid.
     */
    public static function check($nanoOfSecond)
    {
        if ($nanoOfSecond < 0 || $nanoOfSecond > 999999999) {
            throw DateTimeException::fieldNotInRange(self::NAME, $nanoOfSecond, 0, 999999999);
        }
    }
}
