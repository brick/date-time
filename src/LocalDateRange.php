<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Countable;
use DateInterval;
use DatePeriod;
use Generator;
use IteratorAggregate;
use JsonSerializable;
use Stringable;

/**
 * Represents an inclusive range of local dates.
 *
 * This object is iterable and countable: the iterator returns all the LocalDate objects contained
 * in the range, while `count()` returns the total number of dates contained in the range.
 *
 * @template-implements IteratorAggregate<LocalDate>
 */
final class LocalDateRange implements IteratorAggregate, Countable, JsonSerializable, Stringable
{
    /**
     * @param LocalDate $start The start date, inclusive.
     * @param LocalDate $end   The end date, inclusive, validated as not before the start date.
     */
    private function __construct(
        private readonly LocalDate $start,
        private readonly LocalDate $end,
    ) {
    }

    /**
     * Creates an instance of LocalDateRange from a start date and an end date.
     *
     * @param LocalDate $start The start date, inclusive.
     * @param LocalDate $end   The end date, inclusive.
     *
     * @throws DateTimeException If the end date is before the start date.
     */
    public static function of(LocalDate $start, LocalDate $end): LocalDateRange
    {
        if ($end->isBefore($start)) {
            throw new DateTimeException('The end date must not be before the start date.');
        }

        return new LocalDateRange($start, $end);
    }

    /**
     * Obtains an instance of `LocalDateRange` from a set of date-time fields.
     *
     * This method is only useful to parsers.
     *
     * @throws DateTimeException      If the date range is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): LocalDateRange
    {
        $startDate = LocalDate::from($result);

        if ($result->hasField(Field\MonthOfYear::NAME)) {
            if ($result->hasField(Field\Year::NAME)) {
                $endDate = LocalDate::from($result);
            } else {
                $endDate = MonthDay::from($result)->atYear($startDate->getYear());
            }
        } else {
            $endDate = $startDate->withDay((int) $result->getField(Field\DayOfMonth::NAME));
        }

        return LocalDateRange::of($startDate, $endDate);
    }

    /**
     * Obtains an instance of `LocalDateRange` from a text string.
     *
     * Partial representations are allowed; for example, the following representations are equivalent:
     *
     * - `2001-02-03/2001-02-04`
     * - `2001-02-03/02-04`
     * - `2001-02-03/04`
     *
     * @param string              $text   The text to parse.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If either of the dates is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): LocalDateRange
    {
        if ($parser === null) {
            $parser = IsoParsers::localDateRange();
        }

        return LocalDateRange::from($parser->parse($text));
    }

    /**
     * Returns the start date, inclusive.
     */
    public function getStart(): LocalDate
    {
        return $this->start;
    }

    /**
     * Returns the end date, inclusive.
     */
    public function getEnd(): LocalDate
    {
        return $this->end;
    }

    /**
     * Returns whether this LocalDateRange is equal to the given one.
     *
     * @param LocalDateRange $that The range to compare to.
     *
     * @return bool True if this range equals the given one, false otherwise.
     */
    public function isEqualTo(LocalDateRange $that): bool
    {
        return $this->start->isEqualTo($that->start)
            && $this->end->isEqualTo($that->end);
    }

    /**
     * Returns whether this LocalDateRange contains the given date.
     *
     * @param LocalDate $date The date to check.
     *
     * @return bool True if this range contains the given date, false otherwise.
     */
    public function contains(LocalDate $date): bool
    {
        return ! ($date->isBefore($this->start) || $date->isAfter($this->end));
    }

    /**
     * Returns whether this LocalDateRange intersects with the given date range.
     */
    public function intersectsWith(LocalDateRange $that): bool
    {
        return $this->contains($that->start)
            || $this->contains($that->end)
            || $that->contains($this->start)
            || $that->contains($this->end);
    }

    /**
     * Returns the intersection of this LocalDateRange with the given date range.
     *
     * @throws DateTimeException If the ranges do not intersect.
     */
    public function getIntersectionWith(LocalDateRange $that): LocalDateRange
    {
        if (! $this->intersectsWith($that)) {
            throw new DateTimeException('Ranges "' . $this . '" and "' . $that . '" do not intersect.');
        }

        $intersectStart = $this->start->isBefore($that->start) ? $that->start : $this->start;
        $intersectEnd = $this->end->isAfter($that->end) ? $that->end : $this->end;

        return new LocalDateRange($intersectStart, $intersectEnd);
    }

    /**
     * @throws DateTimeException If the start date is after the end date.
     */
    public function withStart(LocalDate $start): LocalDateRange
    {
        if ($start->isEqualTo($this->start)) {
            return $this;
        }

        return LocalDateRange::of($start, $this->end);
    }

    /**
     * @throws DateTimeException If the end date is before the start date.
     */
    public function withEnd(LocalDate $end): LocalDateRange
    {
        if ($end->isEqualTo($this->end)) {
            return $this;
        }

        return LocalDateRange::of($this->start, $end);
    }

    /**
     * Returns the Period between the start date and end date.
     *
     * See `Period::between()` for how this is calculated.
     */
    public function toPeriod(): Period
    {
        return Period::between($this->start, $this->end);
    }

    /**
     * Returns an iterator for all the dates contained in this range.
     *
     * @return Generator<LocalDate>
     */
    public function getIterator(): Generator
    {
        for ($current = $this->start; $current->isBeforeOrEqualTo($this->end); $current = $current->plusDays(1)) {
            yield $current;
        }
    }

    /**
     * Returns the number of days in this range.
     *
     * @return int The number of days, >= 1.
     */
    public function count(): int
    {
        return $this->end->toEpochDay() - $this->start->toEpochDay() + 1;
    }

    /**
     * Serializes as a string using {@see LocalDateRange::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Converts this LocalDateRange to a native DatePeriod object.
     *
     * The result is a DatePeriod->start with time 00:00 and a DatePeriod->end
     * with time 23:59:59.999999 in the UTC time-zone.
     */
    public function toNativeDatePeriod(): DatePeriod
    {
        $start = $this->getStart()->atTime(LocalTime::midnight())->toNativeDateTime();
        $end = $this->getEnd()->atTime(LocalTime::max())->toNativeDateTime();
        $interval = new DateInterval('P1D');

        return new DatePeriod($start, $interval, $end);
    }

    /**
     * Returns the ISO 8601 representation of this date range.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        return $this->start . '/' . $this->end;
    }

    /**
     * {@see LocalDateRange::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
