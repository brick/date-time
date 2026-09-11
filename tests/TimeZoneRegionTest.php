<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZoneRegion;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

/**
 * Unit tests for class TimeZoneRegion.
 */
class TimeZoneRegionTest extends AbstractTestCase
{
    public function testOf(): void
    {
        self::assertSame('Europe/London', TimeZoneRegion::of('Europe/London')->getId());
    }

    #[DataProvider('providerOfInvalidRegionThrowsException')]
    public function testOfInvalidRegionThrowsException(string $region): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneRegion::of($region);
    }

    public static function providerOfInvalidRegionThrowsException(): array
    {
        return [
            [''],
            ['Z'],
            ['z'],
            ['+01:00'],
            ['-01:00'],
            ['Unknown/Region'],
        ];
    }

    public function testParse(): void
    {
        self::assertSame('Europe/London', TimeZoneRegion::parse('Europe/London')->getId());
    }

    #[DataProvider('providerParseInvalidStringThrowsException')]
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        TimeZoneRegion::parse($text);
    }

    public static function providerParseInvalidStringThrowsException(): array
    {
        return [
            [''],
            ['Europe.London'],
        ];
    }

    #[DataProvider('providerGetAllTimeZones')]
    public function testGetAllTimeZones(bool $includeObsolete): void
    {
        $identifiers = TimeZoneRegion::getAllIdentifiers($includeObsolete);
        self::assertGreaterThan(1, count($identifiers));

        $expectedIdentifiers = [
            'UTC',
            'Europe/London',
            'America/Los_Angeles',
        ];

        $expectedObsoleteIdentifiers = [
            'CET',
            'US/Alaska',
            'Mexico/General',
        ];

        foreach ($expectedIdentifiers as $identifier) {
            self::assertContains($identifier, $identifiers);
        }

        foreach ($expectedObsoleteIdentifiers as $identifier) {
            if ($includeObsolete) {
                self::assertContains($identifier, $identifiers);
            } else {
                self::assertNotContains($identifier, $identifiers);
            }
        }
    }

    public static function providerGetAllTimeZones(): array
    {
        return [
            [false],
            [true],
        ];
    }

    #[DataProvider('providerGetTimeZonesForCountry')]
    public function testGetTimeZonesForCountry(string $countryCode, string ...$expectedIdentifiers): void
    {
        $identifiers = TimeZoneRegion::getIdentifiersForCountry($countryCode);

        self::assertSame($expectedIdentifiers, $identifiers);
    }

    public static function providerGetTimeZonesForCountry(): array
    {
        return [
            ['FR', 'Europe/Paris'],
            ['GB', 'Europe/London'],
            ['DE', 'Europe/Berlin', 'Europe/Busingen'],
            ['CH', 'Europe/Zurich'],
            ['PL', 'Europe/Warsaw'],
            ['ES', 'Africa/Ceuta', 'Atlantic/Canary', 'Europe/Madrid'],
            ['IT', 'Europe/Rome'],
            ['CN', 'Asia/Shanghai', 'Asia/Urumqi'],
            ['RE', 'Indian/Reunion'],
        ];
    }

    /**
     * @param string $region         The time-zone region.
     * @param int    $epochSecond    The instant to test.
     * @param int    $expectedOffset The expected offset in seconds.
     */
    #[DataProvider('providerGetOffset')]
    public function testGetOffset(string $region, int $epochSecond, int $expectedOffset): void
    {
        $actualOffset = TimeZoneRegion::of($region)->getOffset(Instant::of($epochSecond));
        self::assertSame($expectedOffset, $actualOffset);
    }

    public static function providerGetOffset(): array
    {
        return [
            ['Europe/London', 1419984000,    0],
            ['Europe/Paris',  1419984000, 3600],
            ['Europe/London', 1406764800, 3600],
            ['Europe/Paris',  1406764800, 7200],
        ];
    }

    public function testToNativeDateTimeZone(): void
    {
        $dateTimeZone = TimeZoneRegion::of('Europe/London')->toNativeDateTimeZone();

        self::assertInstanceOf(DateTimeZone::class, $dateTimeZone);
        self::assertSame('Europe/London', $dateTimeZone->getName());
    }

    public function testGetId(): void
    {
        self::assertSame('Europe/Paris', TimeZoneRegion::of('Europe/Paris')->getId());
    }

    public function testToString(): void
    {
        self::assertSame('America/Los_Angeles', (string) TimeZoneRegion::of('America/Los_Angeles'));
    }

    public function testUTC(): void
    {
        $utcTimeZoneRegion = TimeZoneRegion::utc();
        $this->assertInstanceOf(TimeZoneRegion::class, $utcTimeZoneRegion);
        $this->assertSame('UTC', $utcTimeZoneRegion->getId());
    }
}
