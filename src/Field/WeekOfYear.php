<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use Brick\DateTime\Month;

/**
 * The week-of-year field.
 */
final class WeekOfYear
{
    /**
     * The field name.
     */
    public const NAME = 'week-of-year';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '[0-9]{2}';

    /**
     * @param int      $weekOfYear The week-of-year to check.
     * @param int|null $year       An optional year to check against, validated.
     *
     * @throws DateTimeException If the week-of-year is not valid.
     */
    public static function check(int $weekOfYear, ?int $year = null): void
    {
        if ($weekOfYear < 1 || $weekOfYear > 53) {
            throw DateTimeException::fieldNotInRange(self::NAME, $weekOfYear, 1, 53);
        }

        if ($weekOfYear === 53 && $year !== null && ! self::is53WeekYear($year)) {
            throw new DateTimeException("Year $year does not have 53 weeks");
        }
    }

    /**
     * Returns whether the given year has 53 weeks.
     *
     * A year as 53 weeks if the year starts on a Thursday, or Wednesday in a leap year.
     *
     * @param int $year The year, validated.
     *
     * @return bool True if 53 weeks, false if 52 weeks.
     */
    public static function is53WeekYear(int $year): bool
    {
        $date = LocalDate::of($year, Month::JANUARY, 1);
        $dayOfWeek = $date->getDayOfWeek();

        return $dayOfWeek === DayOfWeek::THURSDAY
            || ($dayOfWeek === DayOfWeek::WEDNESDAY && $date->isLeapYear());
    }

    /**
     * Returns the number of weeks in the given year.
     *
     * @param int $year The year, validated.
     *
     * @return int The number of weeks in the year.
     */
    public static function getWeeksInYear(int $year): int
    {
        return self::is53WeekYear($year) ? 53 : 52;
    }
}
