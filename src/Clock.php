<?php

declare(strict_types=1);

namespace Brick\DateTime;

interface Clock
{
    /**
     * Returns the current time.
     */
    public function getTime(): Instant;
}
