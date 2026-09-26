<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Psr\Clock\ClockInterface;

interface Clock extends ClockInterface
{
    /**
     * Returns the current time.
     */
    public function getTime(): Instant;
}
