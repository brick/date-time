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
 * Represents the combination of a year and a week.
 */
final class YearWeek implements JsonSerializable, Stringable
{
    /**
     * @param int $year The year, validated from MIN_YEAR to MAX_YEAR.
     * @param int $week The week number, validated in the range 1 to 53, and valid for the year.
     */
    private function __construct(
        private readonly int $year,
        private readonly int $week,
    ) {
    }

    /**
     * Obtains an instance of `YearWeek` from a year and week number.
     *
     * @param int $year The year, from MIN_YEAR to MAX_YEAR.
     * @param int $week The week number, from 1 to 53.
     *
     * @throws DateTimeException
     */
    public static function of(int $year, int $week): YearWeek
    {
        Field\Year::check($year);
        Field\WeekOfYear::check($week, $year);

        return new YearWeek($year, $week);
    }

    /**
     * @throws DateTimeException      If the year-week is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): YearWeek
    {
        return YearWeek::of(
            (int) $result->getField(Field\Year::NAME),
            (int) $result->getField(Field\WeekOfYear::NAME),
        );
    }

    /**
     * Obtains an instance of `YearWeek` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-W48`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the year-week is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): YearWeek
    {
        if ($parser === null) {
            $parser = IsoParsers::yearWeek();
        }

        return YearWeek::from($parser->parse($text));
    }

    public static function now(TimeZone $timeZone, ?Clock $clock = null): YearWeek
    {
        return LocalDate::now($timeZone, $clock)->getYearWeek();
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getWeek(): int
    {
        return $this->week;
    }

    /**
     * @return int [-1,0,1] If this year-week is before, on, or after the given year-week.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(YearWeek $that): int
    {
        if ($this->year < $that->year) {
            return -1;
        }
        if ($this->year > $that->year) {
            return 1;
        }
        if ($this->week < $that->week) {
            return -1;
        }
        if ($this->week > $that->week) {
            return 1;
        }

        return 0;
    }

    public function isEqualTo(YearWeek $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isBefore(YearWeek $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(YearWeek $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isAfter(YearWeek $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(YearWeek $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns a copy of this YearWeek with the year altered.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     *
     * @throws DateTimeException If the year is not valid.
     */
    public function withYear(int $year): YearWeek
    {
        if ($year === $this->year) {
            return $this;
        }

        Field\Year::check($year);

        $week = $this->week;

        if ($week === 53 && ! Field\WeekOfYear::is53WeekYear($year)) {
            $week = 52;
        }

        return new YearWeek($year, $week);
    }

    /**
     * Returns a copy of this YearWeek with the week altered.
     *
     * If the new week is 53 and the year does not have 53 weeks, week one of the following year is selected.
     *
     * @throws DateTimeException If the week is not valid.
     */
    public function withWeek(int $week): YearWeek
    {
        if ($week === $this->week) {
            return $this;
        }

        Field\WeekOfYear::check($week);

        $year = $this->year;

        if ($week === 53 && ! Field\WeekOfYear::is53WeekYear($year)) {
            $year++;
            $week = 1;
        }

        return new YearWeek($year, $week);
    }

    /**
     * Combines this year-week with a day-of-week to create a LocalDate.
     */
    public function atDay(DayOfWeek|int $dayOfWeek): LocalDate
    {
        if (is_int($dayOfWeek)) {
            Field\DayOfWeek::check($dayOfWeek);
        } else {
            $dayOfWeek = $dayOfWeek->value;
        }

        $correction = LocalDate::of($this->year, Month::JANUARY, 4)->getDayOfWeek()->value + 3;
        $dayOfYear = $this->week * 7 + $dayOfWeek - $correction;
        $maxDaysOfYear = Field\Year::isLeap($this->year) ? 366 : 365;

        if ($dayOfYear > $maxDaysOfYear) {
            return LocalDate::ofYearDay($this->year + 1, $dayOfYear - $maxDaysOfYear);
        }

        if ($dayOfYear > 0) {
            return LocalDate::ofYearDay($this->year, $dayOfYear);
        }

        $daysOfPreviousYear = Field\Year::isLeap($this->year - 1) ? 366 : 365;

        return LocalDate::ofYearDay($this->year - 1, $daysOfPreviousYear + $dayOfYear);
    }

    /**
     * Returns the first day of this week.
     */
    public function getFirstDay(): LocalDate
    {
        return $this->atDay(DayOfWeek::MONDAY);
    }

    /**
     * Returns the last day of this week.
     */
    public function getLastDay(): LocalDate
    {
        return $this->atDay(DayOfWeek::SUNDAY);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in years added.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     */
    public function plusYears(int $years): YearWeek
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): YearWeek
    {
        if ($weeks === 0) {
            return $this;
        }

        $mondayOfWeek = $this->atDay(DayOfWeek::MONDAY)->plusWeeks($weeks);

        return $mondayOfWeek->getYearWeek();
    }

    /**
     * Returns a copy of this YearWeek with the specified period in years subtracted.
     *
     * If the week is 53 and the new year does not have 53 weeks, the week will be adjusted to be 52.
     */
    public function minusYears(int $years): YearWeek
    {
        return $this->plusYears(-$years);
    }

    /**
     * Returns a copy of this YearWeek with the specified period in weeks subtracted.
     */
    public function minusWeeks(int $weeks): YearWeek
    {
        return $this->plusWeeks(-$weeks);
    }

    /**
     * Returns whether this year has 53 weeks.
     */
    public function is53WeekYear(): bool
    {
        return Field\WeekOfYear::is53WeekYear($this->year);
    }

    /**
     * Returns LocalDateRange that contains all days of this year week.
     */
    public function toLocalDateRange(): LocalDateRange
    {
        return LocalDateRange::of($this->getFirstDay(), $this->getLastDay());
    }

    /**
     * Serializes as a string using {@see YearWeek::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this year-week.
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
            . '-W'
            . ($this->week < 10 ? '0' . $this->week : $this->week);
    }

    /**
     * {@see YearWeek::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
