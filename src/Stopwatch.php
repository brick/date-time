<?php

namespace Brick\DateTime;

/**
 * Measures the time elapsed.
 */
class Stopwatch
{
    /**
     * The total time the stopwatch has been running, excluding the time elapsed since it was started.
     *
     * Every time the stopwatch is stopped, the elapsed time is added to this value.
     *
     * @var Duration
     */
    private $duration;

    /**
     * The time the stopwatch has been started at, or null if it is not running.
     *
     * @var Instant|null
     */
    private $startTime;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->duration = Duration::zero();
    }

    /**
     * Starts the timer.
     *
     * If the timer is already started, this method does nothing.
     *
     * @return void
     */
    public function start()
    {
        if (! $this->startTime) {
            $this->startTime = Instant::now();
        }
    }

    /**
     * Stops the timer.
     *
     * If the timer is already stopped, this method does nothing.
     *
     * @return void
     */
    public function stop()
    {
        if ($this->startTime === null) {
            return;
        }

        $this->duration = $this->duration->plus(Duration::between($this->startTime, Instant::now()));
        $this->startTime = null;
    }

    /**
     * Returns the time this stopwatch has been started at, or null if it is not running.
     *
     * @return Instant|null
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return bool
     */
    public function isRunning() : bool
    {
        return $this->startTime !== null;
    }

    /**
     * Returns the total elapsed time.
     *
     * This includes the times between previous start() and stop() calls if any,
     * as well as the time since the stopwatch was last started if it is running.
     *
     * @return Duration
     */
    public function getElapsedTime() : Duration
    {
        if ($this->startTime === null) {
            return $this->duration;
        }

        return $this->duration->plus(Duration::between($this->startTime, Instant::now()));
    }
}
