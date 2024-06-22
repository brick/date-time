<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The month-of-year field.
 */
final class MonthOfYear
{
    /**
     * The field name.
     */
    public const NAME = 'month-of-year';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '[0-9]{2}';

    /**
     * @param int $monthOfYear The month-of-year to check.
     *
     * @throws DateTimeException If the month-of-year is not valid.
     *
     * @psalm-assert int<1, 12> $monthOfYear
     */
    public static function check(int $monthOfYear): void
    {
        if ($monthOfYear < 1 || $monthOfYear > 12) {
            throw DateTimeException::fieldNotInRange(self::NAME, $monthOfYear, 1, 12);
        }
    }

    /**
     * Returns the length of the given month-of-year.
     *
     * If no year is given, the highest value (29) is returned for the month of February.
     *
     * @param int      $monthOfYear The month-of-year, validated.
     * @param int|null $year        An optional year the month-of-year belongs to, validated.
     *
     * @return int<28, 31>
     */
    public static function getLength(int $monthOfYear, ?int $year = null): int
    {
        return match ($monthOfYear) {
            2 => ($year === null || Year::isLeap($year)) ? 29 : 28,
            4, 6, 9, 11 => 30,
            default => 31,
        };
    }

    /**
     * Returns the camel-cased English name of the given month-of-year.
     *
     * @param int<1, 12> $monthOfYear The month-of-year.
     */
    public static function getName(int $monthOfYear): string
    {
        return match ($monthOfYear) {
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        };
    }
}
