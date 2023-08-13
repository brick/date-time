<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The nano-of-second field.
 */
final class NanoOfSecond
{
    /**
     * The field name.
     */
    public const NAME = 'nano-of-second';

    /**
     * @param int $nanoOfSecond The nano-of-second to check.
     *
     * @throws DateTimeException If the nano-of-second is not valid.
     */
    public static function check(int $nanoOfSecond): void
    {
        if ($nanoOfSecond < 0 || $nanoOfSecond > 999_999_999) {
            throw DateTimeException::fieldNotInRange(self::NAME, $nanoOfSecond, 0, 999_999_999);
        }
    }
}
