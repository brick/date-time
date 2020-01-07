<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Instant;
use Brick\DateTime\Interval;

/**
 * Unit tests for class Interval.
 */
class IntervalTest extends AbstractTestCase
{
    /**
     * @expectedException        \Brick\DateTime\DateTimeException
     * @expectedExceptionMessage The end instant must not be before the start instant.
     */
    public function testEndInstantIsNotBeforeStartInstant()
    {
        $start = Instant::of(2000000000, 987654321);
        $end = Instant::of(2000000009, 123456789);

        $interval = new Interval($end, $start);
    }

    public function testGetStartEnd()
    {
        $start = Instant::of(2000000000, 987654321);
        $end = Instant::of(2000000009, 123456789);

        $interval = new Interval($start, $end);

        $this->assertInstantIs(2000000000, 987654321, $interval->getStart());
        $this->assertInstantIs(2000000009, 123456789, $interval->getEnd());
    }

    /**
     * @depends testGetStartEnd
     */
    public function testWithStart()
    {
        $interval = new Interval(
            Instant::of(2000000000),
            Instant::of(2000000001)
        );

        $newInterval = $interval->withStart(Instant::of(1999999999, 999999999));

        $this->assertNotSame($newInterval, $interval);

        // ensure that the original isn't changed
        $this->assertInstantIs(2000000000, 0, $interval->getStart());
        $this->assertInstantIs(2000000001, 0, $interval->getEnd());

        // test the new instance
        $this->assertInstantIs(1999999999, 999999999, $newInterval->getStart());
        $this->assertInstantIs(2000000001, 0, $newInterval->getEnd());
    }

    /**
     * @depends testGetStartEnd
     */
    public function testWithEnd()
    {
        $interval = new Interval(
            Instant::of(2000000000),
            Instant::of(2000000001)
        );

        $newInterval = $interval->withEnd(Instant::of(2000000002, 222222222));

        $this->assertNotSame($newInterval, $interval);

        // ensure that the original isn't changed
        $this->assertInstantIs(2000000000, 0, $interval->getStart());
        $this->assertInstantIs(2000000001, 0, $interval->getEnd());

        // test the new instance
        $this->assertInstantIs(2000000000, 0, $newInterval->getStart());
        $this->assertInstantIs(2000000002, 222222222, $newInterval->getEnd());
    }

    public function testGetDuration()
    {
        $interval = new Interval(
            Instant::of(1999999999, 555555),
            Instant::of(2000000001, 111)
        );

        $duration = $interval->getDuration();

        $this->assertDurationIs(1, 999444556, $duration);
    }

    public function testJsonSerialize()
    {
        $interval = new Interval(
            Instant::of(1000000000),
            Instant::of(2000000000)
        );

        $this->assertSame(json_encode('2001-09-09T01:46:40Z/2033-05-18T03:33:20Z'), json_encode($interval));
    }

    public function testToString()
    {
        $interval = new Interval(
            Instant::of(1000000000),
            Instant::of(2000000000)
        );

        $this->assertSame('2001-09-09T01:46:40Z/2033-05-18T03:33:20Z', (string) $interval);
    }
}
