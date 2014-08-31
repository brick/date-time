<?php

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\DateTimeParsers;
use Brick\DateTime\Parser\IsoParsers;

/**
 * Represents an inclusive range of local dates.
 *
 * This object is iterable and countable: the iterator returns all the LocalDate objects contained
 * in the range, while `count()` returns the total number of dates contained in the range.
 */
class LocalDateRange implements \IteratorAggregate, \Countable
{
    /**
     * The start date, inclusive.
     *
     * @var \Brick\DateTime\LocalDate
     */
    private $startDate;

    /**
     * The end date, inclusive.
     *
     * @var \Brick\DateTime\LocalDate
     */
    private $endDate;

    /**
     * Class constructor.
     *
     * @param LocalDate $startDate The start date, inclusive.
     * @param LocalDate $endDate   The end date, inclusive, validated as not before the start date.
     */
    private function __construct(LocalDate $startDate, LocalDate $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
     * Creates an instance of LocalDateRange from a start date and an end date.
     *
     * @param LocalDate $startDate The start date, inclusive.
     * @param LocalDate $endDate   The end date, inclusive.
     *
     * @return LocalDateRange
     *
     * @throws DateTimeException If the end date is before the start date.
     */
    public static function of(LocalDate $startDate, LocalDate $endDate)
    {
        if ($endDate->isBefore($startDate)) {
            throw new DateTimeException('The end date must not be before the start date.');
        }

        return new LocalDateRange($startDate, $endDate);
    }

    /**
     * @param DateTimeParseResult $result
     *
     * @return LocalDateRange
     *
     * @throws DateTimeException      If the date range is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result)
    {
        return LocalDateRange::of(
            LocalDate::from($result),
            LocalDate::from($result)
        );
    }

    /**
     * Obtains an instance of `LocalDateRange` from a text string.
     *
     * @todo support partial ends such as `2008-02-15/03-14`
     *
     * @param string              $text   The text to parse, such as `2014-01-01/2014-12-31`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @return LocalDateRange
     *
     * @throws DateTimeException      If either of the dates is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse($text, DateTimeParser $parser = null)
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
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Returns the end date, inclusive.
     *
     * @return LocalDate
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Returns whether this LocalDateRange is equal to the given one.
     *
     * @param LocalDateRange $that The range to compare to.
     *
     * @return boolean True if this range equals the given one, false otherwise.
     */
    public function isEqualTo(LocalDateRange $that)
    {
        return $this->startDate->isEqualTo($that->startDate)
            && $this->endDate->isEqualTo($that->endDate);
    }

    /**
     * Returns whether this LocalDateRange contains the given date.
     *
     * @param LocalDate $date The date to check.
     *
     * @return boolean True if this range contains the given date, false otherwise.
     */
    public function contains(LocalDate $date)
    {
        return ! ($date->isBefore($this->startDate) || $date->isAfter($this->endDate));
    }

    /**
     * Returns an iterator for all the dates contained in this range.
     *
     * @return LocalDate[]
     */
    public function getIterator()
    {
        $date = $this->startDate;

        while (! $date->isAfter($this->endDate)) {
            yield $date;
            $date = $date->plusDays(1);
        }
    }

    /**
     * Returns the number of days in this range.
     *
     * @return integer The number of days, >= 1.
     */
    public function count()
    {
        return $this->endDate->toEpochDay() - $this->startDate->toEpochDay() + 1;
    }

    /**
     * Returns an ISO 8601 string representation of this date range.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->startDate . '/' . $this->endDate;
    }
}
