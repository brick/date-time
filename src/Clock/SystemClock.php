<?php

namespace Brick\DateTime\Clock;

use Brick\DateTime\Instant;

/**
 * This clock returns the system time. It is the default clock.
 *
 * This clock has a microsecond precision on most systems.
 */
class SystemClock extends Clock
{
    /**
     * {@inheritdoc}
     */
    public function getTime()
    {
        list ($fraction, $epochSecond) = explode(' ', microtime());

        $epochSecond    = (int) $epochSecond;
        $nanoAdjustment = 10 * (int) substr($fraction, 2, 8);

        return Instant::of($epochSecond, $nanoAdjustment);
    }
}
