<?php

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
    const NAME = 'day-of-month';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '[0-9]{2}';

    /**
     * @param integer      $dayOfMonth  The day-of-month to check, validated as an integer.
     * @param integer|null $monthOfYear An optional month-of-year to check against, fully validated.
     * @param integer|null $year        An optional year to check against, fully validated.
     *
     * @return void
     *
     * @throws DateTimeException If the day-of-month is not valid.
     */
    public static function check($dayOfMonth, $monthOfYear = null, $year = null)
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
            } else {
                $monthName = MonthOfYear::getName($monthOfYear);

                throw new DateTimeException("Invalid date $monthName $dayOfMonth");
            }
        }
    }
}
