<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Instant;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Period;
use Brick\DateTime\Duration;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\ZonedDateTime;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Clock\FixedClock;

/**
 * Unit tests for class ZonedDateTime.
 */
class ZonedDateTimeTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     *
     * @param string $localDateTime The local date-time as a string.
     * @param string $timeZone      The time-zone as a string.
     * @param string $offset        The expected time-zone offset of the result zoned date-time.
     * @param int    $shift         The expected shift applied to the date-time (when in a gap), in seconds.
     * @param int    $epochSecond   The expected epoch-second the result zoned date-time resolves to.
     * @param int    $nanoOfSecond  The expected nano-of-second of the result zoned date-time.
     */
    public function testOf(string $localDateTime, string $timeZone, string $offset, int $shift, int $epochSecond, int $nanoOfSecond)
    {
        $localDateTime = LocalDateTime::parse($localDateTime);
        $timeZone = TimeZone::parse($timeZone);
        $offset = TimeZoneOffset::parse($offset);

        $expectedDateTime = $localDateTime->plusSeconds($shift);

        $zonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $this->assertInstanceOf(ZonedDateTime::class, $zonedDateTime);

        $this->assertLocalDateTimeEquals($expectedDateTime, $zonedDateTime->getDateTime());
        $this->assertTimeZoneEquals($timeZone, $zonedDateTime->getTimeZone());
        $this->assertTimeZoneEquals($offset, $zonedDateTime->getTimeZoneOffset());

        $this->assertSame($epochSecond, $zonedDateTime->getEpochSecond());
        $this->assertSame($nanoOfSecond, $zonedDateTime->getNano());
    }

    /**
     * @return array
     */
    public function providerOf() : array
    {
        return [
            // WITH OFFSET FROM UTC
            // The date-time is resolved to an instant according to the offset without ambiguity.

            ['2000-01-01T12:34:56.123456789', '-18:00', '-18:00', 0, 946794896, 123456789],
            ['2001-02-02T12:34:56.123456789', '-17:00', '-17:00', 0, 981178496, 123456789],
            ['2002-03-03T12:34:56.123456789', '-16:00', '-16:00', 0, 1015216496, 123456789],
            ['2003-04-04T12:34:56.123456789', '-15:00', '-15:00', 0, 1049513696, 123456789],
            ['2004-05-05T12:34:56.123456789', '-14:00', '-14:00', 0, 1083810896, 123456789],
            ['2005-06-06T12:34:56.123456789', '-06:00', '-06:00', 0, 1118082896, 123456789],
            ['2006-07-07T12:34:56.123456789', '-05:00', '-05:00', 0, 1152293696, 123456789],
            ['2007-08-08T12:34:56.123456789', '-04:00', '-04:00', 0, 1186590896, 123456789],
            ['2008-09-09T12:34:56.123456789', '-03:00', '-03:00', 0, 1220974496, 123456789],
            ['2009-10-10T12:34:56.123456789', '-02:00', '-02:00', 0, 1255185296, 123456789],
            ['2010-11-11T12:34:56.123456789', '-01:30', '-01:30', 0, 1289484296, 123456789],
            ['2011-12-12T12:34:56.123456789', '-01:00', '-01:00', 0, 1323696896, 123456789],
            ['2012-01-13T12:34:56.123456789', '-00:45', '-00:45', 0, 1326460796, 123456789],
            ['2013-02-14T12:34:56.123456789', '-00:30', '-00:30', 0, 1360847096, 123456789],
            ['2014-03-15T12:34:56.123456789', '-00:15', '-00:15', 0, 1394887796, 123456789],
            ['2015-04-16T12:34:56.123456789', '+00:00', '+00:00', 0, 1429187696, 123456789],
            ['2016-05-17T12:34:56.123456789', '+00:15', '+00:15', 0, 1463487596, 123456789],
            ['2017-06-18T12:34:56.123456789', '+00:30', '+00:30', 0, 1497787496, 123456789],
            ['2018-07-19T12:34:56.123456789', '+00:45', '+00:45', 0, 1532000996, 123456789],
            ['2019-08-20T12:34:56.123456789', '+01:00', '+01:00', 0, 1566300896, 123456789],
            ['2011-09-21T12:34:56.123456789', '+01:30', '+01:30', 0, 1316603096, 123456789],
            ['2011-10-22T12:34:56.123456789', '+02:00', '+02:00', 0, 1319279696, 123456789],
            ['2011-11-23T12:34:56.123456789', '+03:00', '+03:00', 0, 1322040896, 123456789],
            ['2011-12-24T12:34:56.123456789', '+04:00', '+04:00', 0, 1324715696, 123456789],
            ['2011-01-25T12:34:56.123456789', '+05:00', '+05:00', 0, 1295940896, 123456789],
            ['2011-02-26T12:34:56.123456789', '+06:00', '+06:00', 0, 1298702096, 123456789],
            ['2011-03-27T12:34:56.123456789', '+14:00', '+14:00', 0, 1301178896, 123456789],
            ['2011-04-28T12:34:56.123456789', '+15:00', '+15:00', 0, 1303940096, 123456789],
            ['2011-05-29T12:34:56.123456789', '+16:00', '+16:00', 0, 1306614896, 123456789],
            ['2011-06-30T12:34:56.123456789', '+17:00', '+17:00', 0, 1309376096, 123456789],
            ['2011-07-31T12:34:56.123456789', '+18:00', '+18:00', 0, 1312050896, 123456789],

            // WITH REGION, NORMAL: NOT WITHIN A DST TRANSITION
            // The region is resolved to an offset without ambiguity.
            // The date-time is resolved to an instant without ambiguity.

            ['2001-06-01T12:34:56.123456789', 'Pacific/Niue',         '-11:00', 0,  991438496, 123456789],
            ['2002-06-02T12:34:56.123456789', 'Pacific/Honolulu',     '-10:00', 0, 1023057296, 123456789],
            ['2003-06-03T12:34:56.123456789', 'Pacific/Marquesas',    '-09:30', 0, 1054677896, 123456789],
            ['2004-12-04T12:34:56.123456789', 'America/Anchorage',    '-09:00', 0, 1102196096, 123456789],
            ['2005-06-05T12:34:56.123456789', 'America/Anchorage',    '-08:00', 0, 1118003696, 123456789],
            ['2006-06-06T12:34:56.123456789', 'America/Los_Angeles',  '-07:00', 0, 1149622496, 123456789],
            ['2007-12-07T12:34:56.123456789', 'America/Mexico_City',  '-06:00', 0, 1197052496, 123456789],
            ['2008-12-08T12:34:56.123456789', 'America/Nassau',       '-05:00', 0, 1228757696, 123456789],
            ['2009-01-09T12:34:56.123456789', 'America/Caracas',      '-04:30', 0, 1231520696, 123456789],
            ['2010-06-10T12:34:56.123456789', 'America/Nassau',       '-04:00', 0, 1276187696, 123456789],
            ['2011-12-11T12:34:56.123456789', 'America/St_Johns',     '-03:30', 0, 1323619496, 123456789],
            ['2012-06-12T12:34:56.123456789', 'America/Sao_Paulo',    '-03:00', 0, 1339515296, 123456789],
            ['2013-06-13T12:34:56.123456789', 'America/St_Johns',     '-02:30', 0, 1371135896, 123456789],
            ['2014-06-14T12:34:56.123456789', 'America/Noronha',      '-02:00', 0, 1402756496, 123456789],
            ['2015-06-15T12:34:56.123456789', 'Atlantic/Cape_Verde',  '-01:00', 0, 1434375296, 123456789],
            ['2016-12-16T12:34:56.123456789', 'Africa/Casablanca',    '+00:00', 0, 1481891696, 123456789],
            ['2017-06-17T12:34:56.123456789', 'Europe/London',        '+01:00', 0, 1497699296, 123456789],
            ['2019-06-19T12:34:56.123456789', 'Europe/Paris',         '+02:00', 0, 1560940496, 123456789],
            ['2020-06-20T12:34:56.123456789', 'Africa/Nairobi',       '+03:00', 0, 1592645696, 123456789],
            ['2001-12-21T12:34:56.123456789', 'Asia/Tehran',          '+03:30', 0, 1008925496, 123456789],
            ['2002-12-22T12:34:56.123456789', 'Asia/Yerevan',         '+04:00', 0, 1040546096, 123456789],
            ['2003-06-23T12:34:56.123456789', 'Asia/Tehran',          '+04:30', 0, 1056355496, 123456789],
            ['2004-06-24T12:34:56.123456789', 'Asia/Dushanbe',        '+05:00', 0, 1088062496, 123456789],
            ['2011-06-25T12:34:56.123456789', 'Asia/Colombo',         '+05:30', 0, 1308985496, 123456789],
            ['2006-06-26T12:34:56.123456789', 'Asia/Kathmandu',       '+05:45', 0, 1151304596, 123456789],
            ['2007-06-27T12:34:56.123456789', 'Asia/Dhaka',           '+06:00', 0, 1182926096, 123456789],
            ['2008-06-28T12:34:56.123456789', 'Asia/Rangoon',         '+06:30', 0, 1214633096, 123456789],
            ['2009-06-29T12:34:56.123456789', 'Asia/Bangkok',         '+07:00', 0, 1246253696, 123456789],
            ['2010-06-30T12:34:56.123456789', 'Asia/Hong_Kong',       '+08:00', 0, 1277872496, 123456789],
            ['2011-07-31T12:34:56.123456789', 'Australia/Eucla',      '+08:45', 0, 1312084196, 123456789],
            ['2012-07-01T12:34:56.123456789', 'Asia/Seoul',           '+09:00', 0, 1341113696, 123456789],
            ['2013-06-02T12:34:56.123456789', 'Australia/Adelaide',   '+09:30', 0, 1370142296, 123456789],
            ['2014-06-03T12:34:56.123456789', 'Pacific/Port_Moresby', '+10:00', 0, 1401762896, 123456789],
            ['2015-06-04T12:34:56.123456789', 'Australia/Lord_Howe',  '+10:30', 0, 1433383496, 123456789],
            ['2016-06-05T12:34:56.123456789', 'Pacific/Noumea',       '+11:00', 0, 1465090496, 123456789],
//            ['2017-06-06T12:34:56.123456789', 'Pacific/Norfolk',      '+11:30', 0, 1496711096, 123456789], // commented out for now: tzdb data differs in PHP 5.6 and PHP 7
            ['2018-06-07T12:34:56.123456789', 'Pacific/Auckland',     '+12:00', 0, 1528331696, 123456789],
            ['2019-06-08T12:34:56.123456789', 'Pacific/Chatham',      '+12:45', 0, 1559951396, 123456789],
            ['2020-06-09T12:34:56.123456789', 'Pacific/Apia',         '+13:00', 0, 1591659296, 123456789],
            ['2001-06-10T12:34:56.123456789', 'Pacific/Kiritimati',   '+14:00', 0,  992126096, 123456789],

            // WITH REGION, OVERLAP: SUMMER TO WINTER DST TRANSITION
            // If the date-time falls within the overlap (inclusive of the start time and exclusive of the end time),
            // the region can be resolved to two different offsets; the offset closest to UTC is chosen.
            // The date-time is resolved to an instant according to the resolved offset.

            // America/Juneau: at 2:00 AM (-08:00) clocks are set backward 1 hour, to 1:00 AM (-09:00)
            // Date-times falling between 1:00 AM and 2:00 AM on the transition day are resolved to -08:00

            ['2014-11-02T00:59:59.999999999', 'America/Juneau', '-08:00', 0, 1414918799, 999999999],
            ['2014-11-02T01:00:00.000000000', 'America/Juneau', '-08:00', 0, 1414918800,         0],
            ['2014-11-02T01:59:59.999999999', 'America/Juneau', '-08:00', 0, 1414922399, 999999999],
            ['2014-11-02T02:00:00.000000000', 'America/Juneau', '-09:00', 0, 1414926000,         0],
            ['2014-11-02T02:59:59.999999999', 'America/Juneau', '-09:00', 0, 1414929599, 999999999],

            // America/Chicago: at 2:00 AM (-05:00) clocks are set backward 1 hour, to 1:00 AM (-06:00)
            // Date-times falling between 1:00 AM and 2:00 AM on the transition day are resolved to -05:00

            ['2013-11-03T00:59:59.999999999', 'America/Chicago', '-05:00', 0, 1383458399, 999999999],
            ['2013-11-03T01:00:00.000000000', 'America/Chicago', '-05:00', 0, 1383458400,         0],
            ['2013-11-03T01:59:59.999999999', 'America/Chicago', '-05:00', 0, 1383461999, 999999999],
            ['2013-11-03T02:00:00.000000000', 'America/Chicago', '-06:00', 0, 1383465600,         0],
            ['2013-11-03T02:59:59.999999999', 'America/Chicago', '-06:00', 0, 1383469199, 999999999],

            ['2014-11-02T00:59:59.999999999', 'America/Chicago', '-05:00', 0, 1414907999, 999999999],
            ['2014-11-02T01:00:00.000000000', 'America/Chicago', '-05:00', 0, 1414908000,         0],
            ['2014-11-02T01:59:59.999999999', 'America/Chicago', '-05:00', 0, 1414911599, 999999999],
            ['2014-11-02T02:00:00.000000000', 'America/Chicago', '-06:00', 0, 1414915200,         0],
            ['2014-11-02T02:59:59.999999999', 'America/Chicago', '-06:00', 0, 1414918799, 999999999],

            // America/Miquelon: at 2:00 AM (-02:00) clocks are set backward 1 hour, to 1:00 AM  (-03:00)
            // Date-times falling between 1:00 AM and 2:00 AM on the transition day are resolved to -02:00

            ['2013-11-03T00:59:59.999999999', 'America/Miquelon', '-02:00', 0, 1383447599, 999999999],
            ['2013-11-03T01:00:00.000000000', 'America/Miquelon', '-02:00', 0, 1383447600,         0],
            ['2013-11-03T01:59:59.999999999', 'America/Miquelon', '-02:00', 0, 1383451199, 999999999],
            ['2013-11-03T02:00:00.000000000', 'America/Miquelon', '-03:00', 0, 1383454800,         0],
            ['2013-11-03T02:59:59.999999999', 'America/Miquelon', '-03:00', 0, 1383458399, 999999999],

            ['2014-11-02T00:59:59.999999999', 'America/Miquelon', '-02:00', 0, 1414897199, 999999999],
            ['2014-11-02T01:00:00.000000000', 'America/Miquelon', '-02:00', 0, 1414897200,         0],
            ['2014-11-02T01:59:59.999999999', 'America/Miquelon', '-02:00', 0, 1414900799, 999999999],
            ['2014-11-02T02:00:00.000000000', 'America/Miquelon', '-03:00', 0, 1414904400,         0],
            ['2014-11-02T02:59:59.999999999', 'America/Miquelon', '-03:00', 0, 1414907999, 999999999],

            // Europe/Paris: at 3:00 AM (+02:00) clocks are set backward 1 hour, to 2:00 AM (+01:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are resolved to +01:00

            ['2013-10-27T01:00:00.000000000', 'Europe/Paris', '+02:00', 0, 1382828400,         0],
            ['2013-10-27T01:59:59.999999999', 'Europe/Paris', '+02:00', 0, 1382831999, 999999999],
            ['2013-10-27T02:00:00.000000000', 'Europe/Paris', '+01:00', 0, 1382835600,         0],
            ['2013-10-27T02:59:59.999999999', 'Europe/Paris', '+01:00', 0, 1382839199, 999999999],
            ['2013-10-27T03:00:00.000000000', 'Europe/Paris', '+01:00', 0, 1382839200,         0],

            ['2014-10-26T01:00:00.000000000', 'Europe/Paris', '+02:00', 0, 1414278000,         0],
            ['2014-10-26T01:59:59.999999999', 'Europe/Paris', '+02:00', 0, 1414281599, 999999999],
            ['2014-10-26T02:00:00.000000000', 'Europe/Paris', '+01:00', 0, 1414285200,         0],
            ['2014-10-26T02:59:59.999999999', 'Europe/Paris', '+01:00', 0, 1414288799, 999999999],
            ['2014-10-26T03:00:00.000000000', 'Europe/Paris', '+01:00', 0, 1414288800,         0],

            // Europe/Athens: at 4:00 AM (+03:00) clocks are set backward 1 hour, to 3:00 AM (+02:00)
            // Date-times falling between 3:00 AM nd 4:00 AM on the transition day are resolved to +02:00

            ['2013-10-27T02:00:00.000000000', 'Europe/Athens', '+03:00', 0, 1382828400,         0],
            ['2013-10-27T02:59:59.999999999', 'Europe/Athens', '+03:00', 0, 1382831999, 999999999],
            ['2013-10-27T03:00:00.000000000', 'Europe/Athens', '+02:00', 0, 1382835600,         0],
            ['2013-10-27T03:59:59.999999999', 'Europe/Athens', '+02:00', 0, 1382839199, 999999999],
            ['2013-10-27T04:00:00.000000000', 'Europe/Athens', '+02:00', 0, 1382839200,         0],

            ['2014-10-26T02:00:00.000000000', 'Europe/Athens', '+03:00', 0, 1414278000,         0],
            ['2014-10-26T02:59:59.999999999', 'Europe/Athens', '+03:00', 0, 1414281599, 999999999],
            ['2014-10-26T03:00:00.000000000', 'Europe/Athens', '+02:00', 0, 1414285200,         0],
            ['2014-10-26T03:59:59.999999999', 'Europe/Athens', '+02:00', 0, 1414288799, 999999999],
            ['2014-10-26T04:00:00.000000000', 'Europe/Athens', '+02:00', 0, 1414288800,         0],

            // Australia/Sydney: at 3:00 AM (+11:00) clocks are set backward 1 hour, to 2:00 AM (+10:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are resolved to +10:00

            ['2013-04-07T01:00:00.000000000', 'Australia/Sydney', '+11:00', 0, 1365256800,         0],
            ['2013-04-07T01:59:59.999999999', 'Australia/Sydney', '+11:00', 0, 1365260399, 999999999],
            ['2013-04-07T02:00:00.000000000', 'Australia/Sydney', '+10:00', 0, 1365264000,         0],
            ['2013-04-07T02:59:59.999999999', 'Australia/Sydney', '+10:00', 0, 1365267599, 999999999],
            ['2013-04-07T03:00:00.000000000', 'Australia/Sydney', '+10:00', 0, 1365267600,         0],
            ['2013-04-07T03:59:59.999999999', 'Australia/Sydney', '+10:00', 0, 1365271199, 999999999],
            ['2013-04-07T04:00:00.000000000', 'Australia/Sydney', '+10:00', 0, 1365271200,         0],

            ['2014-04-06T01:00:00.000000000', 'Australia/Sydney', '+11:00', 0, 1396706400,         0],
            ['2014-04-06T01:59:59.999999999', 'Australia/Sydney', '+11:00', 0, 1396709999, 999999999],
            ['2014-04-06T02:00:00.000000000', 'Australia/Sydney', '+10:00', 0, 1396713600,         0],
            ['2014-04-06T02:59:59.999999999', 'Australia/Sydney', '+10:00', 0, 1396717199, 999999999],
            ['2014-04-06T03:00:00.000000000', 'Australia/Sydney', '+10:00', 0, 1396717200,         0],
            ['2014-04-06T03:59:59.999999999', 'Australia/Sydney', '+10:00', 0, 1396720799, 999999999],
            ['2014-04-06T04:00:00.000000000', 'Australia/Sydney', '+10:00', 0, 1396720800,         0],

            // WITH REGION, GAP: WINTER TO SUMMER DST TRANSITION
            // If the date-time falls within the gap (inclusive of the start time and exclusive of the end time),
            // the date-time is shifted forward by the length of the gap, and the region is resolved to
            // the later offset (summer time).

            // America/Juneau: at 2:00 AM (-09:00) clocks are set forward 1 hour, to 3:00 AM (-08:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are shifted forward 1 hour

            ['2014-03-09T01:59:59.999999999', 'America/Juneau', '-09:00',    0, 1394362799, 999999999],
            ['2014-03-09T02:00:00.000000000', 'America/Juneau', '-08:00', 3600, 1394362800,         0],
            ['2014-03-09T02:59:59.999999999', 'America/Juneau', '-08:00', 3600, 1394366399, 999999999],
            ['2014-03-09T03:00:00.000000000', 'America/Juneau', '-08:00',    0, 1394362800,         0],
            ['2014-03-09T03:59:59.999999999', 'America/Juneau', '-08:00',    0, 1394366399, 999999999],

            // America/Chicago: at 2:00 AM (-06:00) clocks are set forward 1 hour, to 3:00 AM (-05:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are shifted forward 1 hour

            ['2013-03-10T01:59:59.999999999', 'America/Chicago', '-06:00',    0, 1362902399, 999999999],
            ['2013-03-10T02:00:00.000000000', 'America/Chicago', '-05:00', 3600, 1362902400,         0],
            ['2013-03-10T02:59:59.999999999', 'America/Chicago', '-05:00', 3600, 1362905999, 999999999],
            ['2013-03-10T03:00:00.000000000', 'America/Chicago', '-05:00',    0, 1362902400,         0],
            ['2013-03-10T03:59:59.999999999', 'America/Chicago', '-05:00',    0, 1362905999, 999999999],

            ['2014-03-09T01:59:59.999999999', 'America/Chicago', '-06:00',    0, 1394351999, 999999999],
            ['2014-03-09T02:00:00.000000000', 'America/Chicago', '-05:00', 3600, 1394352000,         0],
            ['2014-03-09T02:59:59.999999999', 'America/Chicago', '-05:00', 3600, 1394355599, 999999999],
            ['2014-03-09T03:00:00.000000000', 'America/Chicago', '-05:00',    0, 1394352000,         0],
            ['2014-03-09T03:59:59.999999999', 'America/Chicago', '-05:00',    0, 1394355599, 999999999],

            // America/Miquelon: at 2:00 AM (-03:00) clocks are set forward 1 hour, to 3:00 AM (-02:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are shifted forward 1 hour

            ['2013-03-10T01:59:59.999999999', 'America/Miquelon', '-03:00',    0, 1362891599, 999999999],
            ['2013-03-10T02:00:00.000000000', 'America/Miquelon', '-02:00', 3600, 1362891600,         0],
            ['2013-03-10T02:59:59.999999999', 'America/Miquelon', '-02:00', 3600, 1362895199, 999999999],
            ['2013-03-10T03:00:00.000000000', 'America/Miquelon', '-02:00',    0, 1362891600,         0],
            ['2013-03-10T03:59:59.999999999', 'America/Miquelon', '-02:00',    0, 1362895199, 999999999],

            ['2014-03-09T01:59:59.999999999', 'America/Miquelon', '-03:00',    0, 1394341199, 999999999],
            ['2014-03-09T02:00:00.000000000', 'America/Miquelon', '-02:00', 3600, 1394341200,         0],
            ['2014-03-09T02:59:59.999999999', 'America/Miquelon', '-02:00', 3600, 1394344799, 999999999],
            ['2014-03-09T03:00:00.000000000', 'America/Miquelon', '-02:00',    0, 1394341200,         0],
            ['2014-03-09T03:59:59.999999999', 'America/Miquelon', '-02:00',    0, 1394344799, 999999999],

            // Europe/Paris: at 2:00 AM (+01:00) clocks are set forward 1 hour, to 3:00 AM (+02:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are shifted forward 1 hour

            ['2013-03-31T01:59:59.999999999', 'Europe/Paris', '+01:00',    0, 1364691599, 999999999],
            ['2013-03-31T02:00:00.000000000', 'Europe/Paris', '+02:00', 3600, 1364691600,         0],
            ['2013-03-31T02:59:59.999999999', 'Europe/Paris', '+02:00', 3600, 1364695199, 999999999],
            ['2013-03-31T03:00:00.000000000', 'Europe/Paris', '+02:00',    0, 1364691600,         0],
            ['2013-03-31T03:59:59.999999999', 'Europe/Paris', '+02:00',    0, 1364695199, 999999999],

            ['2014-03-30T01:59:59.999999999', 'Europe/Paris', '+01:00',    0, 1396141199, 999999999],
            ['2014-03-30T02:00:00.000000000', 'Europe/Paris', '+02:00', 3600, 1396141200,         0],
            ['2014-03-30T02:59:59.999999999', 'Europe/Paris', '+02:00', 3600, 1396144799, 999999999],
            ['2014-03-30T03:00:00.000000000', 'Europe/Paris', '+02:00',    0, 1396141200,         0],
            ['2014-03-30T03:59:59.999999999', 'Europe/Paris', '+02:00',    0, 1396144799, 999999999],

            // Europe/Athens: at 3:00 AM (+02:00) clocks are set forward 1 hour, to 4:00 AM (+03:00)
            // Date-times falling between 3:00 AM and 4:00 AM on the transition day are shifted forward 1 hour

            ['2013-03-31T02:59:59.999999999', 'Europe/Athens', '+02:00',    0, 1364691599, 999999999],
            ['2013-03-31T03:00:00.000000000', 'Europe/Athens', '+03:00', 3600, 1364691600,         0],
            ['2013-03-31T03:59:59.999999999', 'Europe/Athens', '+03:00', 3600, 1364695199, 999999999],
            ['2013-03-31T04:00:00.000000000', 'Europe/Athens', '+03:00',    0, 1364691600,         0],
            ['2013-03-31T04:59:59.999999999', 'Europe/Athens', '+03:00',    0, 1364695199, 999999999],

            ['2014-03-30T02:59:59.999999999', 'Europe/Athens', '+02:00',    0, 1396141199, 999999999],
            ['2014-03-30T03:00:00.000000000', 'Europe/Athens', '+03:00', 3600, 1396141200,         0],
            ['2014-03-30T03:59:59.999999999', 'Europe/Athens', '+03:00', 3600, 1396144799, 999999999],
            ['2014-03-30T04:00:00.000000000', 'Europe/Athens', '+03:00',    0, 1396141200,         0],
            ['2014-03-30T04:59:59.999999999', 'Europe/Athens', '+03:00',    0, 1396144799, 999999999],

            // Australia/Sydney: at 2:00 AM (+10:00) clocks are set forward 1 hour, to 3:00 AM (+11:00)
            // Date-times falling between 2:00 AM and 3:00 AM on the transition day are shifted forward 1 hour

            ['2013-10-06T01:59:59.999999999', 'Australia/Sydney', '+10:00',    0, 1380988799, 999999999],
            ['2013-10-06T02:00:00.000000000', 'Australia/Sydney', '+11:00', 3600, 1380988800,         0],
            ['2013-10-06T02:59:59.999999999', 'Australia/Sydney', '+11:00', 3600, 1380992399, 999999999],
            ['2013-10-06T03:00:00.000000000', 'Australia/Sydney', '+11:00',    0, 1380988800,         0],
            ['2013-10-06T03:59:59.999999999', 'Australia/Sydney', '+11:00',    0, 1380992399, 999999999],

            ['2014-10-05T01:59:59.999999999', 'Australia/Sydney', '+10:00',    0, 1412438399, 999999999],
            ['2014-10-05T02:00:00.000000000', 'Australia/Sydney', '+11:00', 3600, 1412438400,         0],
            ['2014-10-05T02:59:59.999999999', 'Australia/Sydney', '+11:00', 3600, 1412441999, 999999999],
            ['2014-10-05T03:00:00.000000000', 'Australia/Sydney', '+11:00',    0, 1412438400,         0],
            ['2014-10-05T03:59:59.999999999', 'Australia/Sydney', '+11:00',    0, 1412441999, 999999999],
        ];
    }

    /**
     * @dataProvider providerOfInstant
     *
     * @param string $formattedDatetime
     * @param string $timeZone
     */
    public function testOfInstant(string $formattedDatetime, string $timeZone)
    {
        $instant = Instant::of(1000000000);
        $zonedDateTime = ZonedDateTime::ofInstant($instant, TimeZone::parse($timeZone));

        $this->assertSame(1000000000, $zonedDateTime->getInstant()->getEpochSecond());
        $this->assertSame($formattedDatetime, (string) $zonedDateTime->getDateTime());
    }

    /**
     * @return array
     */
    public function providerOfInstant() : array
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
    public function testParse(string $text, string $date, string $time, string $offset, string $zone)
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
    public function providerParse() : array
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
    public function testParseInvalidStringThrowsException(string $text)
    {
        ZonedDateTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException() : array
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

    /**
     * @dataProvider providerFromDateTime
     *
     * @param string $dateTimeString
     * @param string $timeZone
     * @param string $expected
     */
    public function testFromDateTime(string $dateTimeString, string $timeZone, string $expected)
    {
        $dateTime = new \DateTime($dateTimeString, new \DateTimeZone($timeZone));
        $this->assertIs(ZonedDateTime::class, $expected, ZonedDateTime::fromDateTime($dateTime));
    }

    /**
     * @return array
     */
    public function providerFromDateTime() : array
    {
        return [
            ['2018-07-21 14:09:10.23456', 'America/Los_Angeles', '2018-07-21T14:09:10.23456-07:00[America/Los_Angeles]'],
            ['2019-01-21 17:59', 'America/Los_Angeles', '2019-01-21T17:59-08:00[America/Los_Angeles]'],
            ['2019-01-23 09:10:11.123', '+05:30', '2019-01-23T09:10:11.123+05:30'],
        ];
    }

    public function testChangeTimeZone()
    {
        $timezone1 = TimeZone::parse('UTC');
        $timezone2 = TimeZone::parse('America/Los_Angeles');

        $datetime1 = ZonedDateTime::ofInstant(Instant::of(1000000000), $timezone1);
        $datetime2 = $datetime1->withTimeZoneSameInstant($timezone2);

        $this->assertSame($timezone1, $datetime1->getTimeZone());
        $this->assertSame($timezone2, $datetime2->getTimeZone());
        $this->assertSame('2001-09-08T18:46:40', (string) $datetime2->getDateTime());

        $datetime2 = $datetime1->withTimeZoneSameLocal($timezone2);

        $this->assertSame($timezone1, $datetime1->getTimeZone());
        $this->assertSame($timezone2, $datetime2->getTimeZone());
        $this->assertSame('2001-09-09T01:46:40', (string) $datetime2->getDateTime());
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param string $z1  The first zoned date-time.
     * @param string $z2  The second zoned date-time.
     * @param int    $cmp The comparison value.
     */
    public function testCompareTo(string $z1, string $z2, int $cmp)
    {
        $z1 = ZonedDateTime::parse($z1);
        $z2 = ZonedDateTime::parse($z2);

        $this->assertSame($cmp, $z1->compareTo($z2));
        $this->assertSame($cmp === 0, $z1->isEqualTo($z2));
        $this->assertSame($cmp === -1, $z1->isBefore($z2));
        $this->assertSame($cmp === 1, $z1->isAfter($z2));
        $this->assertSame($cmp <= 0, $z1->isBeforeOrEqualTo($z2));
        $this->assertSame($cmp >= 0, $z1->isAfterOrEqualTo($z2));

        $this->assertSame(-$cmp, $z2->compareTo($z1));
        $this->assertSame($cmp === 0, $z2->isEqualTo($z1));
        $this->assertSame($cmp === 1, $z2->isBefore($z1));
        $this->assertSame($cmp === -1, $z2->isAfter($z1));
        $this->assertSame($cmp >= 0, $z2->isBeforeOrEqualTo($z1));
        $this->assertSame($cmp <= 0, $z2->isAfterOrEqualTo($z1));
    }

    /**
     * @return array
     */
    public function providerCompareTo() : array
    {
        return [
            ['2020-06-06T14:30:30Z', '2014-12-31T23:59:59.999Z', 1],
            ['2020-06-06T14:30:30Z', '2020-06-06T14:30:30+00:00', 0],
            ['2020-06-06T14:30:30Z', '2020-06-06T14:29:29.999999999+00:00', 1],
            ['2020-06-06T14:30:30Z', '2020-06-06T14:30:30.000000000+00:00', 0],
            ['2020-06-06T14:30:30Z', '2020-06-06T14:30:30.000000001+00:00', -1],
            ['2020-06-06T14:30:30Z', '2020-06-06T15:30:30+01:00', 0],
            ['2020-06-06T14:30:30Z', '2020-06-06T16:00:30+01:30', 0],
            ['2020-06-06T14:30:30Z', '2020-06-06T15:30:30+02:00', 1],
            ['2020-06-06T14:30:30Z', '2020-06-06T13:30:30-02:00', -1],
        ];
    }

    /**
     * @return ZonedDateTime
     */
    private function getTestZonedDateTime() : ZonedDateTime
    {
        $timeZone = TimeZone::parse('America/Los_Angeles');
        $localDateTime = LocalDateTime::parse('2000-01-20T12:34:56.123456789');

        return ZonedDateTime::of($localDateTime, $timeZone);
    }

    public function testGetYear()
    {
        $this->assertSame(2000, $this->getTestZonedDateTime()->getYear());
    }

    public function testGetMonth()
    {
        $this->assertSame(1, $this->getTestZonedDateTime()->getMonth());
    }

    public function testGetDay()
    {
        $this->assertSame(20, $this->getTestZonedDateTime()->getDay());
    }

    public function testGetDayOfWeek()
    {
        $this->assertDayOfWeekIs(DayOfWeek::THURSDAY, $this->getTestZonedDateTime()->getDayOfWeek());
    }

    public function testGetDayOfYear()
    {
        $this->assertSame(20, $this->getTestZonedDateTime()->getDayOfYear());
    }

    public function testGetHour()
    {
        $this->assertSame(12, $this->getTestZonedDateTime()->getHour());
    }

    public function testGetMinute()
    {
        $this->assertSame(34, $this->getTestZonedDateTime()->getMinute());
    }

    public function testGetSecond()
    {
        $this->assertSame(56, $this->getTestZonedDateTime()->getSecond());
    }

    public function testWithDate()
    {
        $newDate = LocalDate::of(2000, 1, 22);

        $this->assertIs(ZonedDateTime::class, '2000-01-22T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withDate($newDate));
    }

    public function testWithTime()
    {
        $time = LocalTime::of(1, 2, 3, 987654321);

        $this->assertIs(ZonedDateTime::class, '2000-01-20T01:02:03.987654321-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withTime($time));
    }

    public function testWithYear()
    {
        $this->assertIs(ZonedDateTime::class, '2020-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withYear(2020));
    }

    public function testWithMonth()
    {
        $this->assertIs(ZonedDateTime::class, '2000-07-20T12:34:56.123456789-07:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withMonth(7));
    }

    public function testWithDay()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-31T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withDay(31));
    }

    public function testWithHour()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T23:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withHour(23));
    }

    public function testWithMinute()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:00:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withMinute(0));
    }

    public function testWithSecond()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:34:06.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withSecond(6));
    }

    public function testWithNano()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:34:56.000000123-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withNano(123));
    }

    public function testWithFixedOffsetTimeZone()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:34:56.123456789-08:00', $this->getTestZonedDateTime()->withFixedOffsetTimeZone());
    }

    public function testPlusPeriod()
    {
        $this->assertIs(ZonedDateTime::class, '2000-04-06T12:34:56.123456789-07:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusPeriod(Period::ofWeeks(11)));
    }

    public function testPlusDuration()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:35:01.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusDuration(Duration::ofSeconds(5)));
    }

    public function testPlusYears()
    {
        $this->assertIs(ZonedDateTime::class, '2002-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusYears(2));
    }

    public function testPlusMonths()
    {
        $this->assertIs(ZonedDateTime::class, '2000-03-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusMonths(2));
    }

    public function testPlusWeeks()
    {
        $this->assertIs(ZonedDateTime::class, '2000-02-03T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusWeeks(2));
    }

    public function testPlusDays()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-22T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusDays(2));
    }

    public function testPlusHours()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T14:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusHours(2));
    }

    public function testPlusMinutes()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:36:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusMinutes(2));
    }

    public function testPlusSeconds()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:34:58.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusSeconds(2));
    }

    public function testMinusPeriod()
    {
        $this->assertIs(ZonedDateTime::class, '1999-11-04T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusPeriod(Period::ofWeeks(11)));
    }

    public function testMinusDuration()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:34:51.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusDuration(Duration::ofSeconds(5)));
    }

    public function testMinusYears()
    {
        $this->assertIs(ZonedDateTime::class, '1999-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusYears(1));
    }

    public function testMinusMonths()
    {
        $this->assertIs(ZonedDateTime::class, '1999-12-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusMonths(1));
    }

    public function testMinusWeeks()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-06T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusWeeks(2));
    }

    public function testMinusDays()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-18T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusDays(2));
    }

    public function testMinusHours()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T10:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusHours(2));
    }

    public function testMinusMinutes()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:32:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusMinutes(2));
    }

    public function testMinusSeconds()
    {
        $this->assertIs(ZonedDateTime::class, '2000-01-20T12:34:54.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusSeconds(2));
    }

    public function testIsBetweenInclusive()
    {
        $timeZone = TimeZone::parse('America/Los_Angeles');
        $localDateTime = '2000-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $fromZonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $localDateTime = '2015-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $toZonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $localDateTime = '2018-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $notIncluZonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $this->assertTrue($fromZonedDateTime->isBetweenInclusive($fromZonedDateTime, $toZonedDateTime));
        $this->assertFalse($fromZonedDateTime->isBetweenInclusive($toZonedDateTime, $notIncluZonedDateTime));
    }

    public function testIsBetweenExclusive()
    {
        $timeZone = TimeZone::parse('America/Los_Angeles');
        $localDateTime = '2000-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $fromZonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $localDateTime = '2015-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $toZonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $localDateTime = '2014-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $incluZonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $this->assertTrue($incluZonedDateTime->isBetweenExclusive($fromZonedDateTime, $toZonedDateTime));
        $this->assertFalse($fromZonedDateTime->isBetweenExclusive($fromZonedDateTime, $toZonedDateTime));
    }

    /**
     * @dataProvider providerForPastFuture
     */
    public function testIsFuture($clockTimestamp, $zonedDateTime, $isFuture)
    {
        $clock = new FixedClock(Instant::of($clockTimestamp));
        $zonedDateTime = ZonedDateTime::parse($zonedDateTime);
        $this->assertSame($isFuture, $zonedDateTime->isFuture($clock));
    }

    /**
     * @dataProvider providerForPastFuture
     */
    public function testIsPast($clockTimestamp, $zonedDateTime, $isFuture)
    {
        $clock = new FixedClock(Instant::of($clockTimestamp));
        $zonedDateTime = ZonedDateTime::parse($zonedDateTime);
        $this->assertSame(! $isFuture, $zonedDateTime->isPast($clock));
    }

    /**
     * @return array
     */
    public function providerForPastFuture()
    {
        return [
            [1234567890, '2009-02-14T00:31:29+01:00', false],
            [1234567890, '2009-02-14T00:31:31+01:00', true],
            [2345678901, '2044-04-30T17:28:20-08:00', false],
            [2345678901, '2044-04-30T17:28:22-08:00', true]
        ];
    }

    /**
     * @dataProvider providerToDateTime
     *
     * @param string $dateTime The date-time string that will be parse()d by ZonedDateTime.
     * @param string $expected The expected output from the native DateTime object.
     */
    public function testToDateTime(string $dateTime, string $expected)
    {
        $zonedDateTime = ZonedDateTime::parse($dateTime);
        $dateTime = $zonedDateTime->toDateTime();

        $this->assertInstanceOf(\DateTime::class, $dateTime);
        $this->assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    /**
     * @dataProvider providerToDateTime
     *
     * @param string $dateTime The date-time string that will be parse()d by ZonedDateTime.
     * @param string $expected The expected output from the native DateTime object.
     */
    public function testToDateTimeImmutable(string $dateTime, string $expected)
    {
        $zonedDateTime = ZonedDateTime::parse($dateTime);
        $dateTime = $zonedDateTime->toDateTimeImmutable();

        $this->assertInstanceOf(\DateTimeImmutable::class, $dateTime);
        $this->assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    /**
     * @return array
     */
    public function providerToDateTime()
    {
        return [
            ['2018-10-18T12:34Z',                        '2018-10-18T12:34:00.000000+0000'],
            ['2018-10-18T12:34:56Z',                     '2018-10-18T12:34:56.000000+0000'],
            ['2018-10-18T12:34:00.001Z',                 '2018-10-18T12:34:00.001000+0000'],
            ['2018-10-18T12:34:56.123002Z',              '2018-10-18T12:34:56.123002+0000'],
            ['2011-07-31T23:59:59+01:00',                '2011-07-31T23:59:59.000000+0100'],
            ['2011-07-31T23:59:59.02-05:30',             '2011-07-31T23:59:59.020000-0530'],
            ['2011-07-31T23:59:59+01:00[Europe/London]', '2011-07-31T23:59:59.000000+0100'],
            ['2011-07-31T23:59:59.000123456-07:00',      '2011-07-31T23:59:59.000123-0700'],
        ];
    }

    public function testToString()
    {
        $timeZone = TimeZone::parse('America/Los_Angeles');
        $localDateTime = '2000-01-20T12:34:56.123456789';
        $localDateTime = LocalDateTime::parse($localDateTime);
        $zonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        $this->assertSame('2000-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', (string) $zonedDateTime);
    }
}
