<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Override;

/**
 * This clock adds an offset to an underlying clock.
 */
final readonly class OffsetClock implements Clock
{
    /**
     * @param Clock    $referenceClock The reference clock.
     * @param Duration $offset         The offset to apply to the clock.
     */
    public function __construct(
        private Clock $referenceClock,
        private Duration $offset,
    ) {
    }

    #[Override]
    public function getTime(): Instant
    {
        return $this->referenceClock->getTime()->plus($this->offset);
    }
}
