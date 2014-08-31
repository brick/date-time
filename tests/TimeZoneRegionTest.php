<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Instant;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;

/**
 * Unit tests for class TimeZoneRegion.
 */
class TimeZoneRegionTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertSame('Europe/London', TimeZoneRegion::of('Europe/London')->getId());
    }

    /**
     * @dataProvider providerOfInvalidRegionThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param string $region
     */
    public function testOfInvalidRegionThrowsException($region)
    {
        TimeZoneRegion::of($region);
    }

    /**
     * @return array
     */
    public function providerOfInvalidRegionThrowsException()
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

    public function testParse()
    {
        $this->assertSame('Europe/London', TimeZoneRegion::parse('Europe/London')->getId());
    }

    /**
     * @dataProvider providerParseInvalidStringThrowsException
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsException($text)
    {
        TimeZoneRegion::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            [''],
            ['Europe.London']
        ];
    }

    /**
     * @dataProvider providerGetOffset
     *
     * @param string  $region         The time-zone region.
     * @param integer $epochSecond    The instant to test.
     * @param integer $expectedOffset The expected offset in seconds.
     */
    public function testGetOffset($region, $epochSecond, $expectedOffset)
    {
        $actualOffset = TimeZoneRegion::of($region)->getOffset(Instant::of($epochSecond));
        $this->assertSame($expectedOffset, $actualOffset);
    }

    /**
     * @return array
     */
    public function providerGetOffset()
    {
        return [
            ['Europe/London', 1419984000,    0],
            ['Europe/Paris',  1419984000, 3600],
            ['Europe/London', 1406764800, 3600],
            ['Europe/Paris',  1406764800, 7200],
        ];
    }

    public function testToDateTimeZone()
    {
        $dateTimeZone = TimeZoneRegion::of('Europe/London')->toDateTimeZone();

        $this->assertInstanceOf(\DateTimeZone::class, $dateTimeZone);
        $this->assertSame('Europe/London', $dateTimeZone->getName());
    }

    public function testGetId()
    {
        $this->assertSame('Europe/Paris', TimeZoneRegion::of('Europe/Paris')->getId());
    }

    public function testToString()
    {
        $this->assertSame('America/Los_Angeles', (string) TimeZoneRegion::of('America/Los_Angeles'));
    }
}
