<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * This clock always returns the same instant. It is typically used for testing.
 */
class FixedClock implements Clock
{
    /**
     * @var Instant
     */
    private $instant;

    /**
     * Class constructor.
     *
     * @param Instant $instant The time to set the clock at.
     */
    public function __construct(Instant $instant)
    {
        $this->instant = $instant;
    }

    /**
     * {@inheritdoc}
     */
    public function getTime() : Instant
    {
        return $this->instant;
    }

    /**
     * @param Instant $instant
     *
     * @return void
     */
    public function setTime(Instant $instant) : void
    {
        $this->instant = $instant;
    }

    /**
     * Moves the clock by a number of seconds and/or nanos.
     *
     * @param int $seconds
     * @param int $nanos
     *
     * @return void
     */
    public function move(int $seconds, int $nanos = 0) : void
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->instant = $this->instant->plus($duration);
    }
}
