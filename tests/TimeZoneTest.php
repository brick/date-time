<?php

namespace Brick\Tests\DateTime;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;

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
    public function testParse($text, $class, $id)
    {
        $timeZone = TimeZone::parse($text);

        $this->assertInstanceOf($class, $timeZone);
        $this->assertSame($id, $timeZone->getId());
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['Z', TimeZoneOffset::class, 'Z'],
            ['z', TimeZoneOffset::class, 'Z'],
            ['+01:00:00', TimeZoneOffset::class, '+01:00'],
            ['-02:30:30', TimeZoneOffset::class, '-02:30:30'],
            ['Europe/London', TimeZoneRegion::class, 'Europe/London'],
            ['America/Los_Angeles', TimeZoneRegion::class, 'America/Los_Angeles']
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
        TimeZone::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            [''],
            ['+'],
            ['-']
        ];
    }

    public function testUtc()
    {
        $this->assertTimeZoneOffsetEquals(0, TimeZone::utc());
    }

    public function testIsEqualTo()
    {
        $this->assertTrue(TimeZone::utc()->isEqualTo(TimeZoneOffset::ofTotalSeconds(0)));
        $this->assertFalse(TimeZone::utc()->isEqualTo(TimeZoneOffset::ofTotalSeconds(1)));
    }

    /**
     * @dataProvider providerFromDateTimeZone
     *
     * @param string $tz The time-zone name.
     */
    public function testFromDateTimeZone($tz)
    {
        $dateTimeZone = new \DateTimeZone($tz);
        $this->assertSame($tz, TimeZone::fromDateTimeZone($dateTimeZone)->getId());
    }

    /**
     * @return array
     */
    public function providerFromDateTimeZone()
    {
        return [
            ['Z'],
            ['+01:00'],
            ['Europe/London'],
            ['America/Los_Angeles']
        ];
    }
}
