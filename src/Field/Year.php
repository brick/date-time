<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The proleptic year field.
 */
final class Year
{
    /**
     * The field name.
     */
    public const NAME = 'year';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '\-?[0-9]{4,9}';

    /**
     * The minimum allowed value.
     */
    public const MIN_VALUE = -999_999;

    /**
     * The maximum allowed value.
     */
    public const MAX_VALUE = 999_999;

    /**
     * @param int $year The year to check.
     *
     * @throws DateTimeException If the year is not valid.
     */
    public static function check(int $year): void
    {
        if ($year < self::MIN_VALUE || $year > self::MAX_VALUE) {
            throw DateTimeException::fieldNotInRange(self::NAME, $year, self::MIN_VALUE, self::MAX_VALUE);
        }
    }

    /**
     * @param int $year The year, validated.
     */
    public static function isLeap(int $year): bool
    {
        return (($year & 3) === 0) && (($year % 100) !== 0 || ($year % 400) === 0);
    }
}
