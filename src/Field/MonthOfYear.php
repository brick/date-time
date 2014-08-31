<?php

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
    const NAME = 'month-of-year';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '[0-9]{2}';

    /**
     * @param integer $monthOfYear The month-of-year to check, validated as an integer.
     *
     * @return void
     *
     * @throws DateTimeException If the month-of-year is not valid.
     */
    public static function check($monthOfYear)
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
     * @param integer      $monthOfYear The month-of-year, fully validated.
     * @param integer|null $year        An optional year the month-of-year belongs to, fully validated.
     *
     * @return integer
     */
    public static function getLength($monthOfYear, $year = null)
    {
        switch ($monthOfYear) {
            case 2:
                return ($year === null || Year::isLeap($year)) ? 29 : 28;

            case 4:
            case 6:
            case 9:
            case 11:
                return 30;

            default:
                return 31;
        }
    }

    /**
     * Returns the camel-cased English name of the given month-of-year.
     *
     * @param integer $monthOfYear The month-of-year, fully validated.
     *
     * @return string
     */
    public static function getName($monthOfYear)
    {
        static $names = [
            1  => 'January',
            2  => 'February',
            3  => 'March',
            4  => 'April',
            5  => 'May',
            6  => 'June',
            7  => 'July',
            8  => 'August',
            9  => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        return $names[$monthOfYear];
    }
}
