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
     * @param int $seconds      The seconds part of the offset.
     * @param int $totalSeconds The expected total number of seconds.
     */
    public function testOf(int $hours, int $minutes, int $seconds, int $totalSeconds)
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::of($hours, $minutes, $seconds));
    }

    /**
     * @return array
     */
    public function providerOf() : array
    {
        return [
            [0, 0, 0, 0],
            [0, 0, 1, 1],
            [0, 1, 0, 60],
            [0, 1, 2, 62],
            [1, 0, 0, 3600],
            [1, 0, 2, 3602],
            [1, 2, 0, 3720],
            [1, 2, 3, 3723],

            [-1, -1, -1, -3661],
            [-1, -1, 0, -3660],
            [-1, 0, -1, -3601],
            [-1, 0, 0, -3600],
            [0, -1, -1, -61],
            [0, -1, 0, -60],
            [0, 0, -1, -1],
            [0, 0, 0, 0],
            [0, 0, 1, 1],
            [0, 1, 0, 60],
            [0, 1, 1, 61],
            [1, 0, 0, 3600],
            [1, 0, 1, 3601],
            [1, 1, 0, 3660],
            [1, 1, 1, 3661],
        ];
    }

    /**
     * @dataProvider providerOfInvalidValuesThrowsException
     *
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     */
    public function testOfInvalidValuesThrowsException(int $hours, int $minutes, int $seconds)
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::of($hours, $minutes, $seconds);
    }

    /**
     * @return array
     */
    public function providerOfInvalidValuesThrowsException() : array
    {
        return [
            [0, 0, 60],
            [0, 0, -60],
            [0, 60, 0],
            [0, -60, 0],
            [19, 0, 0],
            [-19, 0, 0],

            [-1, -1, 1],
            [-1, 0, 1],
            [-1, 1, -1],
            [-1, 1, 0],
            [-1, 1, 1],
            [0, -1, 1],
            [0, 1, -1],
            [1, -1, -1],
            [1, -1, 0],
            [1, -1, 1],
            [1, 0, -1],
            [1, 1, -1],
        ];
    }

    /**
     * @dataProvider providerTotalSeconds
     *
     * @param int $totalSeconds
     */
    public function testOfTotalSeconds(int $totalSeconds)
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    /**
     * @return array
     */
    public function providerTotalSeconds() : array
    {
        return [
            [-64800],
            [-3600],
            [0],
            [3600],
            [64800]
        ];
    }

    /**
     * @dataProvider providerOfInvalidTotalSecondsThrowsException
     *
     * @param int $totalSeconds
     */
    public function testOfInvalidTotalSecondsThrowsException(int $totalSeconds)
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::ofTotalSeconds($totalSeconds);
    }

    /**
     * @return array
     */
    public function providerOfInvalidTotalSecondsThrowsException() : array
    {
        return [
            [-64801],
            [64801]
        ];
    }

    public function testUtc()
    {
        $this->assertTimeZoneOffsetIs(0, TimeZoneOffset::utc());
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text         The text to parse.
     * @param int    $totalSeconds The expected total offset seconds.
     */
    public function testParse(string $text, int $totalSeconds)
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse() : array
    {
        return [
            ['+00:00', 0],
            ['-00:00', 0],
            ['+01:00', 3600],
            ['-01:00', -3600],
            ['+01:30', 5400],
            ['-01:30', -5400],
            ['+01:30:05', 5405],
            ['-01:30:05', -5405],
            ['+18:00', 64800],
            ['-18:00', -64800],
            ['+18:00:00', 64800],
            ['-18:00:00', -64800],
        ];
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsException(string $text)
    {
        $this->expectException(DateTimeParseException::class);
        TimeZoneOffset::parse($text);
    }

    /**
     * @return array
     */
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
     *
     * @param string $text
     */
    public function testParseInvalidValueThrowsException(string $text)
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::parse($text);
    }

    /**
     * @return array
     */
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
    public function testGetId(int $totalSeconds, string $expectedId)
    {
        $this->assertSame($expectedId, TimeZoneOffset::ofTotalSeconds($totalSeconds)->getId());
    }

    /**
     * @dataProvider providerGetId
     *
     * @param int    $totalSeconds The total offset seconds.
     * @param string $string       The expected string.
     */
    public function testToString(int $totalSeconds, string $string)
    {
        $this->assertSame($string, (string) TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    /**
     * @return array
     */
    public function providerGetId() : array
    {
        return [
            [0, 'Z'],
            [1, '+00:00:01'],
            [59, '+00:00:59'],
            [60, '+00:01'],
            [61, '+00:01:01'],
            [3599, '+00:59:59'],
            [3600, '+01:00'],
            [3601, '+01:00:01'],
            [64800, '+18:00'],
            [-1, '-00:00:01'],
            [-59, '-00:00:59'],
            [-60, '-00:01'],
            [-61, '-00:01:01'],
            [-3599, '-00:59:59'],
            [-3600, '-01:00'],
            [-3601, '-01:00:01'],
            [-64800, '-18:00'],
        ];
    }

    public function testGetOffset()
    {
        $whateverInstant = Instant::of(123456789, 987654321);
        $timeZoneOffset = TimeZoneOffset::ofTotalSeconds(-18000);

        $this->assertSame(-18000, $timeZoneOffset->getOffset($whateverInstant));
    }

    public function testToDateTimeZone()
    {
        $dateTimeZone = TimeZoneOffset::ofTotalSeconds(-18000)->toDateTimeZone();

        $this->assertInstanceOf(\DateTimeZone::class, $dateTimeZone);
        $this->assertSame('-05:00', $dateTimeZone->getName());
    }
}
