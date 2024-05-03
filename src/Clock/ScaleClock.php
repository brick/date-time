<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * This clock makes the time move at a given pace.
 */
final class ScaleClock implements Clock
{
    /**
     * The start time.
     */
    private readonly Instant $startTime;

    /**
     * - a scale > 1 makes the time move at an accelerated pace;
     * - a scale == 1 makes the time move at the normal pace;
     * - a scale == 0 freezes the current time;
     * - a scale < 0 makes the time move backwards.
     *
     * @param Clock $referenceClock The reference clock.
     * @param int   $timeScale      The time scale.
     */
    public function __construct(
        private readonly Clock $referenceClock,
        private readonly int $timeScale,
    ) {
        $this->startTime = $this->referenceClock->getTime();
    }

    public function getTime(): Instant
    {
        $duration = Duration::between($this->startTime, $this->referenceClock->getTime());
        $duration = $duration->multipliedBy($this->timeScale);

        return $this->startTime->plus($duration);
    }
}
