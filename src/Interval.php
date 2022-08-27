<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;

/**
 * Represents a period of time between two instants, inclusive of the start instant and exclusive of the end.
 * The end instant is always greater than or equal to the start instant.
 *
 * This class is immutable.
 */
final class Interval implements JsonSerializable
{
    /**
     * The start instant, inclusive.
     */
    private Instant $start;

    /**
     * The end instant, exclusive.
     */
    private Instant $end;

    /**
     * @param Instant $startInclusive The start instant, inclusive.
     * @param Instant $endExclusive   The end instant, exclusive.
     *
     * @throws DateTimeException If the end instant is before the start instant.
     */
    public function __construct(Instant $startInclusive, Instant $endExclusive)
    {
        if ($endExclusive->isBefore($startInclusive)) {
            throw new DateTimeException('The end instant must not be before the start instant.');
        }

        $this->start = $startInclusive;
        $this->end = $endExclusive;
    }

    /**
     * Returns the start instant, inclusive, of this Interval.
     */
    public function getStart(): Instant
    {
        return $this->start;
    }

    /**
     * Returns the end instant, exclusive, of this Interval.
     */
    public function getEnd(): Instant
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start instant altered.
     *
     * @throws DateTimeException If the given start instant is after the current end instant.
     */
    public function withStart(Instant $start): Interval
    {
        return new Interval($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end instant altered.
     *
     * @throws DateTimeException If the given end instant is before the current start instant.
     */
    public function withEnd(Instant $end): Interval
    {
        return new Interval($this->start, $end);
    }

    /**
     * Returns a Duration representing the time elapsed in this Interval.
     */
    public function getDuration(): Duration
    {
        return Duration::between($this->start, $this->end);
    }

    /**
     * Serializes as a string using {@see Interval::__toString()}.
     */
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    public function __toString(): string
    {
        return $this->start . '/' . $this->end;
    }
}
