<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Clock\OffsetClock;
use Brick\DateTime\Clock\ScaleClock;
use Brick\DateTime\Clock\SystemClock;

/**
 * This is where the default clock is set for all methods such as `now()`.
 *
 * The default clock is the system clock by default, but can be changed for testing.
 */
final class DefaultClock
{
    private static ?Clock $clock = null;

    /**
     * Private constructor. This class is not instantiable.
     */
    private function __construct()
    {
    }

    /**
     * Gets the default clock.
     */
    public static function get(): Clock
    {
        if (self::$clock === null) {
            self::$clock = new SystemClock();
        }

        return self::$clock;
    }

    /**
     * Sets the default clock.
     */
    public static function set(Clock $clock): void
    {
        self::$clock = $clock;
    }

    /**
     * Resets the default clock to the system clock.
     */
    public static function reset(): void
    {
        self::$clock = null;
    }

    /**
     * Freezes time to a specific point in time.
     *
     * @param Instant $instant The time to freeze to.
     */
    public static function freeze(Instant $instant): void
    {
        self::set(new FixedClock($instant));
    }

    /**
     * Travels to a specific point in time, but allows time to continue moving forward from there.
     *
     * If the current default clock is frozen, you must `reset()` it first, or the time will stay frozen.
     */
    public static function travelTo(Instant $instant): void
    {
        $clock = self::get();
        $offset = Duration::between($clock->getTime(), $instant);

        self::set(new OffsetClock($clock, $offset));
    }

    /**
     * Travels in time by a duration, which may be forward (positive) or backward (negative).
     *
     * If the current default clock is frozen, you must `reset()` it first, or the time will stay frozen.
     */
    public static function travelBy(Duration $duration): void
    {
        self::set(new OffsetClock(self::get(), $duration));
    }

    /**
     * Makes time move at a given pace.
     *
     * - a scale > 1 makes the time move at an accelerated pace;
     * - a scale == 1 makes the time move at the normal pace;
     * - a scale == 0 freezes the current time;
     * - a scale < 0 makes the time move backwards.
     *
     * If the current default clock is frozen, you must `reset()` it first, or the time will stay frozen.
     * Multiple calls to `scale()` will result in a clock with the combined scales.
     */
    public static function scale(int $timeScale): void
    {
        self::set(new ScaleClock(self::get(), $timeScale));
    }
}
