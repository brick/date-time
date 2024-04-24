<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\Stopwatch;
use PHPUnit\Framework\Attributes\Depends;

/**
 * Unit tests for class Period.
 */
class StopwatchTest extends AbstractTestCase
{
    private static FixedClock $clock;

    public static function setUpBeforeClass(): void
    {
        self::$clock = new FixedClock(Instant::of(0));
    }

    public function testConstructorWithNullClock(): void
    {
        $stopwatch = new Stopwatch();

        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(0, 0, $stopwatch->getElapsedTime());
    }

    public function testNew(): Stopwatch
    {
        $stopwatch = new Stopwatch(self::$clock);

        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(0, 0, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testNew')]
    public function testStart(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(1000, 1);

        $stopwatch->start();

        self::assertInstantIs(1000, 1, $stopwatch->getStartTime());
        self::assertTrue($stopwatch->isRunning());
        self::assertDurationIs(0, 0, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testStart')]
    public function testElapsedTimeWhileRunning(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(2000, 0);

        self::assertInstantIs(1000, 1, $stopwatch->getStartTime());
        self::assertTrue($stopwatch->isRunning());
        self::assertDurationIs(999, 999999999, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    public function testStopWithNullStartTime(): void
    {
        $stopwatch = new Stopwatch();

        $duration = $stopwatch->stop();

        self::assertDurationIs(0, 0, $duration);
        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(0, 0, $stopwatch->getElapsedTime());
    }

    #[Depends('testElapsedTimeWhileRunning')]
    public function testStop(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(3000, 2);

        $duration = $stopwatch->stop();

        self::assertDurationIs(2000, 1, $duration);
        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(2000, 1, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testStop')]
    public function testFrozenAfterStop(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(4000, 9);

        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(2000, 1, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testFrozenAfterStop')]
    public function testRestart(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(5000, 9);

        $stopwatch->start();

        self::assertInstantIs(5000, 9, $stopwatch->getStartTime());
        self::assertTrue($stopwatch->isRunning());
        self::assertDurationIs(2000, 1, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testRestart')]
    public function testElapsedTimeWhileRunningAfterRestart(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(5001, 10);

        self::assertInstantIs(5000, 9, $stopwatch->getStartTime());
        self::assertTrue($stopwatch->isRunning());
        self::assertDurationIs(2001, 2, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testElapsedTimeWhileRunningAfterRestart')]
    public function testStopAgain(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(5002, 20);

        $duration = $stopwatch->stop();

        self::assertDurationIs(2, 11, $duration);
        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(2002, 12, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    #[Depends('testStopAgain')]
    public function testFrozenAfterSecondStop(Stopwatch $stopwatch): void
    {
        self::setClockTime(6000, 999);

        self::assertNull($stopwatch->getStartTime());
        self::assertFalse($stopwatch->isRunning());
        self::assertDurationIs(2002, 12, $stopwatch->getElapsedTime());
    }

    private static function setClockTime(int $second, int $nano): void
    {
        self::$clock->setTime(Instant::of($second, $nano));
    }
}
