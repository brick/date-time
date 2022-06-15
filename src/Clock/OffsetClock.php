<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * This clock adds an offset to an underlying clock.
 */
final class OffsetClock implements Clock
{
    /**
     * The reference clock.
     */
    private Clock $referenceClock;

    /**
     * The offset to apply to the clock.
     */
    private Duration $offset;

    /**
     * @param Clock    $referenceClock The reference clock.
     * @param Duration $offset         The offset to apply to the clock.
     */
    public function __construct(Clock $referenceClock, Duration $offset)
    {
        $this->referenceClock = $referenceClock;
        $this->offset = $offset;
    }

    public function getTime(): Instant
    {
        return $this->referenceClock->getTime()->plus($this->offset);
    }
}
