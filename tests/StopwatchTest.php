<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\Stopwatch;

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

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(0, 0, $stopwatch->getElapsedTime());
    }

    public function testNew(): Stopwatch
    {
        $stopwatch = new Stopwatch(self::$clock);

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(0, 0, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testNew
     */
    public function testStart(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(1000, 1);

        $stopwatch->start();

        $this->assertInstantIs(1000, 1, $stopwatch->getStartTime());
        $this->assertTrue($stopwatch->isRunning());
        $this->assertDurationIs(0, 0, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testStart
     */
    public function testElapsedTimeWhileRunning(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(2000, 0);

        $this->assertInstantIs(1000, 1, $stopwatch->getStartTime());
        $this->assertTrue($stopwatch->isRunning());
        $this->assertDurationIs(999, 999999999, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    public function testStopWithNullStartTime(): void
    {
        $stopwatch = new Stopwatch();
        $stopwatch->stop();

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(0, 0, $stopwatch->getElapsedTime());
    }

    /**
     * @depends testElapsedTimeWhileRunning
     */
    public function testStop(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(3000, 2);

        $stopwatch->stop();

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(2000, 1, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testStop
     */
    public function testFrozenAfterStop(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(4000, 9);

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(2000, 1, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testFrozenAfterStop
     */
    public function testRestart(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(5000, 9);

        $stopwatch->start();

        $this->assertInstantIs(5000, 9, $stopwatch->getStartTime());
        $this->assertTrue($stopwatch->isRunning());
        $this->assertDurationIs(2000, 1, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testRestart
     */
    public function testElapsedTimeWhileRunningAfterRestart(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(5001, 10);

        $this->assertInstantIs(5000, 9, $stopwatch->getStartTime());
        $this->assertTrue($stopwatch->isRunning());
        $this->assertDurationIs(2001, 2, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testElapsedTimeWhileRunningAfterRestart
     */
    public function testStopAgain(Stopwatch $stopwatch): Stopwatch
    {
        self::setClockTime(5002, 20);

        $stopwatch->stop();

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(2002, 12, $stopwatch->getElapsedTime());

        return $stopwatch;
    }

    /**
     * @depends testStopAgain
     */
    public function testFrozenAfterSecondStop(Stopwatch $stopwatch): void
    {
        self::setClockTime(6000, 999);

        $this->assertNull($stopwatch->getStartTime());
        $this->assertFalse($stopwatch->isRunning());
        $this->assertDurationIs(2002, 12, $stopwatch->getElapsedTime());
    }

    private static function setClockTime(int $second, int $nano): void
    {
        self::$clock->setTime(Instant::of($second, $nano));
    }
}
