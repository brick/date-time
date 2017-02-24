<?php

namespace Brick\DateTime;

/**
 * Base class for Instant and ZonedDateTime.
 */
abstract class ReadableInstant
{
    /**
     * @return \Brick\DateTime\Instant
     */
    abstract public function getInstant() : Instant;

    /**
     * @return int
     */
    public function getEpochSecond() : int
    {
        return $this->getInstant()->getEpochSecond();
    }

    /**
     * @return int
     */
    public function getNano() : int
    {
        return $this->getInstant()->getNano();
    }

    /**
     * Compares this instant with another.
     *
     * @param ReadableInstant $that
     *
     * @return int [-1,0,1] If this instant is before, on, or after the given instant.
     */
    public function compareTo(ReadableInstant $that) : int
    {
        $seconds = $this->getEpochSecond() - $that->getEpochSecond();

        if ($seconds !== 0) {
            return $seconds > 0 ? 1 : -1;
        }

        $nanos = $this->getNano() - $that->getNano();

        if ($nanos !== 0) {
            return $nanos > 0 ? 1 : -1;
        }

        return 0;
    }

    /**
     * Returns whether this instant equals another.
     *
     * @param ReadableInstant $that
     *
     * @return bool
     */
    public function isEqualTo(ReadableInstant $that) : bool
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this instant is after another.
     *
     * @param ReadableInstant $that
     *
     * @return bool
     */
    public function isAfter(ReadableInstant $that) : bool
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Returns whether this instant is after or equal to another.
     *
     * @param ReadableInstant $that
     *
     * @return bool
     */
    public function isAfterOrEqualTo(ReadableInstant $that) : bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Returns whether this instant is before another.
     *
     * @param ReadableInstant $that
     *
     * @return bool
     */
    public function isBefore(ReadableInstant $that) : bool
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns whether this instant is before or equal to another.
     *
     * @param ReadableInstant $that
     *
     * @return bool
     */
    public function isBeforeOrEqualTo(ReadableInstant $that) : bool
    {
        return $this->compareTo($that) <= 0;
    }

    /**
     * @param ReadableInstant $from
     * @param ReadableInstant $to
     *
     * @return bool
     */
    public function isBetweenInclusive(ReadableInstant $from, ReadableInstant $to) : bool
    {
        return $this->isAfterOrEqualTo($from) && $this->isBeforeOrEqualTo($to);
    }

    /**
     * @param ReadableInstant $from
     * @param ReadableInstant $to
     *
     * @return bool
     */
    public function isBetweenExclusive(ReadableInstant $from, ReadableInstant $to) : bool
    {
        return $this->isAfter($from) && $this->isBefore($to);
    }

    /**
     * Returns whether this instant is in the future.
     *
     * @return bool
     */
    public function isFuture() : bool
    {
        return $this->isAfter(Instant::now());
    }

    /**
     * Returns whether this instant is in the past.
     *
     * @return bool
     */
    public function isPast() : bool
    {
        return $this->isBefore(Instant::now());
    }
}
