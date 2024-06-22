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
    public const NAME = 'day-of-week';

    /**
     * @param int $dayOfWeek The day-of-week to check.
     *
     * @throws DateTimeException If the day-of-week is not valid.
     *
     * @psalm-assert int<1, 7> $dayOfWeek
     */
    public static function check(int $dayOfWeek): void
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw DateTimeException::fieldNotInRange(self::NAME, $dayOfWeek, 1, 7);
        }
    }
}
