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
    abstract public function getInstant();

    /**
     * @return integer
     */
    public function getEpochSecond()
    {
        return $this->getInstant()->getEpochSecond();
    }

    /**
     * @return integer
     */
    public function getNano()
    {
        return $this->getInstant()->getNano();
    }

    /**
     * Compares this instant with another.
     *
     * @param ReadableInstant $that
     *
     * @return integer [-1,0,1] If this instant is before, on, or after the given instant.
     */
    public function compareTo(ReadableInstant $that)
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
     * @return boolean
     */
    public function isEqualTo(ReadableInstant $that)
    {
        return $this->compareTo($that) === 0;
    }

    /**
     * Returns whether this instant is after another.
     *
     * @param ReadableInstant $that
     *
     * @return boolean
     */
    public function isAfter(ReadableInstant $that)
    {
        return $this->compareTo($that) === 1;
    }

    /**
     * Returns whether this instant is before another.
     *
     * @param ReadableInstant $that
     *
     * @return boolean
     */
    public function isBefore(ReadableInstant $that)
    {
        return $this->compareTo($that) === -1;
    }

    /**
     * Returns whether this instant is in the future.
     *
     * @return boolean
     */
    public function isFuture()
    {
        return $this->isAfter(Instant::now());
    }

    /**
     * Returns whether this instant is in the past.
     *
     * @return boolean
     */
    public function isPast()
    {
        return $this->isBefore(Instant::now());
    }
}
