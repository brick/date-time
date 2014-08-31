<?php

namespace Brick\DateTime;

use Brick\DateTime\Utility\Cast;

/**
 * Represents a month.
 */
class Month
{
    const JANUARY   = 1;
    const FEBRUARY  = 2;
    const MARCH     = 3;
    const APRIL     = 4;
    const MAY       = 5;
    const JUNE      = 6;
    const JULY      = 7;
    const AUGUST    = 8;
    const SEPTEMBER = 9;
    const OCTOBER   = 10;
    const NOVEMBER  = 11;
    const DECEMBER  = 12;

    /**
     * The month number, from 1 (January) to 12 (December).
     *
     * @var integer
     */
    private $month;

    /**
     * Private constructor. Use of() to get a Month instance.
     *
     * @param integer $month The month value, validated as an integer from 1 to 12.
     */
    private function __construct($month)
    {
        $this->month = $month;
    }

    /**
     * Returns a cached Month instance.
     *
     * @param integer $value The month value, validated as an integer from 1 to 12.
     *
     * @return Month The cached Month instance.
     */
    private function get($value)
    {
        static $values;

        if (! isset($values[$value])) {
            $values[$value] = new Month($value);
        }

        return $values[$value];
    }

    /**
     * Returns an instance of Month for the given month value.
     *
     * @param integer $value The month number, from 1 (January) to 12 (December).
     *
     * @return Month The Month instance.
     *
     * @throws DateTimeException
     */
    public static function of($value)
    {
        $value = Cast::toInteger($value);

        Field\MonthOfYear::check($value);

        return Month::get($value);
    }

    /**
     * Returns the twelve months of the year in an array.
     *
     * @return Month[]
     */
    public static function getAll()
    {
        $months = [];

        for ($month = Month::JANUARY; $month <= Month::DECEMBER; $month++) {
            $months[] = Month::get($month);
        }

        return $months;
    }

    /**
     * Returns the ISO-8601 month number.
     *
     * @return integer The month number, from 1 (January) to 12 (December).
     */
    public function getValue()
    {
        return $this->month;
    }

    /**
     * Checks if this month matches the given month number.
     *
     * @param integer $month The month number to test against.
     *
     * @return boolean True if this month is equal to the given value, false otherwise.
     */
    public function is($month)
    {
        return $this->month == $month;
    }

    /**
     * Returns whether this Month equals another Month.
     *
     * @param Month $that
     *
     * @return boolean
     */
    public function isEqualTo(Month $that)
    {
        return ($this->month === $that->month);
    }

    /**
     * Returns the minimum length of this month in days.
     *
     * @return integer The minimum length of this month in days, from 28 to 31.
     */
    public function getMinLength()
    {
        switch ($this->month) {
            case Month::FEBRUARY:
                return 28;
            case Month::APRIL:
            case Month::JUNE:
            case Month::SEPTEMBER:
            case Month::NOVEMBER:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the maximum length of this month in days.
     *
     * @return integer The maximum length of this month in days, from 29 to 31.
     */
    public function getMaxLength()
    {
        switch ($this->month) {
            case Month::FEBRUARY:
                return 29;
            case Month::APRIL:
            case Month::JUNE:
            case Month::SEPTEMBER:
            case Month::NOVEMBER:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the day-of-year for the first day of this month.
     *
     * This returns the day-of-year that this month begins on, using the leap
     * year flag to determine the length of February.
     *
     * @param boolean $leapYear
     *
     * @return integer
     */
    public function getFirstDayOfYear($leapYear)
    {
        $leap = $leapYear ? 1 : 0;

        switch ($this->month) {
            case Month::JANUARY:
                return 1;
            case Month::FEBRUARY:
                return 32;
            case Month::MARCH:
                return 60 + $leap;
            case Month::APRIL:
                return 91 + $leap;
            case Month::MAY:
                return 121 + $leap;
            case Month::JUNE:
                return 152 + $leap;
            case Month::JULY:
                return 182 + $leap;
            case Month::AUGUST:
                return 213 + $leap;
            case Month::SEPTEMBER:
                return 244 + $leap;
            case Month::OCTOBER:
                return 274 + $leap;
            case Month::NOVEMBER:
                return 305 + $leap;
        }

        return 335 + $leap;
    }

    /**
     * Returns the length of this month in days.
     *
     * This takes a flag to determine whether to return the length for a leap year or not.
     *
     * February has 28 days in a standard year and 29 days in a leap year.
     * April, June, September and November have 30 days.
     * All other months have 31 days.
     *
     * @param boolean $leapYear
     *
     * @return integer
     */
    public function getLength($leapYear)
    {
        switch ($this->month) {
            case Month::FEBRUARY:
                return ($leapYear ? 29 : 28);
            case Month::APRIL:
            case Month::JUNE:
            case Month::SEPTEMBER:
            case Month::NOVEMBER:
                return 30;
            default:
                return 31;
        }
    }

    /**
     * Returns the month that is the specified number of months after this one.
     *
     * The calculation rolls around the end of the year from December to January.
     * The specified period may be negative.
     *
     * @param integer $months
     *
     * @return Month
     */
    public function plus($months)
    {
        $months = Cast::toInteger($months);

        return Month::get((((($this->month - 1 + $months) % 12) + 12) % 12) + 1);
    }

    /**
     * Returns the month that is the specified number of months before this one.
     *
     * The calculation rolls around the start of the year from January to December.
     * The specified period may be negative.
     *
     * @param integer $months
     *
     * @return Month
     */
    public function minus($months)
    {
        $months = Cast::toInteger($months);

        return $this->plus(- $months);
    }

    /**
     * Returns the capitalized English name of this Month.
     *
     * @return string
     */
    public function __toString()
    {
        return [
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
        ][$this->month];
    }
}
