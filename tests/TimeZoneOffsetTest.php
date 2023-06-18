<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZoneOffset;
use DateTimeImmutable;

use const PHP_VERSION_ID;

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
    public function testOf(int $hours, int $minutes, int $seconds, int $totalSeconds): void
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::of($hours, $minutes, $seconds));
    }

    public function providerOf(): iterable
    {
        yield from [
            [0, 0, 0, 0],
            [0, 1, 0, 60],
            [1, 0, 0, 3600],
            [1, 2, 0, 3720],

            [-1, -1, 0, -3660],
            [-1, 0, 0, -3600],
            [0, -1, 0, -60],
            [0, 1, 0, 60],
            [1, 0, 0, 3600],
            [1, 1, 0, 3660],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield from [
                [1, 0, 30, 3630],
                [-1, 0, -30, -3630],
            ];
        }
    }

    /**
     * @dataProvider providerOfInvalidValuesThrowsException
     */
    public function testOfInvalidValuesThrowsException(int $hours, int $minutes, int $seconds): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::of($hours, $minutes, $seconds);
    }

    public function providerOfInvalidValuesThrowsException(): iterable
    {
        yield from [
            [0, 60, 0],
            [0, -60, 0],
            [1, -1, 0],
            [-1, 1, 0],
            [19, 0, 0],
            [-19, 0, 0],
        ];

        if (PHP_VERSION_ID < 80107) {
            yield from [
                [1, 0, 30],
                [-1, 0, -30],
            ];
        }
    }

    /**
     * @dataProvider providerTotalSeconds
     */
    public function testOfTotalSeconds(int $totalSeconds): void
    {
        $this->assertTimeZoneOffsetIs($totalSeconds, TimeZoneOffset::ofTotalSeconds($totalSeconds));
    }

    public function providerTotalSeconds(): iterable
    {
        yield from [
            [-64800],
            [-3600],
            [-60],
            [0],
            [60],
            [3600],
            [64800],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield from [
                [-3630],
                [-1],
                [1],
                [3630],
            ];
        }
    }

    /**
     * @dataProvider providerOfInvalidTotalSecondsThrowsException
     */
    public function testOfInvalidTotalSecondsThrowsException(int $totalSeconds): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneOffset::ofTotalSeconds($totalSeconds);
    }

    public function providerOfInvalidTotalSecondsThrowsException(): iterable
    {
        yield from [
            [-64860],
            [64860],
        ];

        if (PHP_VERSION_ID < 80107) {
            yield from [
                [-3630],
                [-1],
                [1],
                [3630],
            ];
        }
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

    public function providerParse(): iterable
    {
        yield from [
            ['+00:00', 0],
            ['-00:00', 0],
            ['+01:00', 3600],
            ['-01:00', -3600],
            ['+01:30', 5400],
            ['-01:30', -5400],
            ['+18:00', 64800],
            ['-18:00', -64800],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield from [
                ['+01:00:30', 3630],
                ['-01:00:30', -3630],
            ];
        }
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     */
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        TimeZoneOffset::parse($text);
    }

    public function providerParseInvalidStringThrowsException(): array
    {
        return [
            [''],
            ['00:00'],
            ['+00'],
            ['+00:'],
            ['+00:00:'],
            ['+1:00'],
            ['+01:1'],
            ['+01:01:1'],
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

    public function providerParseValueStringThrowsException(): iterable
    {
        yield from [
            ['+18:00:01'],
            ['+18:01'],
            ['+19:00'],
            ['-19:00'],
            ['-18:01'],
            ['-18:00:01'],
        ];

        if (PHP_VERSION_ID < 80107) {
            yield from [
                ['+01:00:30'],
                ['-01:00:30'],
            ];
        }
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

    public function providerGetId(): iterable
    {
        yield from [
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

        if (PHP_VERSION_ID >= 80107) {
            yield from [
                [30, '+00:00:30'],
                [90, '+00:01:30'],
                [3599, '+00:59:59'],
                [3601, '+01:00:01'],
                [-30, '-00:00:30'],
                [-90, '-00:01:30'],
                [-3599, '-00:59:59'],
                [-3601, '-01:00:01'],
            ];
        }
    }

    public function testGetOffset(): void
    {
        $whateverInstant = Instant::of(123456789, 987654321);
        $timeZoneOffset = TimeZoneOffset::ofTotalSeconds(-18000);

        $this->assertSame(-18000, $timeZoneOffset->getOffset($whateverInstant));
    }

    /**
     * @dataProvider providerToNativeDateTimeZone
     *
     * @param int    $totalSeconds The total offset seconds.
     * @param string $string       The expected string.
     */
    public function testToDateTimeZone(int $totalSeconds, string $string): void
    {
        $dateTimeZone = TimeZoneOffset::ofTotalSeconds($totalSeconds)->toDateTimeZone();

        $this->assertSame($string, $dateTimeZone->getName());
        $this->assertSame($totalSeconds, $dateTimeZone->getOffset(new DateTimeImmutable()));
    }

    /**
     * @dataProvider providerToNativeDateTimeZone
     *
     * @param int    $totalSeconds The total offset seconds.
     * @param string $string       The expected string.
     */
    public function testToNativeDateTimeZone(int $totalSeconds, string $string): void
    {
        $dateTimeZone = TimeZoneOffset::ofTotalSeconds($totalSeconds)->toNativeDateTimeZone();

        $this->assertSame($string, $dateTimeZone->getName());
        $this->assertSame($totalSeconds, $dateTimeZone->getOffset(new DateTimeImmutable()));
    }

    public function providerToNativeDateTimeZone(): iterable
    {
        yield from [
            [-18000, '-05:00'],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield from [
                [-1, '-00:00'],
                [3630, '+01:00'],
            ];
        }
    }
}
