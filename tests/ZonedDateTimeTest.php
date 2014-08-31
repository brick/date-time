<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\ZonedDateTime;
use Brick\DateTime\TimeZone;

/**
 * Unit tests for class ZonedDateTime.
 */
class ZonedDateTimeTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOfInstant
     *
     * @param string $formattedDatetime
     * @param string $timeZone
     */
    public function testOfInstant($formattedDatetime, $timeZone)
    {
        $instant = Instant::of(1000000000);
        $zonedDateTime = ZonedDateTime::ofInstant($instant, TimeZone::parse($timeZone));

        $this->assertSame(1000000000, $zonedDateTime->getInstant()->getEpochSecond());
        $this->assertSame($formattedDatetime, (string) $zonedDateTime->getDateTime());
    }

    /**
     * @return array
     */
    public function providerOfInstant()
    {
        return [
            ['2001-09-09T01:46:40', 'UTC'],
            ['2001-09-08T18:46:40', 'America/Los_Angeles']
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
    public function testParse($text, $date, $time, $offset, $zone)
    {
        $zonedDateTime = ZonedDateTime::parse($text);

        $this->assertSame($date, (string) $zonedDateTime->getDate());
        $this->assertSame($time, (string) $zonedDateTime->getTime());
        $this->assertSame($offset, (string) $zonedDateTime->getTimeZoneOffset());
        $this->assertSame($zone, (string) $zonedDateTime->getTimeZone());
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['2001-02-03T01:02Z', '2001-02-03', '01:02', 'Z', 'Z'],
            ['2001-02-03T01:02:03Z', '2001-02-03', '01:02:03', 'Z', 'Z'],
            ['2001-02-03T01:02:03.456Z', '2001-02-03', '01:02:03.456', 'Z', 'Z'],
            ['2001-02-03T01:02-03:00', '2001-02-03', '01:02', '-03:00', '-03:00'],
            ['2001-02-03T01:02:03+04:00', '2001-02-03', '01:02:03', '+04:00', '+04:00'],
            ['2001-02-03T01:02:03.456+12:34:56', '2001-02-03', '01:02:03.456', '+12:34:56', '+12:34:56'],
            ['2001-02-03T01:02Z[Europe/London]', '2001-02-03', '01:02', 'Z', 'Europe/London'],
            ['2001-02-03T01:02+00:00[Europe/London]', '2001-02-03', '01:02', 'Z', 'Europe/London'],
            ['2001-02-03T01:02:03-00:00[Europe/London]', '2001-02-03', '01:02:03', 'Z', 'Europe/London'],
            ['2001-02-03T01:02:03.456+00:00[Europe/London]', '2001-02-03', '01:02:03.456', 'Z', 'Europe/London']
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
        ZonedDateTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            [''],
            ['2001'],
            ['2001-'],
            ['2001-02'],
            ['2001-02-'],
            ['2001-02-03'],
            ['2001-02-03T'],
            ['2001-02-03T04'],
            ['2001-02-03T04:'],
            ['2001-02-03T04:05'],
            ['2001-02-03T04:05:06'],
            ['2001-02-03T04:05:06.789'],
            ['2001-02-03T04:05Z[]'],
            ['2001-02-03T04:05[Europe/London]'],
            ['2001-02-03T04:05:06[Europe/London]'],
            ['2001-02-03T04:05.789Z[Europe/London]'],
            ['2001-02-03T04:05:06Z[Europe/London'],

            [' 2001-02-03T01:02:03Z'],
            ['2001-02-03T01:02:03Z ']
        ];
    }

    public function testCreateFromLocalDate()
    {
        $date = LocalDate::of(2012, 6, 30);
        $datetime = ZonedDateTime::createFromDate($date, TimeZone::parse('America/Los_Angeles'));
        $this->assertTrue($datetime->getDate()->isEqualTo($date));
        $this->assertSame(1341039600, $datetime->getInstant()->getEpochSecond());
    }

    public function testCreateFromDateAndTime()
    {
        $date = LocalDate::of(2012, 6, 30);
        $time = LocalTime::of(12, 34, 56);
        $datetime = ZonedDateTime::createFromDateAndTime($date, $time, TimeZone::parse('America/Los_Angeles'));
        $this->assertTrue($datetime->getDate()->isEqualTo($date));
        $this->assertTrue($datetime->getTime()->isEqualTo($time));
        $this->assertSame(1341084896, $datetime->getInstant()->getEpochSecond());
    }

    public function testChangeTimeZone()
    {
        $timezone1 = TimeZone::parse('UTC');
        $timezone2 = TimeZone::parse('America/Los_Angeles');

        $datetime1 = ZonedDateTime::ofInstant(Instant::of(1000000000), $timezone1);
        $datetime2 = $datetime1->withTimeZoneSameInstant($timezone2);

        $this->assertSame($timezone1, $datetime1->getTimezone());
        $this->assertSame($timezone2, $datetime2->getTimezone());
        $this->assertSame('2001-09-08T18:46:40', (string) $datetime2->getDateTime());

        $datetime2 = $datetime1->withTimeZoneSameLocal($timezone2);

        $this->assertSame($timezone1, $datetime1->getTimezone());
        $this->assertSame($timezone2, $datetime2->getTimezone());
        $this->assertSame('2001-09-09T01:46:40', (string) $datetime2->getDateTime());
    }
}
