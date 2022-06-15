<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock;

use Brick\DateTime\Clock;
use Brick\DateTime\Instant;

/**
 * This clock returns the system time. It is the default clock.
 *
 * This clock has a microsecond precision on most systems.
 */
final class SystemClock implements Clock
{
    public function getTime(): Instant
    {
        [$fraction, $epochSecond] = \explode(' ', microtime());

        $epochSecond = (int) $epochSecond;
        $nanoAdjustment = 10 * (int) \substr($fraction, 2, 8);

        return Instant::of($epochSecond, $nanoAdjustment);
    }
}
