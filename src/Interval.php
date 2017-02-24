<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * Represents a period of time between two instants, inclusive of the start instant and exclusive of the end.
 * The end instant is always greater than or equal to the start instant.
 *
 * This class is immutable.
 */
class Interval
{
    /**
     * The start instant, inclusive.
     *
     * @var \Brick\DateTime\Instant
     */
    private $start;

    /**
     * The end instant, exclusive.
     *
     * @var \Brick\DateTime\Instant
     */
    private $end;

    /**
     * Class constructor.
     *
     * @param \Brick\DateTime\Instant $startInclusive The start instant, inclusive.
     * @param \Brick\DateTime\Instant $endExclusive   The end instant, exclusive.
     *
     * @throws \Brick\DateTime\DateTimeException
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
     *
     * @return \Brick\DateTime\Instant
     */
    public function getStart() : Instant
    {
        return $this->start;
    }

    /**
     * Returns the end instant, exclusive, of this Interval.
     *
     * @return \Brick\DateTime\Instant
     */
    public function getEnd() : Instant
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start instant altered.
     *
     * @param \Brick\DateTime\Instant $start
     *
     * @return \Brick\DateTime\Interval
     */
    public function withStart(Instant $start) : Interval
    {
        return new Interval($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end instant altered.
     *
     * @param \Brick\DateTime\Instant $end
     *
     * @return \Brick\DateTime\Interval
     */
    public function withEnd(Instant $end) : Interval
    {
        return new Interval($this->start, $end);
    }

    /**
     * Returns a Duration representing the time elapsed in this Interval.
     *
     * @return \Brick\DateTime\Duration
     */
    public function getDuration() : Duration
    {
        return Duration::between($this->start, $this->end);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->start . '/' . $this->end;
    }
}
