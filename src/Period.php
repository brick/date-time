<?php

namespace Brick\DateTime;

use Brick\DateTime\Utility\Cast;
use Brick\DateTime\Utility\Math;

/**
 * A date-based amount of time in the ISO-8601 calendar system, such as '2 years, 3 months and 4 days'.
 *
 * This class is immutable.
 */
class Period
{
    /**
     * @var integer
     */
    private $years;

    /**
     * @var integer
     */
    private $months;

    /**
     * @var integer
     */
    private $days;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param integer $years   The number of years, validated as an integer.
     * @param integer $months  The number of months, validated as an integer.
     * @param integer $days    The number of days, validated as an integer.
     */
    private function __construct($years, $months, $days)
    {
        $this->years   = $years;
        $this->months  = $months;
        $this->days    = $days;
    }

    /**
     * Creates a Period based on years, months, days, hours, minutes and seconds.
     *
     * @param integer $years   The number of years.
     * @param integer $months  The number of months.
     * @param integer $days    The number of days.
     *
     * @return Period
     */
    public static function of($years, $months, $days)
    {
        return new Period(
            Cast::toInteger($years),
            Cast::toInteger($months),
            Cast::toInteger($days)
        );
    }

    /**
     * @param integer $years
     *
     * @return Period
     */
    public static function ofYears($years)
    {
        $years = Cast::toInteger($years);

        return new Period($years, 0, 0);
    }

    /**
     * @param integer $months
     *
     * @return Period
     */
    public static function ofMonths($months)
    {
        $months = Cast::toInteger($months);

        return new Period(0, $months, 0);
    }

    /**
     * @param integer $weeks
     *
     * @return Period
     */
    public static function ofWeeks($weeks)
    {
        $weeks = Cast::toInteger($weeks);

        return new Period(0, 0, $weeks * LocalTime::DAYS_PER_WEEK);
    }

    /**
     * @param integer $days
     *
     * @return Period
     */
    public static function ofDays($days)
    {
        $days = Cast::toInteger($days);

        return new Period(0, 0, $days);
    }

    /**
     * Creates a zero Period.
     *
     * @return Period
     */
    public static function zero()
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
    public static function parse($text)
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

        if (preg_match($pattern, $text, $matches) !== 1) {
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
     * Returns a Period consisting of the number of years, months and days between two LocalDateTime instances.
     *
     * @param LocalDateTime $startInclusive
     * @param LocalDateTime $endExclusive
     *
     * @return Period
     */
    public static function between(LocalDateTime $startInclusive, LocalDateTime $endExclusive)
    {
        // @todo
    }

    /**
     * @return integer
     */
    public function getYears()
    {
        return $this->years;
    }

    /**
     * @return integer
     */
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * @return integer
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param integer $years
     *
     * @return Period
     */
    public function withYears($years)
    {
        $years = Cast::toInteger($years);

        if ($years === $this->years) {
            return $this;
        }

        return new Period($years, $this->months, $this->days);
    }

    /**
     * @param integer $months
     *
     * @return Period
     */
    public function withMonths($months)
    {
        $months = Cast::toInteger($months);

        if ($months === $this->months) {
            return $this;
        }

        return new Period($this->years, $months, $this->days);
    }

    /**
     * @param integer $days
     *
     * @return Period
     */
    public function withDays($days)
    {
        $days = Cast::toInteger($days);

        if ($days === $this->days) {
            return $this;
        }

        return new Period($this->years, $this->months, $days);
    }

    /**
     * @param integer $years
     *
     * @return Period
     */
    public function plusYears($years)
    {
        $years = Cast::toInteger($years);

        if ($years === 0) {
            return $this;
        }

        return new Period($this->years + $years, $this->months, $this->days);
    }

    /**
     * @param integer $months
     *
     * @return Period
     */
    public function plusMonths($months)
    {
        $months = Cast::toInteger($months);

        if ($months === 0) {
            return $this;
        }

        return new Period($this->years, $this->months + $months, $this->days);
    }

    /**
     * @param integer $days
     *
     * @return Period
     */
    public function plusDays($days)
    {
        $days = Cast::toInteger($days);

        if ($days === 0) {
            return $this;
        }

        return new Period($this->years, $this->months, $this->days + $days);
    }

    /**
     * @param integer $years
     *
     * @return Period
     */
    public function minusYears($years)
    {
        $years = Cast::toInteger($years);

        if ($years === 0) {
            return $this;
        }

        return new Period($this->years - $years, $this->months, $this->days);
    }

    /**
     * @param integer $months
     *
     * @return Period
     */
    public function minusMonths($months)
    {
        $months = Cast::toInteger($months);

        if ($months === 0) {
            return $this;
        }

        return new Period($this->years, $this->months - $months, $this->days);
    }

    /**
     * @param integer $days
     *
     * @return Period
     */
    public function minusDays($days)
    {
        $days = Cast::toInteger($days);

        if ($days === 0) {
            return $this;
        }

        return new Period($this->years, $this->months, $this->days - $days);
    }

    /**
     * Returns a new Period with each value multiplied by the given scalar.
     *
     * @param integer $scalar
     *
     * @return Period
     */
    public function multipliedBy($scalar)
    {
        $scalar = Cast::toInteger($scalar);

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
    public function negated()
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
    public function normalized()
    {
        $totalMonths = $this->years * LocalTime::MONTHS_PER_YEAR + $this->months;

        $splitYears = Math::div($totalMonths, 12);
        $splitMonths = $totalMonths % 12;

        if ($splitYears === $this->years || $splitMonths === $this->months) {
            return $this;
        }

        return new Period($splitYears, $splitMonths, $this->days);
    }

    /**
     * @return boolean
     */
    public function isZero()
    {
        return $this->years === 0 && $this->months === 0 && $this->days === 0;
    }

    /**
     * @param Period $that
     *
     * @return boolean
     */
    public function isEqualTo(Period $that)
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
    public function toDateInterval()
    {
        return \DateInterval::createFromDateString(sprintf(
            '%d years %d months %d days',
            $this->years,
            $this->months,
            $this->days
        ));
    }

    /**
     * @return string
     */
    public function __toString()
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
