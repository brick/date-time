<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use DateTimeZone;

use const PHP_VERSION_ID;

/**
 * Unit tests for class TimeZone.
 */
class TimeZoneTest extends AbstractTestCase
{
    /**
     * @dataProvider providerParse
     *
     * @param string $text  The text to parse.
     * @param string $class The expected class name.
     * @param string $id    The expected id.
     */
    public function testParse(string $text, string $class, string $id): void
    {
        $timeZone = TimeZone::parse($text);

        self::assertInstanceOf($class, $timeZone);
        self::assertSame($id, $timeZone->getId());
    }

    public function providerParse(): iterable
    {
        yield from [
            ['Z', TimeZoneOffset::class, 'Z'],
            ['z', TimeZoneOffset::class, 'Z'],
            ['+01:00', TimeZoneOffset::class, '+01:00'],
            ['-02:30', TimeZoneOffset::class, '-02:30'],
            ['Europe/London', TimeZoneRegion::class, 'Europe/London'],
            ['America/Los_Angeles', TimeZoneRegion::class, 'America/Los_Angeles'],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield ['-02:30:30', TimeZoneOffset::class, '-02:30:30'];
        }
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     */
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        TimeZone::parse($text);
    }

    public function providerParseInvalidStringThrowsException(): array
    {
        return [
            [''],
            ['+'],
            ['-'],
        ];
    }

    public function testUtc(): void
    {
        $utc = TimeZone::utc();

        self::assertTimeZoneOffsetIs(0, $utc);
        self::assertSame($utc, TimeZone::utc());
    }

    public function testIsEqualTo(): void
    {
        self::assertTrue(TimeZone::utc()->isEqualTo(TimeZoneOffset::ofTotalSeconds(0)));
        self::assertFalse(TimeZone::utc()->isEqualTo(TimeZoneOffset::ofTotalSeconds(3600)));
    }

    /**
     * @dataProvider providerFromNativeDateTimeZone
     *
     * @param string $tz The time-zone name.
     */
    public function testFromNativeDateTimeZone(string $tz): void
    {
        $dateTimeZone = new DateTimeZone($tz);
        self::assertSame($tz, TimeZone::fromNativeDateTimeZone($dateTimeZone)->getId());
    }

    public function providerFromNativeDateTimeZone(): iterable
    {
        yield from [
            ['Z'],
            ['+01:00'],
            ['Europe/London'],
            ['America/Los_Angeles'],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield ['-02:30:30'];
        }
    }
}
