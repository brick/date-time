<?php

declare(strict_types=1);

namespace Brick\DateTime;

interface Clock
{
    /**
     * Returns the current time.
     *
     * @psalm-mutation-free
     */
    public function getTime() : Instant;
}
