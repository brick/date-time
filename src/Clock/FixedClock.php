<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Override;

/**
 * This clock always returns the same instant. It is typically used for testing.
 */
final class FixedClock implements Clock
{
    /**
     * @param Instant $instant The time to set the clock at.
     *
     * @phpstan-pure
     */
    public function __construct(
        private Instant $instant,
    ) {
    }

    /**
     * @phpstan-pure
     */
    #[Override]
    public function getTime(): Instant
    {
        return $this->instant;
    }

    /**
     * @phpstan-impure
     */
    public function setTime(Instant $instant): void
    {
        $this->instant = $instant;
    }

    /**
     * Moves the clock by a number of seconds and/or nanos.
     *
     * @phpstan-impure
     */
    public function move(int $seconds, int $nanos = 0): void
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->instant = $this->instant->plus($duration);
    }
}
