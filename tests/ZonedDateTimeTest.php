<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Month;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Period;
use Brick\DateTime\TimeZone;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\ZonedDateTime;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;

use function json_encode;

use const JSON_THROW_ON_ERROR;
use const PHP_VERSION_ID;

/**
 * Unit tests for class ZonedDateTime.
 */
class ZonedDateTimeTest extends AbstractTestCase
{
    /**
     * @param string $localDateTime The local date-time as a string.
     * @param string $timeZone      The time-zone as a string.
     * @param string $offset        The expected time-zone offset of the result zoned date-time.
     * @param int    $shift         The expected shift applied to the date-time (when in a gap), in seconds.
     * @param int    $epochSecond   The expected epoch-second the result zoned date-time resolves to.
     * @param int    $nanoOfSecond  The expected nano-of-second of the result zoned date-time.
     */
    #[DataProvider('providerOf')]
    public function testOf(string $localDateTime, string $timeZone, string $offset, int $shift, int $epochSecond, int $nanoOfSecond): void
    {
        $localDateTime = LocalDateTime::parse($localDateTime);
        $timeZone = TimeZone::parse($timeZone);
        $offset = TimeZoneOffset::parse($offset);

        $expectedDateTime = $localDateTime->plusSeconds($shift);

        $zonedDateTime = ZonedDateTime::of($localDateTime, $timeZone);

        self::assertInstanceOf(ZonedDateTime::class, $zonedDateTime);

        self::assertLocalDateTimeEquals($expectedDateTime, $zonedDateTime->getDateTime());
        self::assertTimeZoneEquals($timeZone, $zonedDateTime->getTimeZone());
        self::assertTimeZoneEquals($offset, $zonedDateTime->getTimeZoneOffset());

        self::assertSame($epochSecond, $zonedDateTime->getEpochSecond());
        self::assertSame($nanoOfSecond, $zonedDateTime->getNano());
    }

    public static function providerOf(): array
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
            ['2017-06-06T12:34:56.123456789', 'Pacific/Norfolk',      '+11:00', 0, 1496712896, 123456789],
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

    #[DataProvider('providerOfInstant')]
    public function testOfInstant(string $formattedDatetime, string $timeZone): void
    {
        $instant = Instant::of(1000000000);
        $zonedDateTime = ZonedDateTime::ofInstant($instant, TimeZone::parse($timeZone));

        self::assertSame(1000000000, $zonedDateTime->getInstant()->getEpochSecond());
        self::assertSame($formattedDatetime, (string) $zonedDateTime->getDateTime());
    }

    public static function providerOfInstant(): array
    {
        return [
            ['2001-09-09T01:46:40', 'UTC'],
            ['2001-09-08T18:46:40', 'America/Los_Angeles'],
        ];
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
        $zonedDateTime = ZonedDateTime::parse($text);

        self::assertSame($date, (string) $zonedDateTime->getDate());
        self::assertSame($time, (string) $zonedDateTime->getTime());
        self::assertSame($offset, (string) $zonedDateTime->getTimeZoneOffset());
        self::assertSame($zone, (string) $zonedDateTime->getTimeZone());
    }

    public static function providerParse(): iterable
    {
        yield from [
            ['2001-02-03T01:02Z', '2001-02-03', '01:02', 'Z', 'Z'],
            ['2001-02-03T01:02:03Z', '2001-02-03', '01:02:03', 'Z', 'Z'],
            ['2001-02-03T01:02:03.456Z', '2001-02-03', '01:02:03.456', 'Z', 'Z'],
            ['2001-02-03T01:02-03:00', '2001-02-03', '01:02', '-03:00', '-03:00'],
            ['2001-02-03T01:02:03+04:00', '2001-02-03', '01:02:03', '+04:00', '+04:00'],
            ['2001-02-03T01:02:03.456+12:34', '2001-02-03', '01:02:03.456', '+12:34', '+12:34'],
            ['2001-02-03T01:02Z[Europe/London]', '2001-02-03', '01:02', 'Z', 'Europe/London'],
            ['2001-02-03T01:02+00:00[Europe/London]', '2001-02-03', '01:02', 'Z', 'Europe/London'],
            ['2001-02-03T01:02:03-00:00[Europe/London]', '2001-02-03', '01:02:03', 'Z', 'Europe/London'],
            ['2001-02-03T01:02:03.456+00:00[Europe/London]', '2001-02-03', '01:02:03.456', 'Z', 'Europe/London'],
        ];

        if (PHP_VERSION_ID >= 80107) {
            yield ['2001-02-03T01:02:03.456+12:34:56', '2001-02-03', '01:02:03.456', '+12:34:56', '+12:34:56'];
        }
    }

    #[DataProvider('providerParseInvalidStringThrowsException')]
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        ZonedDateTime::parse($text);
    }

    public static function providerParseInvalidStringThrowsException(): array
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
            ['2001-02-03T01:02:03Z '],
        ];
    }

    #[RequiresPhp('< 8.1.7')]
    #[DataProvider('providerParseSecondsOffsetThrowsException')]
    public function testParseSecondsOffsetThrowsException(string $text): void
    {
        $this->expectException(DateTimeException::class);
        ZonedDateTime::parse($text);
    }

    public static function providerParseSecondsOffsetThrowsException(): array
    {
        return [
            ['2001-02-03T01:02:03.456+12:34:56'],
        ];
    }

    #[DataProvider('providerFromNativeDateTime')]
    public function testFromNativeDateTime(string $dateTimeString, string $timeZone, string $expected): void
    {
        $dateTime = new DateTime($dateTimeString, new DateTimeZone($timeZone));
        self::assertIs(ZonedDateTime::class, $expected, ZonedDateTime::fromNativeDateTime($dateTime));
    }

    public static function providerFromNativeDateTime(): array
    {
        return [
            ['2018-07-21 14:09:10.23456', 'America/Los_Angeles', '2018-07-21T14:09:10.23456-07:00[America/Los_Angeles]'],
            ['2019-01-21 17:59', 'America/Los_Angeles', '2019-01-21T17:59-08:00[America/Los_Angeles]'],
            ['2019-01-23 09:10:11.123', '+05:30', '2019-01-23T09:10:11.123+05:30'],
        ];
    }

    public function testChangeTimeZone(): void
    {
        $timezone1 = TimeZone::parse('UTC');
        $timezone2 = TimeZone::parse('America/Los_Angeles');

        $datetime1 = ZonedDateTime::ofInstant(Instant::of(1000000000), $timezone1);
        $datetime2 = $datetime1->withTimeZoneSameInstant($timezone2);

        self::assertSame($timezone1, $datetime1->getTimeZone());
        self::assertSame($timezone2, $datetime2->getTimeZone());
        self::assertSame('2001-09-08T18:46:40', (string) $datetime2->getDateTime());

        $datetime2 = $datetime1->withTimeZoneSameLocal($timezone2);

        self::assertSame($timezone1, $datetime1->getTimeZone());
        self::assertSame($timezone2, $datetime2->getTimeZone());
        self::assertSame('2001-09-09T01:46:40', (string) $datetime2->getDateTime());
    }

    /**
     * @param string $z1  The first zoned date-time.
     * @param string $z2  The second zoned date-time.
     * @param int    $cmp The comparison value.
     */
    #[DataProvider('providerCompareTo')]
    public function testCompareTo(string $z1, string $z2, int $cmp): void
    {
        $z1 = ZonedDateTime::parse($z1);
        $z2 = ZonedDateTime::parse($z2);

        self::assertSame($cmp, $z1->compareTo($z2));
        self::assertSame($cmp === 0, $z1->isEqualTo($z2));
        self::assertSame($cmp === -1, $z1->isBefore($z2));
        self::assertSame($cmp === 1, $z1->isAfter($z2));
        self::assertSame($cmp <= 0, $z1->isBeforeOrEqualTo($z2));
        self::assertSame($cmp >= 0, $z1->isAfterOrEqualTo($z2));

        self::assertSame(-$cmp, $z2->compareTo($z1));
        self::assertSame($cmp === 0, $z2->isEqualTo($z1));
        self::assertSame($cmp === 1, $z2->isBefore($z1));
        self::assertSame($cmp === -1, $z2->isAfter($z1));
        self::assertSame($cmp >= 0, $z2->isBeforeOrEqualTo($z1));
        self::assertSame($cmp <= 0, $z2->isAfterOrEqualTo($z1));
    }

    public static function providerCompareTo(): array
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

    public function testGetYear(): void
    {
        self::assertSame(2000, $this->getTestZonedDateTime()->getYear());
    }

    #[DataProvider('providerGetMonth')]
    public function testGetMonth(int $monthValue, Month $month): void
    {
        $zonedDateTime = ZonedDateTime::of(
            LocalDateTime::of(2000, $monthValue, 1),
            TimeZoneOffset::utc(),
        );

        self::assertSame($month, $zonedDateTime->getMonth());
    }

    public static function providerGetMonth(): array
    {
        return [
            [1, Month::JANUARY],
            [2, Month::FEBRUARY],
            [3, Month::MARCH],
            [4, Month::APRIL],
            [5, Month::MAY],
            [6, Month::JUNE],
            [7, Month::JULY],
            [8, Month::AUGUST],
            [9, Month::SEPTEMBER],
            [10, Month::OCTOBER],
            [11, Month::NOVEMBER],
            [12, Month::DECEMBER],
        ];
    }

    public function testGetMonthValue(): void
    {
        self::assertSame(1, $this->getTestZonedDateTime()->getMonthValue());
    }

    public function testGetDayOfMonth(): void
    {
        self::assertSame(20, $this->getTestZonedDateTime()->getDayOfMonth());
    }

    public function testGetDayOfWeek(): void
    {
        self::assertSame(DayOfWeek::THURSDAY, $this->getTestZonedDateTime()->getDayOfWeek());
    }

    public function testGetDayOfYear(): void
    {
        self::assertSame(20, $this->getTestZonedDateTime()->getDayOfYear());
    }

    public function testGetHour(): void
    {
        self::assertSame(12, $this->getTestZonedDateTime()->getHour());
    }

    public function testGetMinute(): void
    {
        self::assertSame(34, $this->getTestZonedDateTime()->getMinute());
    }

    public function testGetSecond(): void
    {
        self::assertSame(56, $this->getTestZonedDateTime()->getSecond());
    }

    public function testWithDate(): void
    {
        $newDate = LocalDate::of(2000, 1, 22);

        self::assertIs(ZonedDateTime::class, '2000-01-22T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withDate($newDate));
    }

    public function testWithTime(): void
    {
        $time = LocalTime::of(1, 2, 3, 987654321);

        self::assertIs(ZonedDateTime::class, '2000-01-20T01:02:03.987654321-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withTime($time));
    }

    public function testWithYear(): void
    {
        self::assertIs(ZonedDateTime::class, '2020-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withYear(2020));
    }

    public function testWithMonth(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-07-20T12:34:56.123456789-07:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withMonth(7));
        self::assertIs(ZonedDateTime::class, '2000-07-20T12:34:56.123456789-07:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withMonth(Month::JULY));
    }

    public function testWithDay(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-31T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withDay(31));
    }

    public function testWithHour(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T23:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withHour(23));
    }

    public function testWithMinute(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:00:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withMinute(0));
    }

    public function testWithSecond(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:34:06.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withSecond(6));
    }

    public function testWithNano(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:34:56.000000123-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->withNano(123));
    }

    public function testWithFixedOffsetTimeZone(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:34:56.123456789-08:00', $this->getTestZonedDateTime()->withFixedOffsetTimeZone());
    }

    public function testPlusPeriod(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-04-06T12:34:56.123456789-07:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusPeriod(Period::ofWeeks(11)));
    }

    public function testPlusDuration(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:35:01.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusDuration(Duration::ofSeconds(5)));
    }

    public function testPlusYears(): void
    {
        self::assertIs(ZonedDateTime::class, '2002-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusYears(2));
    }

    public function testPlusMonths(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-03-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusMonths(2));
    }

    public function testPlusWeeks(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-02-03T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusWeeks(2));
    }

    public function testPlusDays(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-22T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusDays(2));
    }

    public function testPlusHours(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T14:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusHours(2));
    }

    public function testPlusMinutes(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:36:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusMinutes(2));
    }

    public function testPlusSeconds(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:34:58.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->plusSeconds(2));
    }

    public function testMinusPeriod(): void
    {
        self::assertIs(ZonedDateTime::class, '1999-11-04T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusPeriod(Period::ofWeeks(11)));
    }

    public function testMinusDuration(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:34:51.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusDuration(Duration::ofSeconds(5)));
    }

    public function testMinusYears(): void
    {
        self::assertIs(ZonedDateTime::class, '1999-01-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusYears(1));
    }

    public function testMinusMonths(): void
    {
        self::assertIs(ZonedDateTime::class, '1999-12-20T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusMonths(1));
    }

    public function testMinusWeeks(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-06T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusWeeks(2));
    }

    public function testMinusDays(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-18T12:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusDays(2));
    }

    public function testMinusHours(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T10:34:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusHours(2));
    }

    public function testMinusMinutes(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:32:56.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusMinutes(2));
    }

    public function testMinusSeconds(): void
    {
        self::assertIs(ZonedDateTime::class, '2000-01-20T12:34:54.123456789-08:00[America/Los_Angeles]', $this->getTestZonedDateTime()->minusSeconds(2));
    }

    public function testIsBetweenInclusive(): void
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

        self::assertTrue($fromZonedDateTime->isBetweenInclusive($fromZonedDateTime, $toZonedDateTime));
        self::assertFalse($fromZonedDateTime->isBetweenInclusive($toZonedDateTime, $notIncluZonedDateTime));
    }

    public function testIsBetweenExclusive(): void
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

        self::assertTrue($incluZonedDateTime->isBetweenExclusive($fromZonedDateTime, $toZonedDateTime));
        self::assertFalse($fromZonedDateTime->isBetweenExclusive($fromZonedDateTime, $toZonedDateTime));
    }

    #[DataProvider('providerForPastFuture')]
    public function testIsFuture(int $clockTimestamp, string $zonedDateTime, bool $isFuture): void
    {
        $clock = new FixedClock(Instant::of($clockTimestamp));
        $zonedDateTime = ZonedDateTime::parse($zonedDateTime);
        self::assertSame($isFuture, $zonedDateTime->isFuture($clock));
    }

    #[DataProvider('providerForPastFuture')]
    public function testIsPast(int $clockTimestamp, string $zonedDateTime, bool $isFuture): void
    {
        $clock = new FixedClock(Instant::of($clockTimestamp));
        $zonedDateTime = ZonedDateTime::parse($zonedDateTime);
        self::assertSame(! $isFuture, $zonedDateTime->isPast($clock));
    }

    public static function providerForPastFuture(): array
    {
        return [
            [1234567890, '2009-02-14T00:31:29+01:00', false],
            [1234567890, '2009-02-14T00:31:31+01:00', true],
            [2345678901, '2044-04-30T17:28:20-08:00', false],
            [2345678901, '2044-04-30T17:28:22-08:00', true],
        ];
    }

    /**
     * @param string $dateTime The date-time string that will be parse()d by ZonedDateTime.
     * @param string $expected The expected output from the native DateTime object.
     */
    #[DataProvider('providerToNativeDateTime')]
    public function testToNativeDateTime(string $dateTime, string $expected): void
    {
        $zonedDateTime = ZonedDateTime::parse($dateTime);
        $dateTime = $zonedDateTime->toNativeDateTime();

        self::assertInstanceOf(DateTime::class, $dateTime);
        self::assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    /**
     * @param string $dateTime The date-time string that will be parse()d by ZonedDateTime.
     * @param string $expected The expected output from the native DateTime object.
     */
    #[DataProvider('providerToNativeDateTime')]
    public function testToNativeDateTimeImmutable(string $dateTime, string $expected): void
    {
        $zonedDateTime = ZonedDateTime::parse($dateTime);
        $dateTime = $zonedDateTime->toNativeDateTimeImmutable();

        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
        self::assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    public static function providerToNativeDateTime(): array
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

    #[DataProvider('providerToString')]
    public function testJsonSerialize(string $localDateTime, string $timeZone, string $expectedString): void
    {
        $localDateTime = LocalDateTime::parse($localDateTime);
        $zonedDateTime = ZonedDateTime::of($localDateTime, TimeZone::parse($timeZone));

        self::assertSame(json_encode($expectedString, JSON_THROW_ON_ERROR), json_encode($zonedDateTime, JSON_THROW_ON_ERROR));
    }

    #[DataProvider('providerToString')]
    public function testToISOString(string $localDateTime, string $timeZone, string $expectedString): void
    {
        $localDateTime = LocalDateTime::parse($localDateTime);
        $zonedDateTime = ZonedDateTime::of($localDateTime, TimeZone::parse($timeZone));

        self::assertSame($expectedString, $zonedDateTime->toISOString());
    }

    #[DataProvider('providerToString')]
    public function testToString(string $localDateTime, string $timeZone, string $expectedString): void
    {
        $localDateTime = LocalDateTime::parse($localDateTime);
        $zonedDateTime = ZonedDateTime::of($localDateTime, TimeZone::parse($timeZone));

        self::assertSame($expectedString, (string) $zonedDateTime);
    }

    public static function providerToString(): array
    {
        return [
            ['2000-01-20T12:34:56.123456789', 'America/Los_Angeles', '2000-01-20T12:34:56.123456789-08:00[America/Los_Angeles]'],
            ['2000-01-20T12:34:56.123456789', '-07:00', '2000-01-20T12:34:56.123456789-07:00'],
        ];
    }

    #[DataProvider('providerGetDurationTo')]
    public function testGetDurationTo(string $firstDate, string $secondDate, int $expectedSeconds, int $expectedNanos): void
    {
        $actualResult = ZonedDateTime::parse($firstDate)->getDurationTo(ZonedDateTime::parse($secondDate));

        self::assertDurationIs($expectedSeconds, $expectedNanos, $actualResult);
    }

    public static function providerGetDurationTo(): array
    {
        return [
            ['2023-01-01T10:00:00Z',           '2023-01-01T10:00:00Z',           0, 0],
            ['2023-01-01T10:00:00Z',           '2023-01-01T10:00:10Z',           10, 0],
            ['2023-01-01T10:00:00.001Z',       '2023-01-01T10:00:10.002Z',       10, 1000000],
            ['2023-01-01T10:00:00.001Z',       '2023-01-01T13:00:10.002+03:00',  10, 1000000],
            ['2023-01-01T10:00:00.000000001Z', '2023-01-01T10:00:00.000000009Z', 0, 8],
            ['2023-01-01T10:00:00Z',           '2023-01-02T10:00:00Z',           24 * 60 * 60, 0],
            ['2023-01-02T10:00:00Z',           '2023-01-01T10:00:00Z',           -24 * 60 * 60, 0],
        ];
    }

    #[DataProvider('providerGetIntervalTo')]
    public function testGetIntervalTo(string $firstDate, string $secondDate, string $expectedInterval): void
    {
        $actualResult = ZonedDateTime::parse($firstDate)->getIntervalTo(ZonedDateTime::parse($secondDate));

        self::assertSame($expectedInterval, (string) $actualResult);
    }

    public static function providerGetIntervalTo(): array
    {
        return [
            ['2023-01-01T10:00:00Z',           '2023-01-01T10:00:00Z',           '2023-01-01T10:00Z/2023-01-01T10:00Z'],
            ['2023-01-01T10:00:00Z',           '2023-01-01T10:00:10Z',           '2023-01-01T10:00Z/2023-01-01T10:00:10Z'],
            ['2023-01-01T10:00:00.001Z',       '2023-01-01T10:00:10.002Z',       '2023-01-01T10:00:00.001Z/2023-01-01T10:00:10.002Z'],
            ['2023-01-01T10:00:00.001Z',       '2023-01-01T13:00:10.002+03:00',  '2023-01-01T10:00:00.001Z/2023-01-01T10:00:10.002Z'],
            ['2023-01-01T10:00:00.001+03:00',  '2023-01-01T13:00:10.002+03:00',  '2023-01-01T07:00:00.001Z/2023-01-01T10:00:10.002Z'],
            ['2023-01-01T10:00:00.000000001Z', '2023-01-01T10:00:00.000000009Z', '2023-01-01T10:00:00.000000001Z/2023-01-01T10:00:00.000000009Z'],
            ['2023-01-01T10:00:00Z',           '2023-01-02T10:00:00Z',           '2023-01-01T10:00Z/2023-01-02T10:00Z'],
        ];
    }

    private function getTestZonedDateTime(): ZonedDateTime
    {
        $timeZone = TimeZone::parse('America/Los_Angeles');
        $localDateTime = LocalDateTime::parse('2000-01-20T12:34:56.123456789');

        return ZonedDateTime::of($localDateTime, $timeZone);
    }
}
