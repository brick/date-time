<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * A date-based amount of time in the ISO-8601 calendar system, such as '2 years, 3 months and 4 days'.
 *
 * This class is immutable.
 */
class Period
{
    /**
     * @var int
     */
    private $years;

    /**
     * @var int
     */
    private $months;

    /**
     * @var int
     */
    private $days;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param int $years  The number of years.
     * @param int $months The number of months.
     * @param int $days   The number of days.
     */
    private function __construct(int $years, int $months, int $days)
    {
        $this->years  = $years;
        $this->months = $months;
        $this->days   = $days;
    }

    /**
     * Creates a Period based on years, months, days, hours, minutes and seconds.
     *
     * @param int $years  The number of years.
     * @param int $months The number of months.
     * @param int $days   The number of days.
     *
     * @return Period
     */
    public static function of(int $years, int $months, int $days) : Period
    {
        return new Period($years, $months, $days);
    }

    /**
     * @param int $years
     *
     * @return Period
     */
    public static function ofYears(int $years) : Period
    {
        return new Period($years, 0, 0);
    }

    /**
     * @param int $months
     *
     * @return Period
     */
    public static function ofMonths(int $months) : Period
    {
        return new Period(0, $months, 0);
    }

    /**
     * @param int $weeks
     *
     * @return Period
     */
    public static function ofWeeks(int $weeks) : Period
    {
        return new Period(0, 0, $weeks * LocalTime::DAYS_PER_WEEK);
    }

    /**
     * @param int $days
     *
     * @return Period
     */
    public static function ofDays(int $days) : Period
    {
        return new Period(0, 0, $days);
    }

    /**
     * Creates a zero Period.
     *
     * @return Period
     */
    public static function zero() : Period
    {
        return new Period(0, 0, 0);
    }

    /**
     * Obtains an instance of `Period` by parsing a text string.
     *
     * This will parse the ISO-8601 period format `PnYnMnWnD`
     * which is the format returned by `__toString()`.
     *
     * All of the values (years, months, weeks, days) are optional,
     * but the period must at least contain one of these values.
     *
     * A week is converted to 7 days.
     *
     * Each of the (years, months, weeks, days) values can optionally be preceded with a '+' or '-' sign.
     * The whole string can also start with an optional '+' or '-' sign, which will further affect all the fields.
     *
     * @param string $text
     *
     * @return \Brick\DateTime\Period
     *
     * @throws \Brick\DateTime\Parser\DateTimeParseException
     */
    public static function parse(string $text) : Period
    {
        $pattern =
            '/^' .
            '([\-\+]?)' .
            'P' .
            '(?:([\-\+]?[0-9]+)Y)?' .
            '(?:([\-\+]?[0-9]+)M)?' .
            '(?:([\-\+]?[0-9]+)W)?' .
            '(?:([\-\+]?[0-9]+)D)?' .
            '()$/i';

        if (\preg_match($pattern, $text, $matches) !== 1) {
            throw Parser\DateTimeParseException::invalidPeriod($text);
        }

        list (, $sign, $years, $months, $weeks, $days) = $matches;

        if ($years === '' && $months === '' && $weeks === '' && $days === '') {
            throw Parser\DateTimeParseException::invalidPeriod($text);
        }

        $years  = (int) $years;
        $months = (int) $months;
        $weeks  = (int) $weeks;
        $days   = (int) $days;

        $days += LocalTime::DAYS_PER_WEEK * $weeks;

        if ($sign === '-') {
            $years  = -$years;
            $months = -$months;
            $days   = -$days;
        }

        return new Period($years, $months, $days);
    }

    /**
     * Returns a Period consisting of the number of years, months, and days between two dates.
     *
     * The start date is included, but the end date is not.
     * The period is calculated by removing complete months, then calculating
     * the remaining number of days, adjusting to ensure that both have the same sign.
     * The number of months is then split into years and months based on a 12 month year.
     * A month is considered if the end day-of-month is greater than or equal to the start day-of-month.
     *
     * For example, from `2010-01-15` to `2011-03-18` is one year, two months and three days.
     *
     * The result of this method can be a negative period if the end is before the start.
     * The negative sign will be the same in each of year, month and day.
     *
     * @param LocalDate $startInclusive
     * @param LocalDate $endExclusive
     *
     * @return Period
     */
    public static function between(LocalDate $startInclusive, LocalDate $endExclusive) : Period
    {
        return $startInclusive->until($endExclusive);
    }

    /**
     * @return int
     */
    public function getYears() : int
    {
        return $this->years;
    }

    /**
     * @return int
     */
    public function getMonths() : int
    {
        return $this->months;
    }

    /**
     * @return int
     */
    public function getDays() : int
    {
        return $this->days;
    }

    /**
     * @param int $years
     *
     * @return Period
     */
    public function withYears(int $years) : Period
    {
        if ($years === $this->years) {
            return $this;
        }

        return new Period($years, $this->months, $this->days);
    }

    /**
     * @param int $months
     *
     * @return Period
     */
    public function withMonths(int $months) : Period
    {
        if ($months === $this->months) {
            return $this;
        }

        return new Period($this->years, $months, $this->days);
    }

    /**
     * @param int $days
     *
     * @return Period
     */
    public function withDays(int $days) : Period
    {
        if ($days === $this->days) {
            return $this;
        }

        return new Period($this->years, $this->months, $days);
    }

    /**
     * @param int $years
     *
     * @return Period
     */
    public function plusYears(int $years) : Period
    {
        if ($years === 0) {
            return $this;
        }

        return new Period($this->years + $years, $this->months, $this->days);
    }

    /**
     * @param int $months
     *
     * @return Period
     */
    public function plusMonths(int $months) : Period
    {
        if ($months === 0) {
            return $this;
        }

        return new Period($this->years, $this->months + $months, $this->days);
    }

    /**
     * @param int $days
     *
     * @return Period
     */
    public function plusDays(int $days) : Period
    {
        if ($days === 0) {
            return $this;
        }

        return new Period($this->years, $this->months, $this->days + $days);
    }

    /**
     * @param int $years
     *
     * @return Period
     */
    public function minusYears(int $years) : Period
    {
        if ($years === 0) {
            return $this;
        }

        return new Period($this->years - $years, $this->months, $this->days);
    }

    /**
     * @param int $months
     *
     * @return Period
     */
    public function minusMonths(int $months) : Period
    {
        if ($months === 0) {
            return $this;
        }

        return new Period($this->years, $this->months - $months, $this->days);
    }

    /**
     * @param int $days
     *
     * @return Period
     */
    public function minusDays(int $days) : Period
    {
        if ($days === 0) {
            return $this;
        }

        return new Period($this->years, $this->months, $this->days - $days);
    }

    /**
     * Returns a new Period with each value multiplied by the given scalar.
     *
     * @param int $scalar
     *
     * @return Period
     */
    public function multipliedBy(int $scalar) : Period
    {
        if ($scalar === 1) {
            return $this;
        }

        return new Period(
            $this->years * $scalar,
            $this->months * $scalar,
            $this->days * $scalar
        );
    }

    /**
     * Returns a new instance with each amount in this Period negated.
     *
     * @return Period
     */
    public function negated() : Period
    {
        if ($this->isZero()) {
            return $this;
        }

        return new Period(
            - $this->years,
            - $this->months,
            - $this->days
        );
    }

    /**
     * Returns a copy of this Period with the years and months normalized.
     *
     * This normalizes the years and months units, leaving the days unit unchanged.
     * The months unit is adjusted to have an absolute value less than 12,
     * with the years unit being adjusted to compensate. For example, a period of
     * "1 year and 15 months" will be normalized to "2 years and 3 months".
     *
     * The sign of the years and months units will be the same after normalization.
     * For example, a period of "1 year and -25 months" will be normalized to
     * "-1 year and -1 month".
     *
     * @return Period
     */
    public function normalized() : Period
    {
        $totalMonths = $this->years * LocalTime::MONTHS_PER_YEAR + $this->months;

        $splitYears = \intdiv($totalMonths, 12);
        $splitMonths = $totalMonths % 12;

        if ($splitYears === $this->years || $splitMonths === $this->months) {
            return $this;
        }

        return new Period($splitYears, $splitMonths, $this->days);
    }

    /**
     * @return bool
     */
    public function isZero() : bool
    {
        return $this->years === 0 && $this->months === 0 && $this->days === 0;
    }

    /**
     * @param Period $that
     *
     * @return bool
     */
    public function isEqualTo(Period $that) : bool
    {
        return $this->years === $that->years
            && $this->months === $that->months
            && $this->days === $that->days;
    }

    /**
     * Returns a native DateInterval object equivalent to this Period.
     *
     * We cannot use the constructor with the output of __toString(),
     * as it does not support negative values.
     *
     * @return \DateInterval
     */
    public function toDateInterval() : \DateInterval
    {
        return \DateInterval::createFromDateString(\sprintf(
            '%d years %d months %d days',
            $this->years,
            $this->months,
            $this->days
        ));
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        if ($this->isZero()) {
            return 'P0D';
        }

        $string = 'P';

        if ($this->years !== 0) {
            $string .= $this->years . 'Y';
        }
        if ($this->months !== 0) {
            $string .= $this->months . 'M';
        }
        if ($this->days !== 0) {
            $string .= $this->days . 'D';
        }

        return $string;
    }
}
