<?php

namespace Brick\DateTime\Clock;

use Brick\DateTime\Instant;

/**
 * A clock provides the current time.
 */
abstract class Clock
{
    /**
     * The global default Clock.
     *
     * @var Clock|null
     */
    private static $default = null;

    /**
     * Sets the default clock.
     *
     * @param Clock $clock The new clock.
     *
     * @return Clock The previous clock.
     */
    public static function setDefault(Clock $clock) : Clock
    {
        $current = self::getDefault();
        self::$default = $clock;

        return $current;
    }

    /**
     * Returns the default clock. Defaults to the system clock unless overridden.
     *
     * @return Clock
     */
    public static function getDefault() : Clock
    {
        if (self::$default === null) {
            self::$default = new SystemClock();
        }

        return self::$default;
    }

    /**
     * Returns the current time.
     *
     * @return \Brick\DateTime\Instant
     */
    abstract public function getTime() : Instant;
}
