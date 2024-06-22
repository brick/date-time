<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Countable;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use Stringable;

/**
 * Represents an inclusive range of year-months.
 *
 * This object is iterable and countable: the iterator returns all the YearMonth objects contained
 * in the range, while `count()` returns the total number of year-months contained in the range.
 *
 * @template-implements IteratorAggregate<YearMonth>
 */
class YearMonthRange implements IteratorAggregate, Countable, JsonSerializable, Stringable
{
    /**
     * @param YearMonth $start The start year-month, inclusive.
     * @param YearMonth $end   The end year-month, inclusive, validated as not before the start year-month.
     */
    private function __construct(
        private readonly YearMonth $start,
        private readonly YearMonth $end,
    ) {
    }

    /**
     * Creates an instance of YearMonthRange from a start year-month and an end year-month.
     *
     * @param YearMonth $start The start year-month, inclusive.
     * @param YearMonth $end   The end year-month, inclusive.
     *
     * @throws DateTimeException If the end year-month is before the start year-month.
     */
    public static function of(YearMonth $start, YearMonth $end): YearMonthRange
    {
        if ($end->isBefore($start)) {
            throw new DateTimeException('The end year-month must not be before the start year-month.');
        }

        return new YearMonthRange($start, $end);
    }

    /**
     * Obtains an instance of `YearMonthRange` from a set of date-time fields.
     *
     * This method is only useful to parsers.
     *
     * @throws DateTimeException      If the year-month range is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): YearMonthRange
    {
        $start = YearMonth::from($result);

        if ($result->hasField(Field\Year::NAME)) {
            $end = YearMonth::from($result);
        } else {
            $end = $start->withMonth((int) $result->getField(Field\MonthOfYear::NAME));
        }

        return YearMonthRange::of($start, $end);
    }

    /**
     * Obtains an instance of `YearMonthRange` from a text string.
     *
     * Partial representations are allowed; for example, the following representations are equivalent:
     *
     * - `2001-02/2001-07`
     * - `2001-02/07`
     *
     * @param string              $text   The text to parse.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If either of the year-months is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): YearMonthRange
    {
        if ($parser === null) {
            $parser = IsoParsers::yearMonthRange();
        }

        return YearMonthRange::from($parser->parse($text));
    }

    /**
     * Returns the start year-month, inclusive.
     */
    public function getStart(): YearMonth
    {
        return $this->start;
    }

    /**
     * Returns the end year-month, inclusive.
     */
    public function getEnd(): YearMonth
    {
        return $this->end;
    }

    /**
     * Returns whether this YearMonthRange is equal to the given one.
     *
     * @param YearMonthRange $that The range to compare to.
     *
     * @return bool True if this range equals the given one, false otherwise.
     */
    public function isEqualTo(YearMonthRange $that): bool
    {
        return $this->start->isEqualTo($that->start) && $this->end->isEqualTo($that->end);
    }

    /**
     * Returns whether this YearMonthRange contains the given year-month.
     *
     * @param YearMonth $yearMonth The year-month to check.
     *
     * @return bool True if this range contains the given year-month, false otherwise.
     */
    public function contains(YearMonth $yearMonth): bool
    {
        return $yearMonth->isAfterOrEqualTo($this->start) && $yearMonth->isBeforeOrEqualTo($this->end);
    }

    /**
     * Returns an iterator for all the year-months contained in this range.
     *
     * @return Generator<YearMonth>
     */
    public function getIterator(): Generator
    {
        for ($current = $this->start; $current->isBeforeOrEqualTo($this->end); $current = $current->plusMonths(1)) {
            yield $current;
        }
    }

    /**
     * Returns the number of year-months in this range.
     *
     * @return int<1, max> The number of year-months.
     */
    public function count(): int
    {
        /** @var int<1, max> */
        return 12 * ($this->end->getYear() - $this->start->getYear())
            + ($this->end->getMonthValue() - $this->start->getMonthValue())
            + 1;
    }

    /**
     * Returns LocalDateRange that contains all days of this year-months range.
     */
    public function toLocalDateRange(): LocalDateRange
    {
        return LocalDateRange::of(
            $this->getStart()->getFirstDay(),
            $this->getEnd()->getLastDay(),
        );
    }

    /**
     * Serializes as a string using {@see YearMonthRange::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns a string representation of this year-month range.
     *
     * ISO 8601 does not seem to provide a standard notation for year-month ranges, but we're using the same format as
     * date ranges.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        return $this->start . '/' . $this->end;
    }

    /**
     * {@see YearMonthRange::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
