<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Clock\SystemClock;

/**
 * This is where the default clock is set for all methods such as `now()`.
 *
 * The default clock is the system clock by default, but can be changed for testing.
 */
final class DefaultClock
{
    /**
     * @var Clock|null
     */
    private static $clock;

    /**
     * Private constructor. This class is not instantiable.
     */
    private function __construct()
    {
    }

    /**
     * Returns the default clock.
     *
     * @return Clock
     */
    public static function get() : Clock
    {
        if (self::$clock === null) {
            self::$clock = new SystemClock();
        }

        return self::$clock;
    }

    /**
     * Sets the default clock.
     *
     * @param Clock $clock
     *
     * @return void
     */
    public static function set(Clock $clock) : void
    {
        self::$clock = $clock;
    }

    /**
     * Resets the default clock to the system clock.
     *
     * @return void
     */
    public static function reset() : void
    {
        self::$clock = null;
    }
}
