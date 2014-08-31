<?php

namespace Brick\Tests\DateTime;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZoneOffset;

/**
 * Units tests for class TimeZoneOffset.
 */
class TimeZoneOffsetTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     *
     * @param integer $hours        The hours part of the offset.
     * @param integer $minutes      The minutes part of the offset.
     * @param integer $seconds      The seconds part of the offset.
     * @param integer $totalSeconds The expected total number of seconds.
     */
    public function testOf($hours, $minutes, $seconds, $totalSeconds)
    {
        $this->assertTimeZoneOffsetEquals($totalSeconds, TimeZoneOffset::of($hours, $minutes, $seconds));
    }

    /**
     * @return array
     */
    public function providerOf()
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
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     */
    public function testOfInvalidValuesThrowsException($hours, $minutes, $seconds)
    {
        TimeZoneOffset::of($hours, $minutes, $seconds);
    }

    /**
     * @return array
     */
    public function providerOfInvalidValuesThrowsException()
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
     * @param integer $totalSeconds
     */
    public function testOfTotalSeconds($totalSeconds)
    {
        $this->assertTimeZoneOffsetEquals($totalSeconds, TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    /**
     * @return array
     */
    public function providerTotalSeconds()
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
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $totalSeconds
     */
    public function testOfInvalidTotalSecondsThrowsException($totalSeconds)
    {
        TimeZoneOffset::ofTotalSeconds($totalSeconds);
    }

    /**
     * @return array
     */
    public function providerOfInvalidTotalSecondsThrowsException()
    {
        return [
            [-64801],
            [64801]
        ];
    }

    public function testUtc()
    {
        $this->assertTimeZoneOffsetEquals(0, TimeZoneOffset::utc());
    }

    /**
     * @dataProvider providerParse
     *
     * @param string  $text         The text to parse.
     * @param integer $totalSeconds The expected total offset seconds.
     */
    public function testParse($text, $totalSeconds)
    {
        $this->assertTimeZoneOffsetEquals($totalSeconds, TimeZoneOffset::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse()
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
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsException($text)
    {
        TimeZoneOffset::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
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
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param string $text
     */
    public function testParseInvalidValueThrowsException($text)
    {
        TimeZoneOffset::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseValueStringThrowsException()
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
     * @param integer $totalSeconds The total offset seconds.
     * @param string  $expectedId   The expected id.
     */
    public function testGetId($totalSeconds, $expectedId)
    {
        $this->assertSame($expectedId, TimeZoneOffset::ofTotalSeconds($totalSeconds)->getId());
    }

    /**
     * @dataProvider providerGetId
     *
     * @param integer $totalSeconds The total offset seconds.
     * @param string  $string       The expected string.
     */
    public function testToString($totalSeconds, $string)
    {
        $this->assertSame($string, (string) TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    /**
     * @return array
     */
    public function providerGetId()
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
