<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\LocalDateTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\UtcDateTime;
use Brick\DateTime\ZonedDateTime;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class ZonedDateTime.
 */
class UtcDateTimeTest extends AbstractTestCase
{
    public function testOf(): void
    {
        $a = ZonedDateTime::of(LocalDateTime::of(2020, 1, 2), TimeZone::utc());
        self::assertInstanceOf(UtcDateTime::class, $a);
        self::assertSame('2020-01-02T00:00Z', (string) $a);

        $b = UtcDateTime::of(LocalDateTime::of(2020, 1, 2), TimeZone::utc());
        self::assertInstanceOf(UtcDateTime::class, $b);
        self::assertSame('2020-01-02T00:00Z', (string) $b);

        $c = UtcDateTime::of(LocalDateTime::of(2020, 1, 2));
        self::assertInstanceOf(UtcDateTime::class, $c);
        self::assertSame('2020-01-02T00:00Z', (string) $c);
    }

    public function testOfError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UtcDateTime::of(LocalDateTime::of(2020, 1, 2), TimeZone::parse('Europe/Moscow'));
    }

    /**
     * @param string $text   The string to parse.
     * @param string $date   The expected date string.
     * @param string $time   The expected time string.
     * @param string $offset The expected time-zone offset.
     * @param string $zone   The expected time-zone, should be the same as offset when no region is specified.
     */
    #[DataProvider('providerParse')]
    public function testParse(string $text, string $date, string $time, string $offset, string $zone): void
    {
        $zonedDateTime = UtcDateTime::parse($text);

        self::assertInstanceOf(UtcDateTime::class, $zonedDateTime);

        self::assertSame($date, (string) $zonedDateTime->getDate());
        self::assertSame($time, (string) $zonedDateTime->getTime());
        self::assertSame($offset, (string) $zonedDateTime->getTimeZoneOffset());
        self::assertSame($zone, (string) $zonedDateTime->getTimeZone());
    }

    public static function providerParse(): array
    {
        return [
            ['2001-02-03T01:02Z', '2001-02-03', '01:02', 'Z', 'Z'],
            ['2001-02-03T01:02:03Z', '2001-02-03', '01:02:03', 'Z', 'Z'],
            ['2001-02-03T01:02:03.456Z', '2001-02-03', '01:02:03.456', 'Z', 'Z'],
            ['2001-02-03T01:02-03:00', '2001-02-03', '04:02', 'Z', 'Z'],
            ['2001-02-03T01:02:03+04:00', '2001-02-02', '21:02:03', 'Z', 'Z'],

            //['2001-02-03T01:02:03.456+12:34:56', '2001-02-03', '01:02:03.456', 'Z', 'Z'],
            ['2001-02-03T01:02Z[Europe/London]', '2001-02-03', '01:02', 'Z', 'Z'],
            ['2001-02-03T01:02+00:00[Europe/London]', '2001-02-03', '01:02', 'Z', 'Z'],
            ['2001-02-03T01:02:03-00:00[Europe/London]', '2001-02-03', '01:02:03', 'Z', 'Z'],
            ['2001-02-03T01:02:03.456+00:00[Europe/London]', '2001-02-03', '01:02:03.456', 'Z', 'Z'],
        ];
    }

    #[DataProvider('provideFromSqlFormat')]
    public function testFromSqlFormat(string $input, string $expected): void
    {
        $dateTime = UtcDateTime::fromSqlFormat($input);

        self::assertSame($expected, (string) $dateTime);
    }

    public static function provideFromSqlFormat(): array
    {
        return [
            [
                '2018-10-13 12:13:14',
                '2018-10-13T12:13:14Z',
            ],
            [
                '2018-10-13 12:13:14.000',
                '2018-10-13T12:13:14Z',
            ],
            [
                '2018-10-13 12:13:14.000000',
                '2018-10-13T12:13:14Z',
            ],
            [
                '2018-10-13 12:13:14.000000001',
                '2018-10-13T12:13:14.000000001Z',
            ],
            [
                '2018-10-13 12:13:14.0000000059',
                '2018-10-13T12:13:14.000000005Z',
            ],
            [
                '2018-10-13 12:13:14.00203',
                '2018-10-13T12:13:14.00203Z',
            ],
        ];
    }

    #[DataProvider('provideFromSqlFormatInvalidCases')]
    public function testFromSqlFormatInvalidCases(string $input, string $timeZone, string $expected): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expected);

        UtcDateTime::fromSqlFormat($input, TimeZone::parse($timeZone));
    }

    public static function provideFromSqlFormatInvalidCases(): array
    {
        return [
            [
                '2018-10-23 12:13:14 ',
                'Z',
                'Input expected to be in "Y-m-d H:i:s" format. Got "2018-10-23 12:13:14 "',
            ],
            [
                '2018-10-23 12:13:14.abba',
                'Z',
                'Incorrect fractional part in format. Got "2018-10-23 12:13:14.abba"',
            ],
            [
                '2018-10-23T12:13:14Z',
                'Z',
                'Input expected to be in "Y-m-d H:i:s" format. Got "2018-10-23T12:13:14Z"',
            ],
            [
                '2018-10-23T12:13:14',
                'Europe/Moscow',
                'Create UtcDateTime with not UTC timezone is not supported',
            ],
        ];
    }

    #[DataProvider('providerToCanonicalFormat')]
    public function testToCanonicalFormat(UtcDateTime $input, int $precision, string $expected): void
    {
        self::assertSame($expected, $input->toCanonicalFormat($precision));
    }

    public static function providerToCanonicalFormat(): array
    {
        return [
            [
                UtcDateTime::parse('2022-03-30T00:00Z'),
                6,
                '2022-03-30T00:00:00.000000Z',
            ],
            [
                UtcDateTime::parse('2022-03-30T10:11Z'),
                6,
                '2022-03-30T10:11:00.000000Z',
            ],
            [
                UtcDateTime::parse('2022-03-30T10:11:12Z'),
                6,
                '2022-03-30T10:11:12.000000Z',
            ],
            [
                UtcDateTime::parse('2022-03-30T10:11:12.1Z'),
                6,
                '2022-03-30T10:11:12.100000Z',
            ],
            [
                UtcDateTime::parse('2022-03-30T10:11:12.001Z'),
                6,
                '2022-03-30T10:11:12.001000Z',
            ],
            [
                UtcDateTime::parse('2022-03-30T10:11:12.000001Z'),
                6,
                '2022-03-30T10:11:12.000001Z',
            ],
            [
                UtcDateTime::parse('2022-03-30T10:11:12.000000999Z'),
                6,
                '2022-03-30T10:11:12.000000Z',
            ],
            [
                UtcDateTime::parse('1000-03-30T10:11:12.123456789Z'),
                6,
                '1000-03-30T10:11:12.123456Z',
            ],
            [
                UtcDateTime::parse('1000-03-30T10:11:12.123456789Z'),
                9,
                '1000-03-30T10:11:12.123456789Z',
            ],
            [
                UtcDateTime::parse('1000-03-30T10:11:12.123456789Z'),
                0,
                '1000-03-30T10:11:12Z',
            ],
            [
                UtcDateTime::parse('1000-03-30T10:11:12.123456789Z'),
                1,
                '1000-03-30T10:11:12.1Z',
            ],
            [
                UtcDateTime::parse('1000-03-30T10:11:12Z'),
                9,
                '1000-03-30T10:11:12.000000000Z',
            ],
        ];
    }
}
