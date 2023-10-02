<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\Interval;

use function json_encode;

/**
 * Unit tests for class Interval.
 */
class IntervalTest extends AbstractTestCase
{
    public function testEndInstantIsNotBeforeStartInstant(): void
    {
        $start = Instant::of(2000000000, 987654321);
        $end = Instant::of(2000000009, 123456789);

        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage('The end instant must not be before the start instant.');

        Interval::of($end, $start);
    }

    public function testGetStartEnd(): void
    {
        $start = Instant::of(2000000000, 987654321);
        $end = Instant::of(2000000009, 123456789);

        $interval = Interval::of($start, $end);

        self::assertInstantIs(2000000000, 987654321, $interval->getStart());
        self::assertInstantIs(2000000009, 123456789, $interval->getEnd());
    }

    /**
     * @depends testGetStartEnd
     */
    public function testWithStart(): void
    {
        $interval = Interval::of(
            Instant::of(2000000000),
            Instant::of(2000000001)
        );

        $newInterval = $interval->withStart(Instant::of(1999999999, 999999999));

        self::assertNotSame($newInterval, $interval);

        // ensure that the original isn't changed
        self::assertInstantIs(2000000000, 0, $interval->getStart());
        self::assertInstantIs(2000000001, 0, $interval->getEnd());

        // test the new instance
        self::assertInstantIs(1999999999, 999999999, $newInterval->getStart());
        self::assertInstantIs(2000000001, 0, $newInterval->getEnd());
    }

    /**
     * @depends testGetStartEnd
     */
    public function testWithEnd(): void
    {
        $interval = Interval::of(
            Instant::of(2000000000),
            Instant::of(2000000001)
        );

        $newInterval = $interval->withEnd(Instant::of(2000000002, 222222222));

        self::assertNotSame($newInterval, $interval);

        // ensure that the original isn't changed
        self::assertInstantIs(2000000000, 0, $interval->getStart());
        self::assertInstantIs(2000000001, 0, $interval->getEnd());

        // test the new instance
        self::assertInstantIs(2000000000, 0, $newInterval->getStart());
        self::assertInstantIs(2000000002, 222222222, $newInterval->getEnd());
    }

    public function testGetDuration(): void
    {
        $interval = Interval::of(
            Instant::of(1999999999, 555555),
            Instant::of(2000000001, 111)
        );

        $duration = $interval->getDuration();

        self::assertDurationIs(1, 999444556, $duration);
    }

    /** @dataProvider providerContains */
    public function testContains(int $start, int $end, int $now, bool $expected, string $errorMessage): void
    {
        $interval = Interval::of(Instant::of($start), Instant::of($end));

        self::assertSame($expected, $interval->contains(Instant::of($now)), $errorMessage);
    }

    public function providerContains(): array
    {
        return [
            'at the start' => [
                1000000000,
                2000000000,
                1000000000,
                true,
                'an Interval must contain its start',
            ],
            'at the end' => [
                1000000000,
                2000000000,
                2000000000,
                false,
                'an Interval must not contain its end',
            ],
            'in the middle' => [
                1000000001,
                1000000003,
                1000000002,
                true,
                'an Interval must contain its intermediate values',
            ],
        ];
    }

    /** @dataProvider providerIntersectsWith */
    public function testIntersectsWith(int $start1, int $end1, int $start2, int $end2, bool $expected): void
    {
        $interval1 = Interval::of(Instant::of($start1), Instant::of($end1));
        $interval2 = Interval::of(Instant::of($start2), Instant::of($end2));
        self::assertSame($expected, $interval1->intersectsWith($interval2));
    }

    public function providerIntersectsWith(): array
    {
        return [
            'second is after first' => [
                100000, 200000,
                400000, 500000,
                false,
            ],
            'second is before first' => [
                400000, 500000,
                100000, 200000,
                false,
            ],
            'end of the first is start of the second' => [
                100000, 200000,
                200000, 300000,
                false,
            ],
            'start of the first is end of the second' => [
                200000, 300000,
                100000, 200000,
                false,
            ],
            'intersection' => [
                100000, 200000,
                150000, 250000,
                true,
            ],
        ];
    }

    /** @dataProvider providerGetIntersectionWith */
    public function testGetIntersectionWith(
        int $start1,
        int $end1,
        int $start2,
        int $end2,
        int $expectedStart,
        int $expectedEnd
    ): void {
        $interval1 = Interval::of(Instant::of($start1), Instant::of($end1));
        $interval2 = Interval::of(Instant::of($start2), Instant::of($end2));
        $expected = Interval::of(Instant::of($expectedStart), Instant::of($expectedEnd));

        self::assertTrue($expected->isEqualTo($interval1->getIntersectionWith($interval2)));
    }

    public function providerGetIntersectionWith(): array
    {
        return [
            'first before second' => [
                100000, 200000,
                150000, 250000,
                150000, 200000,
            ],
            'first after second' => [
                150000, 250000,
                100000, 200000,
                150000, 200000,
            ],
            'first inside second' => [
                200000, 300000,
                100000, 400000,
                200000, 300000,
            ],
            'second inside first' => [
                100000, 400000,
                200000, 300000,
                200000, 300000,
            ],
            'first = second' => [
                5000, 6000,
                5000, 6000,
                5000, 6000,
            ],
        ];
    }

    public function testGetIntersectionWithInvalidParams(): void
    {
        $interval1 = Interval::of(Instant::of(100000), Instant::of(200000));
        $interval2 = Interval::of(Instant::of(300000), Instant::of(400000));

        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage('Intervals "1970-01-02T03:46:40Z/1970-01-03T07:33:20Z" and "1970-01-04T11:20Z/1970-01-05T15:06:40Z" do not intersect.');

        $interval1->getIntersectionWith($interval2);
    }

    /** @dataProvider providerIsEqualTo */
    public function testIsEqualTo(Interval $a, Interval $b, bool $expectedResult): void
    {
        self::assertSame($expectedResult, $a->isEqualTo($b));
        self::assertSame($expectedResult, $b->isEqualTo($a));
    }

    public function providerIsEqualTo(): array
    {
        return [
            'start is not equal' => [
                Interval::of(Instant::of(100000), Instant::of(200000)),
                Interval::of(Instant::of(150000), Instant::of(200000)),
                false,
            ],
            'end is not equal' => [
                Interval::of(Instant::of(100000), Instant::of(200000)),
                Interval::of(Instant::of(100000), Instant::of(250000)),
                false,
            ],
            'both start and end are not equal' => [
                Interval::of(Instant::of(100000), Instant::of(200000)),
                Interval::of(Instant::of(150000), Instant::of(250000)),
                false,
            ],
            'intervals are equal' => [
                Interval::of(Instant::of(100000), Instant::of(200000)),
                Interval::of(Instant::of(100000), Instant::of(200000)),
                true,
            ],
        ];
    }

    /** @dataProvider providerToString */
    public function testJsonSerialize(int $epochSecondStart, int $epochSecondEnd, string $expectedString): void
    {
        $interval = Interval::of(
            Instant::of($epochSecondStart),
            Instant::of($epochSecondEnd)
        );

        self::assertSame(json_encode($expectedString), json_encode($interval));
    }

    /** @dataProvider providerToString */
    public function testToISOString(int $epochSecondStart, int $epochSecondEnd, string $expectedString): void
    {
        $interval = Interval::of(
            Instant::of($epochSecondStart),
            Instant::of($epochSecondEnd)
        );

        self::assertSame($expectedString, $interval->toISOString());
    }

    /** @dataProvider providerToString */
    public function testToString(int $epochSecondStart, int $epochSecondEnd, string $expectedString): void
    {
        $interval = Interval::of(
            Instant::of($epochSecondStart),
            Instant::of($epochSecondEnd)
        );

        self::assertSame($expectedString, (string) $interval);
    }

    public function providerToString(): array
    {
        return [
            [1000000000, 1000000000, '2001-09-09T01:46:40Z/2001-09-09T01:46:40Z'],
            [1000000000, 2000000000, '2001-09-09T01:46:40Z/2033-05-18T03:33:20Z'],
        ];
    }
}
