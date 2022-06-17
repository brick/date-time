<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The second-of-day field.
 */
final class SecondOfDay
{
    /**
     * The field name.
     */
    public const NAME = 'second-of-day';

    /**
     * @param int $secondOfDay The second-of-day to check.
     *
     * @throws DateTimeException If the second-of-day is not valid.
     */
    public static function check(int $secondOfDay): void
    {
        if ($secondOfDay < 0 || $secondOfDay > 86399) {
            throw DateTimeException::fieldNotInRange(self::NAME, $secondOfDay, 0, 86399);
        }
    }
}
