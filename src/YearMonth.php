<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;
use JsonSerializable;
use Stringable;

use function is_int;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Represents the combination of a year and a month.
 */
final class YearMonth implements JsonSerializable, Stringable
{
    /**
     * @param int        $year  The year, validated from MIN_YEAR to MAX_YEAR.
     * @param int<1, 12> $month The month.
     */
    private function __construct(
        private readonly int $year,
        private readonly int $month,
    ) {
    }

    /**
     * Obtains an instance of `YearMonth` from a year and month.
     *
     * @param int $year The year, from MIN_YEAR to MAX_YEAR.
     *
     * @throws DateTimeException
     */
    public static function of(int $year, int|Month $month): YearMonth
    {
        Field\Year::check($year);

        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        } else {
            $month = $month->value;
        }

        return new YearMonth($year, $month);
    }

    /**
     * @throws DateTimeException      If the year-month is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): YearMonth
    {
        return YearMonth::of(
            (int) $result->getField(Field\Year::NAME),
            (int) $result->getField(Field\MonthOfYear::NAME),
        );
    }

    /**
     * Obtains an instance of `YearMonth` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-12`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): YearMonth
    {
        if ($parser === null) {
            $parser = IsoParsers::yearMonth();
        }

        return YearMonth::from($parser->parse($text));
    }

    /**
     * Returns the current year-month in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): YearMonth
    {
        $localDate = LocalDate::now($timeZone, $clock);

        return new YearMonth($localDate->getYear(), $localDate->getMonthValue());
    }

    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Returns the month-of-year as a Month enum.
     */
    public function getMonth(): Month
    {
        return Month::from($this->month);
    }

    /**
     * Returns the month-of-year value from 1 to 12.
     *
     * @return int<1, 12>
     */
    public function getMonthValue(): int
    {
        return $this->month;
    }

    /**
     * Returns whether the year is a leap year.
     */
    public function isLeapYear(): bool
    {
        return Year::of($this->year)->isLeap();
    }

    /**
     * Returns the length of the month in days, taking account of the year.
     *
     * @return int<28, 31>
     */
    public function getLengthOfMonth(): int
    {
        return Month::from($this->month)->getLength($this->isLeapYear());
    }

    /**
     * Returns the length of the year in days, either 365 or 366.
     */
    public function getLengthOfYear(): int
    {
        return $this->isLeapYear() ? 366 : 365;
    }

    /**
     * @return int [-1,0,1] If this year-month is before, on, or after the given year-month.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(YearMonth $that): int
    {
        if ($this->year < $that->year) {
            return -1;
        }
        if ($this->year > $that->year) {
            return 1;
        }
        if ($this->month < $that->month) {
            return -1;
        }
        if ($this->month > $that->month) {
            return 1;
        }

        return 0;
    }

    public function isEqualTo(YearMonth $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isBefore(YearMonth $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(YearMonth $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isAfter(YearMonth $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(YearMonth $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns a copy of this YearMonth with the year altered.
     *
     * @throws DateTimeException If the year is not valid.
     */
    public function withYear(int $year): YearMonth
    {
        if ($year === $this->year) {
            return $this;
        }

        Field\Year::check($year);

        return new YearMonth($year, $this->month);
    }

    /**
     * Returns a copy of this YearMonth with the month-of-year altered.
     */
    public function withMonth(int|Month $month): YearMonth
    {
        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        } else {
            $month = $month->value;
        }

        if ($month === $this->month) {
            return $this;
        }

        return new YearMonth($this->year, $month);
    }

    public function getFirstDay(): LocalDate
    {
        return $this->atDay(1);
    }

    public function getLastDay(): LocalDate
    {
        return $this->atDay($this->getLengthOfMonth());
    }

    /**
     * Combines this year-month with a day-of-month to create a LocalDate.
     *
     * @param int $day The day-of-month to use, valid for the year-month.
     *
     * @return LocalDate The date formed from this year-month and the specified day.
     *
     * @throws DateTimeException If the day is not valid for this year-month.
     */
    public function atDay(int $day): LocalDate
    {
        return LocalDate::of($this->year, $this->month, $day);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in years added.
     */
    public function plusYears(int $years): YearMonth
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in months added.
     */
    public function plusMonths(int $months): YearMonth
    {
        if ($months === 0) {
            return $this;
        }

        $month = $this->month + $months - 1;

        $yearDiff = Math::floorDiv($month, 12);

        /** @var int<1, 12> $month */
        $month = Math::floorMod($month, 12) + 1;

        $year = $this->year + $yearDiff;

        return new YearMonth($year, $month);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in years subtracted.
     */
    public function minusYears(int $years): YearMonth
    {
        return $this->plusYears(-$years);
    }

    /**
     * Returns a copy of this YearMonth with the specified period in months subtracted.
     */
    public function minusMonths(int $months): YearMonth
    {
        return $this->plusMonths(-$months);
    }

    /**
     * Returns LocalDateRange that contains all days of this year and month.
     */
    public function toLocalDateRange(): LocalDateRange
    {
        return LocalDateRange::of($this->getFirstDay(), $this->getLastDay());
    }

    /**
     * Serializes as a string using {@see YearMonth::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this year-month.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        // This code is optimized for high performance
        return ($this->year < 1000 && $this->year > -1000
            ? (
                $this->year < 0
                    ? '-' . str_pad((string) -$this->year, 4, '0', STR_PAD_LEFT)
                    : str_pad((string) $this->year, 4, '0', STR_PAD_LEFT)
            )
            : $this->year
        )
            . '-'
            . ($this->month < 10 ? '0' . $this->month : $this->month);
    }

    /**
     * {@see YearMonth::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
