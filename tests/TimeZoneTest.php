<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

use function date_default_timezone_get;

use const PHP_VERSION_ID;

/**
 * Unit tests for class TimeZone.
 */
class TimeZoneTest extends AbstractTestCase
{
    /**
     * @param string $text  The text to parse.
     * @param string $class The expected class name.
     * @param string $id    The expected id.
     */
    #[DataProvider('providerParse')]
    public function testParse(string $text, string $class, string $id): void
    {
        $timeZone = TimeZone::parse($text);

        self::assertInstanceOf($class, $timeZone);
        self::assertSame($id, $timeZone->getId());
    }

    public static function providerParse(): iterable
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

    #[DataProvider('providerParseInvalidStringThrowsException')]
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        TimeZone::parse($text);
    }

    public static function providerParseInvalidStringThrowsException(): array
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
     * @param string $tz The time-zone name.
     */
    #[DataProvider('providerFromNativeDateTimeZone')]
    public function testFromNativeDateTimeZone(string $tz): void
    {
        $dateTimeZone = new DateTimeZone($tz);
        self::assertSame($tz, TimeZone::fromNativeDateTimeZone($dateTimeZone)->getId());
    }

    public static function providerFromNativeDateTimeZone(): iterable
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

    public function testFromDefaultTimeZone(): void
    {
        $defaultTimeZone = date_default_timezone_get();

        $timeZone = TimeZone::fromDefaultTimeZone();

        self::assertInstanceOf(TimeZone::class, $timeZone);
        self::assertSame($defaultTimeZone, $timeZone->getId());
    }
}
