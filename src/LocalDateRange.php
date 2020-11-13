<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;

/**
 * Represents an inclusive range of local dates.
 *
 * This object is iterable and countable: the iterator returns all the LocalDate objects contained
 * in the range, while `count()` returns the total number of dates contained in the range.
 */
final class LocalDateRange implements \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * The start date, inclusive.
     *
     * @var \Brick\DateTime\LocalDate
     */
    private $start;

    /**
     * The end date, inclusive.
     *
     * @var \Brick\DateTime\LocalDate
     */
    private $end;

    /**
     * Class constructor.
     *
     * @param LocalDate $start The start date, inclusive.
     * @param LocalDate $end   The end date, inclusive, validated as not before the start date.
     */
    private function __construct(LocalDate $start, LocalDate $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    /**
     * Creates an instance of LocalDateRange from a start date and an end date.
     *
     * @param LocalDate $start The start date, inclusive.
     * @param LocalDate $end   The end date, inclusive.
     *
     * @return LocalDateRange
     *
     * @throws DateTimeException If the end date is before the start date.
     */
    public static function of(LocalDate $start, LocalDate $end) : LocalDateRange
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
     * @param DateTimeParseResult $result
     *
     * @return LocalDateRange
     *
     * @throws DateTimeException      If the date range is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result) : LocalDateRange
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
     * @return LocalDateRange
     *
     * @throws DateTimeException      If either of the dates is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null) : LocalDateRange
    {
        if (! $parser) {
            $parser = IsoParsers::localDateRange();
        }

        return LocalDateRange::from($parser->parse($text));
    }

    /**
     * Returns the start date, inclusive.
     *
     * @return LocalDate
     */
    public function getStart() : LocalDate
    {
        return $this->start;
    }

    /**
     * Returns the end date, inclusive.
     *
     * @return LocalDate
     */
    public function getEnd() : LocalDate
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
    public function isEqualTo(LocalDateRange $that) : bool
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
    public function contains(LocalDate $date) : bool
    {
        return ! ($date->isBefore($this->start) || $date->isAfter($this->end));
    }

    /**
     * Returns whether this LocalDateRange intersects with the given date range.
     *
     * @param LocalDateRange $that
     *
     * @return bool
     */
    public function intersectsWith(LocalDateRange $that) : bool
    {
        return $this->contains($that->start)
            || $this->contains($that->end)
            || $that->contains($this->start)
            || $that->contains($this->end);
    }

    /**
     * Returns the intersection of this LocalDateRange with the given date range.
     *
     * @param LocalDateRange $that
     *
     * @return LocalDateRange
     *
     * @throws DateTimeException If the ranges do not intersect.
     */
    public function getIntersectionWith(LocalDateRange $that) : LocalDateRange
    {
        if (!$this->intersectsWith($that)) {
            throw new DateTimeException('Ranges "' . $this . '" and "' . $that . '" do not intersect.');
        }

        $intersectStart = $this->start->isBefore($that->start) ? $that->start : $this->start;
        $intersectEnd = $this->end->isAfter($that->end) ? $that->end: $this->end;

        return new LocalDateRange($intersectStart, $intersectEnd);
    }

    /**
     * Returns an iterator for all the dates contained in this range.
     *
     * @return \Generator<LocalDate>
     */
    public function getIterator() : \Generator
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
    public function count() : int
    {
        return $this->end->toEpochDay() - $this->start->toEpochDay() + 1;
    }

    /**
     * Serializes as a string using {@see LocalDateRange::__toString()}.
     *
     * @return string
     */
    public function jsonSerialize() : string
    {
        return (string) $this;
    }

    /**
     * Returns an ISO 8601 string representation of this date range.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->start . '/' . $this->end;
    }
}
