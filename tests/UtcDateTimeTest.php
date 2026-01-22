<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Period;
use Brick\DateTime\Duration;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\UtcDateTime;
use Brick\DateTime\ZonedDateTime;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Clock\FixedClock;

/**
 * Unit tests for class ZonedDateTime.
 */
class UtcDateTimeTest extends AbstractTestCase
{
    public function testOf(): void
    {
        $a = ZonedDateTime::of(LocalDateTime::of(2020, 1, 2), TimeZone::utc());
        $this->assertInstanceOf(UtcDateTime::class, $a);
        $b = UtcDateTime::of(LocalDateTime::of(2020, 1, 2), TimeZone::utc());
        $this->assertInstanceOf(UtcDateTime::class, $b);
        $c = UtcDateTime::of(LocalDateTime::of(2020, 1, 2));
        $this->assertInstanceOf(UtcDateTime::class, $c);

        $this->assertEquals($b, $a);
        $this->assertEquals($c, $a);
        $this->assertEquals($c, $b);
    }

    public function testOfError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UtcDateTime::of(LocalDateTime::of(2020, 1, 2), TimeZone::parse('Europe/Moscow'));
    }

    /**
     * @dataProvider providerFromDateTime
     * @param string $dateTimeString
     * @param string $timeZone
     * @param string $expected
     * @throws \Exception
     */
    public function testFromDateTime(string $dateTimeString, string $timeZone, string $expected): void
    {
        $dateTime = new \DateTime($dateTimeString, new \DateTimeZone($timeZone));
        $this->assertIs(UtcDateTime::class, $expected, UtcDateTime::fromDateTime($dateTime));
    }

    public function providerFromDateTime() : array
    {
        return [
            ['2018-07-21 14:09:10.23456', 'America/Los_Angeles', '2018-07-21T21:09:10.23456Z'],
            ['2019-01-21 17:59', 'America/Los_Angeles', '2019-01-22T01:59Z'],
            ['2019-01-23 09:10:11.123', '+05:30', '2019-01-23T03:40:11.123Z'],
        ];
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text   The string to parse.
     * @param string $date   The expected date string.
     * @param string $time   The expected time string.
     * @param string $offset The expected time-zone offset.
     * @param string $zone   The expected time-zone, should be the same as offset when no region is specified.
     */
    public function testParse(string $text, string $date, string $time, string $offset, string $zone): void
    {
        $zonedDateTime = UtcDateTime::parse($text);

        $this->assertInstanceOf(UtcDateTime::class, $zonedDateTime);

        $this->assertSame($date, (string) $zonedDateTime->getDate());
        $this->assertSame($time, (string) $zonedDateTime->getTime());
        $this->assertSame($offset, (string) $zonedDateTime->getTimeZoneOffset());
        $this->assertSame($zone, (string) $zonedDateTime->getTimeZone());
    }

    public function providerParse() : array
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
            ['2001-02-03T01:02:03.456+00:00[Europe/London]', '2001-02-03', '01:02:03.456', 'Z', 'Z']
        ];
    }
}
