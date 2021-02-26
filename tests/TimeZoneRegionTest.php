<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZoneRegion;

/**
 * Unit tests for class TimeZoneRegion.
 */
class TimeZoneRegionTest extends AbstractTestCase
{
    public function testOf(): void
    {
        $this->assertSame('Europe/London', TimeZoneRegion::of('Europe/London')->getId());
    }

    /**
     * @dataProvider providerOfInvalidRegionThrowsException
     */
    public function testOfInvalidRegionThrowsException(string $region): void
    {
        $this->expectException(DateTimeException::class);
        TimeZoneRegion::of($region);
    }

    public function providerOfInvalidRegionThrowsException() : array
    {
        return [
            [''],
            ['Z'],
            ['z'],
            ['+01:00'],
            ['-01:00'],
            ['Unknown/Region']
        ];
    }

    public function testParse(): void
    {
        $this->assertSame('Europe/London', TimeZoneRegion::parse('Europe/London')->getId());
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     */
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        TimeZoneRegion::parse($text);
    }

    public function providerParseInvalidStringThrowsException() : array
    {
        return [
            [''],
            ['Europe.London']
        ];
    }

    /**
     * @dataProvider providerGetAllTimeZones
     */
    public function testGetAllTimeZones(bool $includeObsolete): void
    {
        $identifiers = TimeZoneRegion::getAllIdentifiers($includeObsolete);
        $this->assertGreaterThan(1, \count($identifiers));

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
            $this->assertContains($identifier, $identifiers);
        }

        foreach ($expectedObsoleteIdentifiers as $identifier) {
            if ($includeObsolete) {
                $this->assertContains($identifier, $identifiers);
            } else {
                $this->assertNotContains($identifier, $identifiers);
            }
        }
    }

    public function providerGetAllTimeZones() : array
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider providerGetTimeZonesForCountry
     */
    public function testGetTimeZonesForCountry(string $countryCode, string ...$expectedIdentifiers): void
    {
        $identifiers = TimeZoneRegion::getIdentifiersForCountry($countryCode);

        $this->assertSame($expectedIdentifiers, $identifiers);
    }

    public function providerGetTimeZonesForCountry() : array
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
     * @dataProvider providerGetOffset
     *
     * @param string $region         The time-zone region.
     * @param int    $epochSecond    The instant to test.
     * @param int    $expectedOffset The expected offset in seconds.
     */
    public function testGetOffset(string $region, int $epochSecond, int $expectedOffset): void
    {
        $actualOffset = TimeZoneRegion::of($region)->getOffset(Instant::of($epochSecond));
        $this->assertSame($expectedOffset, $actualOffset);
    }

    public function providerGetOffset() : array
    {
        return [
            ['Europe/London', 1419984000,    0],
            ['Europe/Paris',  1419984000, 3600],
            ['Europe/London', 1406764800, 3600],
            ['Europe/Paris',  1406764800, 7200],
        ];
    }

    public function testToDateTimeZone(): void
    {
        $dateTimeZone = TimeZoneRegion::of('Europe/London')->toDateTimeZone();

        $this->assertInstanceOf(\DateTimeZone::class, $dateTimeZone);
        $this->assertSame('Europe/London', $dateTimeZone->getName());
    }

    public function testGetId(): void
    {
        $this->assertSame('Europe/Paris', TimeZoneRegion::of('Europe/Paris')->getId());
    }

    public function testToString(): void
    {
        $this->assertSame('America/Los_Angeles', (string) TimeZoneRegion::of('America/Los_Angeles'));
    }
}
