<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZoneOffset;

/**
 * Units tests for class TimeZoneOffset.
 */
class TimeZoneOffsetTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     *
     * @param int $hours        The hours part of the offset.
     * @param int $minutes      The minutes part of the offset.
     * @param int $totalSeconds The expected total number of seconds.
     */
    public function testOf(int $hours, int $minutes, int $totalSeconds): void
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::of($hours, $minutes));
    }

    public function providerOf() : array
    {
        return [
            [0, 0, 0],
            [0, 1, 60],
            [1, 0, 3600],
            [1, 2, 3720],

            [-1, -1, -3660],
            [-1, 0, -3600],
            [0, -1, -60],
            [0, 1, 60],
            [1, 0, 3600],
            [1, 1, 3660],
        ];
    }

    /**
     * @dataProvider providerOfInvalidValuesThrowsException
     */
    public function testOfInvalidValuesThrowsException(int $hours, int $minutes): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::of($hours, $minutes);
    }

    public function providerOfInvalidValuesThrowsException() : array
    {
        return [
            [0, 60],
            [0, -60],
            [1, -1],
            [-1, 1],
            [19, 0],
            [-19, 0],
        ];
    }

    /**
     * @dataProvider providerTotalSeconds
     */
    public function testOfTotalSeconds(int $totalSeconds): void
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    public function providerTotalSeconds() : array
    {
        return [
            [-64800],
            [-3600],
            [-60],
            [0],
            [60],
            [3600],
            [64800]
        ];
    }

    /**
     * @dataProvider providerOfInvalidTotalSecondsThrowsException
     */
    public function testOfInvalidTotalSecondsThrowsException(int $totalSeconds): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::ofTotalSeconds($totalSeconds);
    }

    public function providerOfInvalidTotalSecondsThrowsException() : array
    {
        return [
            [-1],
            [1],
            [-64860],
            [64860]
        ];
    }

    public function testUtc(): void
    {
        $this->assertTimeZoneOffsetIs(0, TimeZoneOffset::utc());
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text         The text to parse.
     * @param int    $totalSeconds The expected total offset seconds.
     */
    public function testParse(string $text, int $totalSeconds): void
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::parse($text));
    }

    public function providerParse() : array
    {
        return [
            ['+00:00', 0],
            ['-00:00', 0],
            ['+01:00', 3600],
            ['-01:00', -3600],
            ['+01:30', 5400],
            ['-01:30', -5400],
            ['+18:00', 64800],
            ['-18:00', -64800],
        ];
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     */
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        TimeZoneOffset::parse($text);
    }

    public function providerParseInvalidStringThrowsException() : array
    {
        return [
            [''],
            ['00:00'],
            ['+00'],
            ['+00:'],
            ['+00:00:'],
            ['+1:00'],
            ['+01:1'],
            ['+01:01:1']
        ];
    }

    /**
     * @dataProvider providerParseValueStringThrowsException
     */
    public function testParseInvalidValueThrowsException(string $text): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::parse($text);
    }

    public function providerParseValueStringThrowsException() : array
    {
        return [
            ['+18:00:01'],
            ['+18:01'],
            ['+19:00'],
            ['-19:00'],
            ['-18:01'],
            ['-18:00:01']
        ];
    }

    /**
     * @dataProvider providerGetId
     *
     * @param int    $totalSeconds The total offset seconds.
     * @param string $expectedId   The expected id.
     */
    public function testGetId(int $totalSeconds, string $expectedId): void
    {
        $this->assertSame($expectedId, TimeZoneOffset::ofTotalSeconds($totalSeconds)->getId());
    }

    /**
     * @dataProvider providerGetId
     *
     * @param int    $totalSeconds The total offset seconds.
     * @param string $string       The expected string.
     */
    public function testToString(int $totalSeconds, string $string): void
    {
        $this->assertSame($string, (string) TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    public function providerGetId() : array
    {
        return [
            [0, 'Z'],
            [60, '+00:01'],
            [120, '+00:02'],
            [3600, '+01:00'],
            [7380, '+02:03'],
            [64800, '+18:00'],
            [-60, '-00:01'],
            [-120, '-00:02'],
            [-3600, '-01:00'],
            [-7380, '-02:03'],
            [-64800, '-18:00'],
        ];
    }

    public function testGetOffset(): void
    {
        $whateverInstant = Instant::of(123456789, 987654321);
        $timeZoneOffset = TimeZoneOffset::ofTotalSeconds(-18000);

        $this->assertSame(-18000, $timeZoneOffset->getOffset($whateverInstant));
    }

    public function testToDateTimeZone(): void
    {
        $dateTimeZone = TimeZoneOffset::ofTotalSeconds(-18000)->toDateTimeZone();

        $this->assertInstanceOf(\DateTimeZone::class, $dateTimeZone);
        $this->assertSame('-05:00', $dateTimeZone->getName());
    }

    public function testToNativeDateTimeZone(): void
    {
        $dateTimeZone = TimeZoneOffset::ofTotalSeconds(-18000)->toNativeDateTimeZone();

        $this->assertInstanceOf(\DateTimeZone::class, $dateTimeZone);
        $this->assertSame('-05:00', $dateTimeZone->getName());
    }
}
