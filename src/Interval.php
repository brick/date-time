<?php

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
     * @var \Brick\DateTime\ReadableInstant
     */
    private $start;

    /**
     * The end instant, exclusive.
     *
     * @var \Brick\DateTime\ReadableInstant
     */
    private $end;

    /**
     * Class constructor.
     *
     * @param \Brick\DateTime\ReadableInstant $startInclusive The start instant, inclusive.
     * @param \Brick\DateTime\ReadableInstant $endExclusive   The end instant, exclusive.
     *
     * @throws \Brick\DateTime\DateTimeException
     */
    public function __construct(ReadableInstant $startInclusive, ReadableInstant $endExclusive)
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
     * @return \Brick\DateTime\ReadableInstant
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end instant, exclusive, of this Interval.
     *
     * @return \Brick\DateTime\ReadableInstant
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start instant altered.
     *
     * @param \Brick\DateTime\ReadableInstant $start
     *
     * @return \Brick\DateTime\Interval
     */
    public function withStart(ReadableInstant $start)
    {
        return new Interval($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end instant altered.
     *
     * @param \Brick\DateTime\ReadableInstant $end
     *
     * @return \Brick\DateTime\Interval
     */
    public function withEnd(ReadableInstant $end)
    {
        return new Interval($this->start, $end);
    }

    /**
     * Returns a Duration representing the time elapsed in this Interval.
     *
     * @return \Brick\DateTime\Duration
     */
    public function getDuration()
    {
        return Duration::between($this->start, $this->end);
    }
}
