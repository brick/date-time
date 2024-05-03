<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use JsonSerializable;
use Stringable;

use function is_int;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Represents a year in the proleptic calendar.
 */
final class Year implements JsonSerializable, Stringable
{
    public const MIN_VALUE = LocalDate::MIN_YEAR;
    public const MAX_VALUE = LocalDate::MAX_YEAR;

    /**
     * @param int $year The year, validated.
     */
    private function __construct(
        private readonly int $year,
    ) {
    }

    /**
     * @throws DateTimeException If the year is out of range.
     */
    public static function of(int $year): Year
    {
        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * @throws DateTimeException      If the year is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): Year
    {
        $year = (int) $result->getField(Field\Year::NAME);

        return Year::of($year);
    }

    /**
     * Obtains an instance of `Year` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the year is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): Year
    {
        if ($parser === null) {
            $parser = IsoParsers::year();
        }

        return Year::from($parser->parse($text));
    }

    /**
     * Returns the current year in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): Year
    {
        return new Year(LocalDate::now($timeZone, $clock)->getYear());
    }

    public function getValue(): int
    {
        return $this->year;
    }

    /**
     * Checks if the year is a leap year, according to the ISO proleptic calendar system rules.
     *
     * This method applies the current rules for leap years across the whole time-line.
     * In general, a year is a leap year if it is divisible by four without
     * remainder. However, years divisible by 100, are not leap years, with
     * the exception of years divisible by 400 which are.
     *
     * The calculation is proleptic - applying the same rules into the far future and far past.
     * This is historically inaccurate, but is correct for the ISO-8601 standard.
     */
    public function isLeap(): bool
    {
        return Field\Year::isLeap($this->year);
    }

    public function isValidMonthDay(MonthDay $monthDay): bool
    {
        return $monthDay->isValidYear($this->year);
    }

    /**
     * Returns the length of this year in days.
     *
     * @return int The length of this year in days, 365 or 366.
     */
    public function getLength(): int
    {
        return $this->isLeap() ? 366 : 365;
    }

    /**
     * Returns a copy of this year with the specified number of years added.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $years The years to add, may be negative.
     *
     * @return Year A Year based on this year with the period added.
     *
     * @throws DateTimeException If the resulting year exceeds the supported range.
     */
    public function plus(int $years): Year
    {
        if ($years === 0) {
            return $this;
        }

        $year = $this->year + $years;

        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * Returns a copy of this year with the specified number of years subtracted.
     *
     * This instance is immutable and unaffected by this method call.
     *
     * @param int $years The years to subtract, may be negative.
     *
     * @return Year A Year based on this year with the period subtracted.
     *
     * @throws DateTimeException If the resulting year exceeds the supported range.
     */
    public function minus(int $years): Year
    {
        if ($years === 0) {
            return $this;
        }

        $year = $this->year - $years;

        Field\Year::check($year);

        return new Year($year);
    }

    /**
     * Compares this year to another year.
     *
     * @param Year $that The year to compare to.
     *
     * @return int [-1, 0, 1] If this year is before, equal to, or after the given year.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(Year $that): int
    {
        if ($this->year > $that->year) {
            return 1;
        }

        if ($this->year < $that->year) {
            return -1;
        }

        return 0;
    }

    /**
     * Checks if this year is equal to the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return bool True if this year is equal to the given year, false otherwise.
     */
    public function isEqualTo(Year $that): bool
    {
        return $this->year === $that->year;
    }

    /**
     * Checks if this year is after the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return bool True if this year is after the given year, false otherwise.
     */
    public function isAfter(Year $that): bool
    {
        return $this->year > $that->year;
    }

    /**
     * Checks if this year is before the given year.
     *
     * @param Year $that The year to compare to.
     *
     * @return bool True if this year is before the given year, false otherwise.
     */
    public function isBefore(Year $that): bool
    {
        return $this->year < $that->year;
    }

    /**
     * Combines this year with a day-of-year to create a LocalDate.
     *
     * @param int $dayOfYear The day-of-year to use, from 1 to 366.
     *
     * @throws DateTimeException If the day-of-year is invalid for this year.
     */
    public function atDay(int $dayOfYear): LocalDate
    {
        return LocalDate::ofYearDay($this->year, $dayOfYear);
    }

    /**
     * Combines this year with a month to create a YearMonth.
     */
    public function atMonth(int|Month $month): YearMonth
    {
        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        }

        return YearMonth::of($this->year, $month);
    }

    /**
     * Combines this Year with a MonthDay to create a LocalDate.
     *
     * A month-day of February 29th will be adjusted to February 28th
     * in the resulting date if the year is not a leap year.
     *
     * @param MonthDay $monthDay The month-day to use.
     */
    public function atMonthDay(MonthDay $monthDay): LocalDate
    {
        return $monthDay->atYear($this->year);
    }

    /**
     * Returns LocalDateRange that contains all days of this year.
     */
    public function toLocalDateRange(): LocalDateRange
    {
        return LocalDateRange::of(
            $this->atMonth(Month::JANUARY)->getFirstDay(),
            $this->atMonth(Month::DECEMBER)->getLastDay(),
        );
    }

    /**
     * Serializes as a string using {@see Year::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this year.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        // This code is optimized for high performance
        return $this->year < 1000 && $this->year > -1000
            ? (
                $this->year < 0
                ? '-' . str_pad((string) -$this->year, 4, '0', STR_PAD_LEFT)
                : str_pad((string) $this->year, 4, '0', STR_PAD_LEFT)
            )
            : (string) $this->year;
    }

    /**
     * {@see Year::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
