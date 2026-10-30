<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * Measures the time elapsed.
 */
final class Stopwatch
{
    private readonly Clock $clock;

    /**
     * The total time the stopwatch has been running, excluding the time elapsed since it was started.
     *
     * Every time the stopwatch is stopped, the elapsed time is added to this value.
     */
    private Duration $duration;

    /**
     * The time the stopwatch has been started at, or null if it is not running.
     */
    private ?Instant $startTime = null;

    /**
     * @param Clock|null $clock An optional clock to use.
     *
     * @phpstan-impure
     */
    public function __construct(?Clock $clock = null)
    {
        if ($clock === null) {
            $clock = DefaultClock::get();
        }

        $this->clock = $clock;
        $this->duration = Duration::zero();
    }

    /**
     * Starts the timer.
     *
     * If the timer is already started, this method does nothing.
     *
     * @phpstan-impure
     */
    public function start(): void
    {
        if ($this->startTime === null) {
            $this->startTime = $this->clock->getTime();
        }
    }

    /**
     * Stops the timer and returns the lap duration.
     *
     * If the timer is already stopped, this method does nothing, and returns a zero duration.
     *
     * @phpstan-impure
     */
    public function stop(): Duration
    {
        if ($this->startTime === null) {
            return Duration::zero();
        }

        $endTime = $this->clock->getTime();
        $duration = Duration::between($this->startTime, $endTime);

        $this->duration = $this->duration->plus($duration);
        $this->startTime = null;

        return $duration;
    }

    /**
     * Returns the time this stopwatch has been started at, or null if it is not running.
     *
     * @phpstan-pure
     */
    public function getStartTime(): ?Instant
    {
        return $this->startTime;
    }

    /**
     * @phpstan-pure
     */
    public function isRunning(): bool
    {
        return $this->startTime !== null;
    }

    /**
     * Returns the total elapsed time.
     *
     * This includes the times between previous start() and stop() calls if any,
     * as well as the time since the stopwatch was last started if it is running.
     *
     * @phpstan-impure
     */
    public function getElapsedTime(): Duration
    {
        if ($this->startTime === null) {
            return $this->duration;
        }

        return $this->duration->plus(Duration::between($this->startTime, $this->clock->getTime()));
    }
}
