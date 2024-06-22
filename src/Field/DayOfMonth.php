<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The day-of-month field.
 */
final class DayOfMonth
{
    /**
     * The field name.
     */
    public const NAME = 'day-of-month';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '[0-9]{2}';

    /**
     * @param int             $dayOfMonth  The day-of-month to check.
     * @param int<1, 12>|null $monthOfYear An optional month-of-year to check against.
     * @param int|null        $year        An optional year to check against, validated.
     *
     * @throws DateTimeException If the day-of-month is not valid.
     *
     * @psalm-assert int<1, 31> $dayOfMonth
     */
    public static function check(int $dayOfMonth, ?int $monthOfYear = null, ?int $year = null): void
    {
        if ($dayOfMonth < 1 || $dayOfMonth > 31) {
            throw DateTimeException::fieldNotInRange(self::NAME, $dayOfMonth, 1, 31);
        }

        if ($monthOfYear === null) {
            return;
        }

        $monthLength = MonthOfYear::getLength($monthOfYear, $year);

        if ($dayOfMonth > $monthLength) {
            if ($dayOfMonth === 29) {
                throw new DateTimeException("Invalid date February 29 as $year is not a leap year");
            }

            $monthName = MonthOfYear::getName($monthOfYear);

            throw new DateTimeException("Invalid date $monthName $dayOfMonth");
        }
    }
}
