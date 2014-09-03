<?php

namespace Brick\DateTime\Tests;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\TimeZoneOffset;

/**
 * Unit tests for class LocalTime.
 */
class LocalTimeTest extends AbstractTestCase
{
    public function testOf()
    {
        $this->assertLocalTimeIs(12, 34, 56, 123456789, LocalTime::of(12, 34, 56, 123456789));
    }

    /**
     * @dataProvider providerOfInvalidTimeThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $hour
     * @param integer $minute
     * @param integer $second
     */
    public function testOfInvalidTimeThrowsException($hour, $minute, $second)
    {
        LocalTime::of($hour, $minute, $second);
    }

    /**
     * @return array
     */
    public function providerOfInvalidTimeThrowsException()
    {
        return [
            [-1, 0, 0],
            [24, 0, 0],
            [0, -1, 0],
            [0, 60, 0],
            [0, 0, -1],
            [0, 0, 60]
        ];
    }

    /**
     * @dataProvider providerOfSecondOfDay
     *
     * @param integer $secondOfDay  The second-of-day to test.
     * @param integer $hour         The expected resulting hour.
     * @param integer $minute       The expected resulting minute.
     * @param integer $second       The expected resulting second.
     */
    public function testOfSecondOfDay($secondOfDay, $hour, $minute, $second)
    {
        $localTime = LocalTime::ofSecondOfDay($secondOfDay, 123);
        $this->assertLocalTimeIs($hour, $minute, $second, 123, $localTime);
    }

    /**
     * @return array
     */
    public function providerOfSecondOfDay()
    {
        return [
            [0, 0, 0, 0],
            [1, 0, 0, 1],
            [59, 0, 0, 59],
            [60, 0, 1, 0],
            [61, 0, 1, 1],
            [3599, 0, 59, 59],
            [3600, 1, 0, 0],
            [3601, 1, 0, 1],
            [3659, 1, 0, 59],
            [3660, 1, 1, 0],
            [3661, 1, 1, 1],
            [43199, 11, 59, 59],
            [43200, 12, 0, 0],
            [43201, 12, 0, 1],
            [86399, 23, 59, 59]
        ];
    }

    /**
     * @dataProvider providerOfInvalidSecondOfDayThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $secondOfDay
     * @param integer $nanoOfSecond
     */
    public function testOfInvalidSecondOfDayThrowsException($secondOfDay, $nanoOfSecond)
    {
        LocalTime::ofSecondOfDay($secondOfDay, $nanoOfSecond);
    }

    /**
     * @return array
     */
    public function providerOfInvalidSecondOfDayThrowsException()
    {
        return [
            [-1, 0],
            [86400, 0],
            [0, -1],
            [0, 1000000000]
        ];
    }

    /**
     * @dataProvider providerParse
     *
     * @param string  $text
     * @param integer $hour
     * @param integer $minute
     * @param integer $second
     * @param integer $nano
     */
    public function testParse($text, $hour, $minute, $second, $nano)
    {
        $time = LocalTime::parse($text);

        $this->assertSame($hour, $time->getHour());
        $this->assertSame($minute, $time->getMinute());
        $this->assertSame($second, $time->getSecond());
        $this->assertSame($nano, $time->getNano());
    }

    /**
     * @return array
     */
    public function providerParse()
    {
        return [
            ['01:02', 1, 2, 0, 0],
            ['12:34:56', 12, 34, 56, 0],
            ['12:34:56.78', 12, 34, 56, 780000000],
            ['12:34:56.789123456', 12, 34, 56, 789123456]
        ];
    }

    /**
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     * @dataProvider providerParseInvalidStringThrowsException
     *
     * @param string $text
     */
    public function testParseInvalidStringThrowsException($text)
    {
        LocalTime::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseInvalidStringThrowsException()
    {
        return [
            ['12'],
            ['12:'],
            ['12.34'],
            ['1:23'],
            ['12:2'],
            ['12:34:'],
            ['12:23:4'],
            ['12:34:56.'],
            ['12:34.567'],
            [' 12:34'],
            ['12:34:56 '],
            ['12:34:56.7 ']
        ];
    }

    /**
     * @dataProvider providerNow
     *
     * @param integer $second The second to set the clock to.
     * @param integer $nano   The nanosecond adjustment to the clock.
     * @param integer $offset The time-zone offset to get the time at.
     * @param integer $h      The expected hour.
     * @param integer $m      The expected minute.
     * @param integer $s      The expected second.
     * @param integer $n      The expected nano.
     */
    public function testNow($second, $nano, $offset, $h, $m, $s, $n)
    {
        $this->setClockTime($second, $nano);
        $timeZone = TimeZoneOffset::ofTotalSeconds($offset);
        $this->assertLocalTimeIs($h, $m, $s, $n, LocalTime::now($timeZone));
    }

    /**
     * @return array
     */
    public function providerNow()
    {
        return [
            [1409574896, 0, 0, 12, 34, 56, 0],
            [1409574896, 123, 0, 12, 34, 56, 123],
            [1409574896, 0, 3600, 13, 34, 56, 0],
            [1409574896, 123456, 5400, 14, 4, 56, 123456]
        ];
    }

    public function testMidnight()
    {
        $this->assertLocalTimeIs(0, 0, 0, 0, LocalTime::midnight());
    }

    public function testNoon()
    {
        $this->assertLocalTimeIs(12, 0, 0, 0, LocalTime::noon());
    }

    public function testMin()
    {
        $this->assertLocalTimeIs(0, 0, 0, 0, LocalTime::min());
    }

    public function testMax()
    {
        $this->assertLocalTimeIs(23, 59, 59, 999999999, LocalTime::max());
    }

    /**
     * @dataProvider providerWithHour
     *
     * @param integer $hour The new hour.
     */
    public function testWithHour($hour)
    {
        $this->assertLocalTimeIs($hour, 34, 56, 789, LocalTime::of(12, 34, 56, 789)->withHour($hour));
    }

    /**
     * @return array
     */
    public function providerWithHour()
    {
        return [
            [12],
            [23]
        ];
    }

    /**
     * @dataProvider providerWithInvalidHourThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidHour
     */
    public function testWithInvalidHourThrowsException($invalidHour)
    {
        LocalTime::of(12, 34, 56)->withHour($invalidHour);
    }

    /**
     * @return array
     */
    public function providerWithInvalidHourThrowsException()
    {
        return [
            [-1],
            [24]
        ];
    }

    /**
     * @dataProvider providerWithMinute
     *
     * @param integer $minute The new minute.
     */
    public function testWithMinute($minute)
    {
        $this->assertLocalTimeIs(12, $minute, 56, 789, LocalTime::of(12, 34, 56, 789)->withMinute($minute));
    }

    /**
     * @return array
     */
    public function providerWithMinute()
    {
        return [
            [34],
            [45]
        ];
    }

    /**
     * @dataProvider providerWithInvalidMinuteThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidMinute
     */
    public function testWithInvalidMinuteThrowsException($invalidMinute)
    {
        LocalTime::of(12, 34, 56)->withMinute($invalidMinute);
    }

    /**
     * @return array
     */
    public function providerWithInvalidMinuteThrowsException()
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithSecond
     *
     * @param integer $second The new second.
     */
    public function testWithSecond($second)
    {
        $this->assertLocalTimeIs(12, 34, $second, 789, LocalTime::of(12, 34, 56, 789)->withSecond($second));
    }

    /**
     * @return array
     */
    public function providerWithSecond()
    {
        return [
            [56],
            [45]
        ];
    }

    /**
     * @dataProvider providerWithInvalidSecondThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidSecond
     */
    public function testWithInvalidSecondThrowsException($invalidSecond)
    {
        LocalTime::of(12, 34, 56)->withSecond($invalidSecond);
    }

    /**
     * @return array
     */
    public function providerWithInvalidSecondThrowsException()
    {
        return [
            [-1],
            [60]
        ];
    }

    /**
     * @dataProvider providerWithNano
     *
     * @param integer $nano The new nano.
     */
    public function testWithNano($nano)
    {
        $this->assertLocalTimeIs(12, 34, 56, $nano, LocalTime::of(12, 34, 56, 789)->withNano($nano));
    }

    /**
     * @return array
     */
    public function providerWithNano()
    {
        return [
            [789],
            [123456]
        ];
    }

    /**
     * @dataProvider providerWithInvalidNanoThrowsException
     * @expectedException \Brick\DateTime\DateTimeException
     *
     * @param integer $invalidNano
     */
    public function testWithInvalidNanoThrowsException($invalidNano)
    {
        LocalTime::of(12, 34, 56)->withNano($invalidNano);
    }

    /**
     * @return array
     */
    public function providerWithInvalidNanoThrowsException()
    {
        return [
            [-1],
            [1000000000]
        ];
    }

    public function testWithSameValueReturnsThis()
    {
        $time = LocalTime::of(12, 34, 56, 789);

        $this->assertSame($time, $time->withHour(12));
        $this->assertSame($time, $time->withMinute(34));
        $this->assertSame($time, $time->withSecond(56));
        $this->assertSame($time, $time->withNano(789));
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param integer $h  The base hour.
     * @param integer $d  The number of hours to add.
     * @param integer $eh The expected result hour.
     */
    public function testPlusHours($h, $d, $eh)
    {
        $result = LocalTime::of($h, 34, 56, 789)->plusHours($d);
        $this->assertLocalTimeIs($eh, 34, 56, 789, $result);
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param integer $h  The base hour.
     * @param integer $d  The number of hours to add.
     * @param integer $eh The expected result hour.
     */
    public function testMinusHours($h, $d, $eh)
    {
        $result = LocalTime::of($h, 34, 56, 789)->minusHours(-$d);
        $this->assertLocalTimeIs($eh, 34, 56, 789, $result);
    }

    /**
     * @return array
     */
    public function providerPlusHours()
    {
        return [
            [0, -25, 23],
            [0, -24, 0],
            [0, -23, 1],
            [0, -1, 23],
            [0, 0, 0],
            [0, 1, 1],
            [0, 23, 23],
            [0, 24, 0],
            [0, 25, 1],
            [12, -25, 11],
            [12, -24, 12],
            [12, -23, 13],
            [12, -1, 11],
            [12, 0, 12],
            [12, 1, 13],
            [12, 23, 11],
            [12, 24, 12],
            [12, 25, 13],
            [23, -25, 22],
            [23, -24, 23],
            [23, -23, 0],
            [23, -1, 22],
            [23, 0, 23],
            [23, 1, 0],
            [23, 23, 22],
            [23, 24, 23],
            [23, 25, 0]
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $d  The number of minutes to add.
     * @param integer $eh The expected result hour.
     * @param integer $em The expected result minute.
     */
    public function testPlusMinutes($h, $m, $d, $eh, $em)
    {
        $result = LocalTime::of($h, $m, 56, 789)->plusMinutes($d);
        $this->assertLocalTimeIs($eh, $em, 56, 789, $result);
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $d  The number of minutes to add.
     * @param integer $eh The expected result hour.
     * @param integer $em The expected result minute.
     */
    public function testMinusMinutes($h, $m, $d, $eh, $em)
    {
        $result = LocalTime::of($h, $m, 56, 789)->minusMinutes(-$d);
        $this->assertLocalTimeIs($eh, $em, 56, 789, $result);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes()
    {
        return [
            [0, 0, -1441, 23, 59],
            [0, 0, -1440, 0, 0],
            [0, 0, -1439, 0, 1],
            [0, 0, -61, 22, 59],
            [0, 0, -60, 23, 0],
            [0, 0, -59, 23, 1],
            [0, 0, -1, 23, 59],
            [0, 0, 0, 0, 0],
            [0, 0, 1, 0, 1],
            [0, 0, 59, 0, 59],
            [0, 0, 60, 1, 0],
            [0, 0, 61, 1, 1],
            [0, 0, 1439, 23, 59],
            [0, 0, 1440, 0, 0],
            [0, 0, 1441, 0, 1],
            [12, 45, -1441, 12, 44],
            [12, 45, -1440, 12, 45],
            [12, 45, -1439, 12, 46],
            [12, 45, -766, 23, 59],
            [12, 45, -765, 0, 0],
            [12, 45, -764, 0, 1],
            [12, 45, -1, 12, 44],
            [12, 45, 0, 12, 45],
            [12, 45, 1, 12, 46],
            [12, 45, 674, 23, 59],
            [12, 45, 675, 0, 0],
            [12, 45, 676, 0, 1],
            [12, 45, 1439, 12, 44],
            [12, 45, 1440, 12, 45],
            [12, 45, 1441, 12, 46],
            [23, 59, -1441, 23, 58],
            [23, 59, -1440, 23, 59],
            [23, 59, -1439, 0, 0],
            [23, 59, -61, 22, 58],
            [23, 59, -60, 22, 59],
            [23, 59, -59, 23, 0],
            [23, 59, -1, 23, 58],
            [23, 59, 0, 23, 59],
            [23, 59, 1, 0, 0],
            [23, 59, 59, 0, 58],
            [23, 59, 60, 0, 59],
            [23, 59, 61, 1, 0],
            [23, 59, 1439, 23, 58],
            [23, 59, 1440, 23, 59],
            [23, 59, 1441, 0, 0]
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $s  The base second.
     * @param integer $d  The number of seconds to add.
     * @param integer $eh The expected result hour.
     * @param integer $em The expected result minute.
     * @param integer $es The expected result second.
     */
    public function testPlusSeconds($h, $m, $s, $d, $eh, $em, $es)
    {
        $result = LocalTime::of($h, $m, $s, 123456789)->plusSeconds($d);
        $this->assertLocalTimeIs($eh, $em, $es, 123456789, $result);
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $s  The base second.
     * @param integer $d  The number of seconds to add.
     * @param integer $eh The expected result hour.
     * @param integer $em The expected result minute.
     * @param integer $es The expected result second.
     */
    public function testMinusSeconds($h, $m, $s, $d, $eh, $em, $es)
    {
        $result = LocalTime::of($h, $m, $s, 123456789)->minusSeconds(-$d);
        $this->assertLocalTimeIs($eh, $em, $es, 123456789, $result);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds()
    {
        return [
            [0, 0, 0, -86401, 23, 59, 59],
            [0, 0, 0, -86400, 0, 0, 0],
            [0, 0, 0, -86399, 0, 0, 1],
            [0, 0, 0, -3601, 22, 59, 59],
            [0, 0, 0, -3600, 23, 0, 0],
            [0, 0, 0, -3599, 23, 0, 1],
            [0, 0, 0, -61, 23, 58, 59],
            [0, 0, 0, -60, 23, 59, 0],
            [0, 0, 0, -59, 23, 59, 1],
            [0, 0, 0, -1, 23, 59, 59],
            [0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 0, 1],
            [0, 0, 0, 59, 0, 0, 59],
            [0, 0, 0, 60, 0, 1, 0],
            [0, 0, 0, 61, 0, 1, 1],
            [0, 0, 0, 3599, 0, 59, 59],
            [0, 0, 0, 3600, 1, 0, 0],
            [0, 0, 0, 3601, 1, 0, 1],
            [0, 0, 0, 86399, 23, 59, 59],
            [0, 0, 0, 86400, 0, 0, 0],
            [0, 0, 0, 86401, 0, 0, 1],
            [15, 30, 45, -86401, 15, 30, 44],
            [15, 30, 45, -86400, 15, 30, 45],
            [15, 30, 45, -86399, 15, 30, 46],
            [15, 30, 45, -55846, 23, 59, 59],
            [15, 30, 45, -55845, 0, 0, 0],
            [15, 30, 45, -55844, 0, 0, 1],
            [15, 30, 45, -1, 15, 30, 44],
            [15, 30, 45, 0, 15, 30, 45],
            [15, 30, 45, 1, 15, 30, 46],
            [15, 30, 45, 30554, 23, 59, 59],
            [15, 30, 45, 30555, 0, 0, 0],
            [15, 30, 45, 30556, 0, 0, 1],
            [15, 30, 45, 86399, 15, 30, 44],
            [15, 30, 45, 86400, 15, 30, 45],
            [15, 30, 45, 86401, 15, 30, 46],
            [23, 59, 59, -86401, 23, 59, 58],
            [23, 59, 59, -86400, 23, 59, 59],
            [23, 59, 59, -86399, 0, 0, 0],
            [23, 59, 59, -3601, 22, 59, 58],
            [23, 59, 59, -3600, 22, 59, 59],
            [23, 59, 59, -3599, 23, 0, 0],
            [23, 59, 59, -61, 23, 58, 58],
            [23, 59, 59, -60, 23, 58, 59],
            [23, 59, 59, -59, 23, 59, 0],
            [23, 59, 59, -1, 23, 59, 58],
            [23, 59, 59, 0, 23, 59, 59],
            [23, 59, 59, 1, 0, 0, 0],
            [23, 59, 59, 59, 0, 0, 58],
            [23, 59, 59, 60, 0, 0, 59],
            [23, 59, 59, 61, 0, 1, 0],
            [23, 59, 59, 3599, 0, 59, 58],
            [23, 59, 59, 3600, 0, 59, 59],
            [23, 59, 59, 3601, 1, 0, 0],
            [23, 59, 59, 86399, 23, 59, 58],
            [23, 59, 59, 86400, 23, 59, 59],
            [23, 59, 59, 86401, 0, 0, 0],
        ];
    }

    /**
     * @dataProvider providerPlusNanos
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $s  The base second.
     * @param integer $n  The base nanosecond.
     * @param integer $d  The nanoseconds to add.
     * @param integer $eh The expected result hour.
     * @param integer $em The expected result minute.
     * @param integer $es The expected result second.
     * @param integer $en The expected result nanosecond.
     */
    public function testPlusNanos($h, $m, $s, $n, $d, $eh, $em, $es, $en)
    {
        $result = LocalTime::of($h, $m, $s, $n)->plusNanos($d);
        $this->assertLocalTimeIs($eh, $em, $es, $en, $result);
    }

    /**
     * @dataProvider providerPlusNanos
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $s  The base second.
     * @param integer $n  The base nanosecond.
     * @param integer $d  The nanoseconds to add.
     * @param integer $eh The expected result hour.
     * @param integer $em The expected result minute.
     * @param integer $es The expected result second.
     * @param integer $en The expected result nanosecond.
     */
    public function testMinusNanos($h, $m, $s, $n, $d, $eh, $em, $es, $en)
    {
        $result = LocalTime::of($h, $m, $s, $n)->minusNanos(-$d);
        $this->assertLocalTimeIs($eh, $em, $es, $en, $result);
    }

    /**
     * @return array
     */
    public function providerPlusNanos()
    {
        return [
            [0, 0, 1, 123, -2100000123, 23, 59, 58, 900000000],
            [0, 0, 1, 123, -1000000124, 23, 59, 59, 999999999],
            [0, 0, 1, 123, -1000000123, 0, 0, 0, 0],
            [0, 0, 1, 123, -124, 0, 0, 0, 999999999],
            [0, 0, 1, 123, -123, 0, 0, 1, 0],
            [0, 0, 1, 123, -1, 0, 0, 1, 122],
            [0, 0, 1, 123, 0, 0, 0, 1, 123],
            [0, 0, 1, 123, 1, 0, 0, 1, 124],
            [0, 0, 1, 123, 123, 0, 0, 1, 246],
            [0, 0, 1, 123, 999999877, 0, 0, 2, 0],
            [0, 0, 1, 123, 1999999878, 0, 0, 3, 1],
            [23, 59, 58, 987654321, -1987654321, 23, 59, 57, 0],
            [23, 59, 58, 987654321, -987654322, 23, 59, 57, 999999999],
            [23, 59, 58, 987654321, -987654321, 23, 59, 58, 0],
            [23, 59, 58, 987654321, -987654320, 23, 59, 58, 1],
            [23, 59, 58, 987654321, -1, 23, 59, 58, 987654320],
            [23, 59, 58, 987654321, 0, 23, 59, 58, 987654321],
            [23, 59, 58, 987654321, 1, 23, 59, 58, 987654322],
            [23, 59, 58, 987654321, 123456789, 23, 59, 59, 111111110],
            [23, 59, 58, 987654321, 1987654321, 0, 0, 0, 975308642],
            [23, 59, 58, 987654321, 2123456789, 0, 0, 1, 111111110]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $h1  The hour of the 1st time.
     * @param integer $m1  The minute of the 1st time.
     * @param integer $s1  The second of the 1st time.
     * @param integer $n1  The nano of the 1st time.
     * @param integer $h2  The hour of the 2nd time.
     * @param integer $m2  The minute of the 2nd time.
     * @param integer $s2  The second of the 2nd time.
     * @param integer $n2  The nano of the 2nd time.
     * @param integer $cmp The comparison value.
     */
    public function testCompareTo($h1, $m1, $s1, $n1, $h2, $m2, $s2, $n2, $cmp)
    {
        $t1 = LocalTime::of($h1, $m1, $s1, $n1);
        $t2 = LocalTime::of($h2, $m2, $s2, $n2);

        $this->assertSame($cmp, $t1->compareTo($t2));
        $this->assertSame($cmp === 0, $t1->isEqualTo($t2));
        $this->assertSame($cmp === -1, $t1->isBefore($t2));
        $this->assertSame($cmp === 1, $t1->isAfter($t2));
    }

    /**
     * @return array
     */
    public function providerCompareTo()
    {
        return [
            [0, 0, 0, 0, 0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0, 0, 0, 99, -1],
            [0, 0, 0, 0, 0, 0, 59, 0, -1],
            [0, 0, 0, 0, 0, 0, 59, 99, -1],
            [0, 0, 0, 0, 0, 59, 0, 0, -1],
            [0, 0, 0, 0, 0, 59, 0, 99, -1],
            [0, 0, 0, 0, 0, 59, 59, 0, -1],
            [0, 0, 0, 0, 0, 59, 59, 99, -1],
            [0, 0, 0, 0, 23, 0, 0, 0, -1],
            [0, 0, 0, 0, 23, 0, 0, 99, -1],
            [0, 0, 0, 0, 23, 0, 59, 0, -1],
            [0, 0, 0, 0, 23, 0, 59, 99, -1],
            [0, 0, 0, 0, 23, 59, 0, 0, -1],
            [0, 0, 0, 0, 23, 59, 0, 99, -1],
            [0, 0, 0, 0, 23, 59, 59, 0, -1],
            [0, 0, 0, 0, 23, 59, 59, 99, -1],
            [0, 0, 0, 99, 0, 0, 0, 0, 1],
            [0, 0, 0, 99, 0, 0, 0, 99, 0],
            [0, 0, 0, 99, 0, 0, 59, 0, -1],
            [0, 0, 0, 99, 0, 0, 59, 99, -1],
            [0, 0, 0, 99, 0, 59, 0, 0, -1],
            [0, 0, 0, 99, 0, 59, 0, 99, -1],
            [0, 0, 0, 99, 0, 59, 59, 0, -1],
            [0, 0, 0, 99, 0, 59, 59, 99, -1],
            [0, 0, 0, 99, 23, 0, 0, 0, -1],
            [0, 0, 0, 99, 23, 0, 0, 99, -1],
            [0, 0, 0, 99, 23, 0, 59, 0, -1],
            [0, 0, 0, 99, 23, 0, 59, 99, -1],
            [0, 0, 0, 99, 23, 59, 0, 0, -1],
            [0, 0, 0, 99, 23, 59, 0, 99, -1],
            [0, 0, 0, 99, 23, 59, 59, 0, -1],
            [0, 0, 0, 99, 23, 59, 59, 99, -1],
            [0, 0, 59, 0, 0, 0, 0, 0, 1],
            [0, 0, 59, 0, 0, 0, 0, 99, 1],
            [0, 0, 59, 0, 0, 0, 59, 0, 0],
            [0, 0, 59, 0, 0, 0, 59, 99, -1],
            [0, 0, 59, 0, 0, 59, 0, 0, -1],
            [0, 0, 59, 0, 0, 59, 0, 99, -1],
            [0, 0, 59, 0, 0, 59, 59, 0, -1],
            [0, 0, 59, 0, 0, 59, 59, 99, -1],
            [0, 0, 59, 0, 23, 0, 0, 0, -1],
            [0, 0, 59, 0, 23, 0, 0, 99, -1],
            [0, 0, 59, 0, 23, 0, 59, 0, -1],
            [0, 0, 59, 0, 23, 0, 59, 99, -1],
            [0, 0, 59, 0, 23, 59, 0, 0, -1],
            [0, 0, 59, 0, 23, 59, 0, 99, -1],
            [0, 0, 59, 0, 23, 59, 59, 0, -1],
            [0, 0, 59, 0, 23, 59, 59, 99, -1],
            [0, 0, 59, 99, 0, 0, 0, 0, 1],
            [0, 0, 59, 99, 0, 0, 0, 99, 1],
            [0, 0, 59, 99, 0, 0, 59, 0, 1],
            [0, 0, 59, 99, 0, 0, 59, 99, 0],
            [0, 0, 59, 99, 0, 59, 0, 0, -1],
            [0, 0, 59, 99, 0, 59, 0, 99, -1],
            [0, 0, 59, 99, 0, 59, 59, 0, -1],
            [0, 0, 59, 99, 0, 59, 59, 99, -1],
            [0, 0, 59, 99, 23, 0, 0, 0, -1],
            [0, 0, 59, 99, 23, 0, 0, 99, -1],
            [0, 0, 59, 99, 23, 0, 59, 0, -1],
            [0, 0, 59, 99, 23, 0, 59, 99, -1],
            [0, 0, 59, 99, 23, 59, 0, 0, -1],
            [0, 0, 59, 99, 23, 59, 0, 99, -1],
            [0, 0, 59, 99, 23, 59, 59, 0, -1],
            [0, 0, 59, 99, 23, 59, 59, 99, -1],
            [0, 59, 0, 0, 0, 0, 0, 0, 1],
            [0, 59, 0, 0, 0, 0, 0, 99, 1],
            [0, 59, 0, 0, 0, 0, 59, 0, 1],
            [0, 59, 0, 0, 0, 0, 59, 99, 1],
            [0, 59, 0, 0, 0, 59, 0, 0, 0],
            [0, 59, 0, 0, 0, 59, 0, 99, -1],
            [0, 59, 0, 0, 0, 59, 59, 0, -1],
            [0, 59, 0, 0, 0, 59, 59, 99, -1],
            [0, 59, 0, 0, 23, 0, 0, 0, -1],
            [0, 59, 0, 0, 23, 0, 0, 99, -1],
            [0, 59, 0, 0, 23, 0, 59, 0, -1],
            [0, 59, 0, 0, 23, 0, 59, 99, -1],
            [0, 59, 0, 0, 23, 59, 0, 0, -1],
            [0, 59, 0, 0, 23, 59, 0, 99, -1],
            [0, 59, 0, 0, 23, 59, 59, 0, -1],
            [0, 59, 0, 0, 23, 59, 59, 99, -1],
            [0, 59, 0, 99, 0, 0, 0, 0, 1],
            [0, 59, 0, 99, 0, 0, 0, 99, 1],
            [0, 59, 0, 99, 0, 0, 59, 0, 1],
            [0, 59, 0, 99, 0, 0, 59, 99, 1],
            [0, 59, 0, 99, 0, 59, 0, 0, 1],
            [0, 59, 0, 99, 0, 59, 0, 99, 0],
            [0, 59, 0, 99, 0, 59, 59, 0, -1],
            [0, 59, 0, 99, 0, 59, 59, 99, -1],
            [0, 59, 0, 99, 23, 0, 0, 0, -1],
            [0, 59, 0, 99, 23, 0, 0, 99, -1],
            [0, 59, 0, 99, 23, 0, 59, 0, -1],
            [0, 59, 0, 99, 23, 0, 59, 99, -1],
            [0, 59, 0, 99, 23, 59, 0, 0, -1],
            [0, 59, 0, 99, 23, 59, 0, 99, -1],
            [0, 59, 0, 99, 23, 59, 59, 0, -1],
            [0, 59, 0, 99, 23, 59, 59, 99, -1],
            [0, 59, 59, 0, 0, 0, 0, 0, 1],
            [0, 59, 59, 0, 0, 0, 0, 99, 1],
            [0, 59, 59, 0, 0, 0, 59, 0, 1],
            [0, 59, 59, 0, 0, 0, 59, 99, 1],
            [0, 59, 59, 0, 0, 59, 0, 0, 1],
            [0, 59, 59, 0, 0, 59, 0, 99, 1],
            [0, 59, 59, 0, 0, 59, 59, 0, 0],
            [0, 59, 59, 0, 0, 59, 59, 99, -1],
            [0, 59, 59, 0, 23, 0, 0, 0, -1],
            [0, 59, 59, 0, 23, 0, 0, 99, -1],
            [0, 59, 59, 0, 23, 0, 59, 0, -1],
            [0, 59, 59, 0, 23, 0, 59, 99, -1],
            [0, 59, 59, 0, 23, 59, 0, 0, -1],
            [0, 59, 59, 0, 23, 59, 0, 99, -1],
            [0, 59, 59, 0, 23, 59, 59, 0, -1],
            [0, 59, 59, 0, 23, 59, 59, 99, -1],
            [0, 59, 59, 99, 0, 0, 0, 0, 1],
            [0, 59, 59, 99, 0, 0, 0, 99, 1],
            [0, 59, 59, 99, 0, 0, 59, 0, 1],
            [0, 59, 59, 99, 0, 0, 59, 99, 1],
            [0, 59, 59, 99, 0, 59, 0, 0, 1],
            [0, 59, 59, 99, 0, 59, 0, 99, 1],
            [0, 59, 59, 99, 0, 59, 59, 0, 1],
            [0, 59, 59, 99, 0, 59, 59, 99, 0],
            [0, 59, 59, 99, 23, 0, 0, 0, -1],
            [0, 59, 59, 99, 23, 0, 0, 99, -1],
            [0, 59, 59, 99, 23, 0, 59, 0, -1],
            [0, 59, 59, 99, 23, 0, 59, 99, -1],
            [0, 59, 59, 99, 23, 59, 0, 0, -1],
            [0, 59, 59, 99, 23, 59, 0, 99, -1],
            [0, 59, 59, 99, 23, 59, 59, 0, -1],
            [0, 59, 59, 99, 23, 59, 59, 99, -1],
            [23, 0, 0, 0, 0, 0, 0, 0, 1],
            [23, 0, 0, 0, 0, 0, 0, 99, 1],
            [23, 0, 0, 0, 0, 0, 59, 0, 1],
            [23, 0, 0, 0, 0, 0, 59, 99, 1],
            [23, 0, 0, 0, 0, 59, 0, 0, 1],
            [23, 0, 0, 0, 0, 59, 0, 99, 1],
            [23, 0, 0, 0, 0, 59, 59, 0, 1],
            [23, 0, 0, 0, 0, 59, 59, 99, 1],
            [23, 0, 0, 0, 23, 0, 0, 0, 0],
            [23, 0, 0, 0, 23, 0, 0, 99, -1],
            [23, 0, 0, 0, 23, 0, 59, 0, -1],
            [23, 0, 0, 0, 23, 0, 59, 99, -1],
            [23, 0, 0, 0, 23, 59, 0, 0, -1],
            [23, 0, 0, 0, 23, 59, 0, 99, -1],
            [23, 0, 0, 0, 23, 59, 59, 0, -1],
            [23, 0, 0, 0, 23, 59, 59, 99, -1],
            [23, 0, 0, 99, 0, 0, 0, 0, 1],
            [23, 0, 0, 99, 0, 0, 0, 99, 1],
            [23, 0, 0, 99, 0, 0, 59, 0, 1],
            [23, 0, 0, 99, 0, 0, 59, 99, 1],
            [23, 0, 0, 99, 0, 59, 0, 0, 1],
            [23, 0, 0, 99, 0, 59, 0, 99, 1],
            [23, 0, 0, 99, 0, 59, 59, 0, 1],
            [23, 0, 0, 99, 0, 59, 59, 99, 1],
            [23, 0, 0, 99, 23, 0, 0, 0, 1],
            [23, 0, 0, 99, 23, 0, 0, 99, 0],
            [23, 0, 0, 99, 23, 0, 59, 0, -1],
            [23, 0, 0, 99, 23, 0, 59, 99, -1],
            [23, 0, 0, 99, 23, 59, 0, 0, -1],
            [23, 0, 0, 99, 23, 59, 0, 99, -1],
            [23, 0, 0, 99, 23, 59, 59, 0, -1],
            [23, 0, 0, 99, 23, 59, 59, 99, -1],
            [23, 0, 59, 0, 0, 0, 0, 0, 1],
            [23, 0, 59, 0, 0, 0, 0, 99, 1],
            [23, 0, 59, 0, 0, 0, 59, 0, 1],
            [23, 0, 59, 0, 0, 0, 59, 99, 1],
            [23, 0, 59, 0, 0, 59, 0, 0, 1],
            [23, 0, 59, 0, 0, 59, 0, 99, 1],
            [23, 0, 59, 0, 0, 59, 59, 0, 1],
            [23, 0, 59, 0, 0, 59, 59, 99, 1],
            [23, 0, 59, 0, 23, 0, 0, 0, 1],
            [23, 0, 59, 0, 23, 0, 0, 99, 1],
            [23, 0, 59, 0, 23, 0, 59, 0, 0],
            [23, 0, 59, 0, 23, 0, 59, 99, -1],
            [23, 0, 59, 0, 23, 59, 0, 0, -1],
            [23, 0, 59, 0, 23, 59, 0, 99, -1],
            [23, 0, 59, 0, 23, 59, 59, 0, -1],
            [23, 0, 59, 0, 23, 59, 59, 99, -1],
            [23, 0, 59, 99, 0, 0, 0, 0, 1],
            [23, 0, 59, 99, 0, 0, 0, 99, 1],
            [23, 0, 59, 99, 0, 0, 59, 0, 1],
            [23, 0, 59, 99, 0, 0, 59, 99, 1],
            [23, 0, 59, 99, 0, 59, 0, 0, 1],
            [23, 0, 59, 99, 0, 59, 0, 99, 1],
            [23, 0, 59, 99, 0, 59, 59, 0, 1],
            [23, 0, 59, 99, 0, 59, 59, 99, 1],
            [23, 0, 59, 99, 23, 0, 0, 0, 1],
            [23, 0, 59, 99, 23, 0, 0, 99, 1],
            [23, 0, 59, 99, 23, 0, 59, 0, 1],
            [23, 0, 59, 99, 23, 0, 59, 99, 0],
            [23, 0, 59, 99, 23, 59, 0, 0, -1],
            [23, 0, 59, 99, 23, 59, 0, 99, -1],
            [23, 0, 59, 99, 23, 59, 59, 0, -1],
            [23, 0, 59, 99, 23, 59, 59, 99, -1],
            [23, 59, 0, 0, 0, 0, 0, 0, 1],
            [23, 59, 0, 0, 0, 0, 0, 99, 1],
            [23, 59, 0, 0, 0, 0, 59, 0, 1],
            [23, 59, 0, 0, 0, 0, 59, 99, 1],
            [23, 59, 0, 0, 0, 59, 0, 0, 1],
            [23, 59, 0, 0, 0, 59, 0, 99, 1],
            [23, 59, 0, 0, 0, 59, 59, 0, 1],
            [23, 59, 0, 0, 0, 59, 59, 99, 1],
            [23, 59, 0, 0, 23, 0, 0, 0, 1],
            [23, 59, 0, 0, 23, 0, 0, 99, 1],
            [23, 59, 0, 0, 23, 0, 59, 0, 1],
            [23, 59, 0, 0, 23, 0, 59, 99, 1],
            [23, 59, 0, 0, 23, 59, 0, 0, 0],
            [23, 59, 0, 0, 23, 59, 0, 99, -1],
            [23, 59, 0, 0, 23, 59, 59, 0, -1],
            [23, 59, 0, 0, 23, 59, 59, 99, -1],
            [23, 59, 0, 99, 0, 0, 0, 0, 1],
            [23, 59, 0, 99, 0, 0, 0, 99, 1],
            [23, 59, 0, 99, 0, 0, 59, 0, 1],
            [23, 59, 0, 99, 0, 0, 59, 99, 1],
            [23, 59, 0, 99, 0, 59, 0, 0, 1],
            [23, 59, 0, 99, 0, 59, 0, 99, 1],
            [23, 59, 0, 99, 0, 59, 59, 0, 1],
            [23, 59, 0, 99, 0, 59, 59, 99, 1],
            [23, 59, 0, 99, 23, 0, 0, 0, 1],
            [23, 59, 0, 99, 23, 0, 0, 99, 1],
            [23, 59, 0, 99, 23, 0, 59, 0, 1],
            [23, 59, 0, 99, 23, 0, 59, 99, 1],
            [23, 59, 0, 99, 23, 59, 0, 0, 1],
            [23, 59, 0, 99, 23, 59, 0, 99, 0],
            [23, 59, 0, 99, 23, 59, 59, 0, -1],
            [23, 59, 0, 99, 23, 59, 59, 99, -1],
            [23, 59, 59, 0, 0, 0, 0, 0, 1],
            [23, 59, 59, 0, 0, 0, 0, 99, 1],
            [23, 59, 59, 0, 0, 0, 59, 0, 1],
            [23, 59, 59, 0, 0, 0, 59, 99, 1],
            [23, 59, 59, 0, 0, 59, 0, 0, 1],
            [23, 59, 59, 0, 0, 59, 0, 99, 1],
            [23, 59, 59, 0, 0, 59, 59, 0, 1],
            [23, 59, 59, 0, 0, 59, 59, 99, 1],
            [23, 59, 59, 0, 23, 0, 0, 0, 1],
            [23, 59, 59, 0, 23, 0, 0, 99, 1],
            [23, 59, 59, 0, 23, 0, 59, 0, 1],
            [23, 59, 59, 0, 23, 0, 59, 99, 1],
            [23, 59, 59, 0, 23, 59, 0, 0, 1],
            [23, 59, 59, 0, 23, 59, 0, 99, 1],
            [23, 59, 59, 0, 23, 59, 59, 0, 0],
            [23, 59, 59, 0, 23, 59, 59, 99, -1],
            [23, 59, 59, 99, 0, 0, 0, 0, 1],
            [23, 59, 59, 99, 0, 0, 0, 99, 1],
            [23, 59, 59, 99, 0, 0, 59, 0, 1],
            [23, 59, 59, 99, 0, 0, 59, 99, 1],
            [23, 59, 59, 99, 0, 59, 0, 0, 1],
            [23, 59, 59, 99, 0, 59, 0, 99, 1],
            [23, 59, 59, 99, 0, 59, 59, 0, 1],
            [23, 59, 59, 99, 0, 59, 59, 99, 1],
            [23, 59, 59, 99, 23, 0, 0, 0, 1],
            [23, 59, 59, 99, 23, 0, 0, 99, 1],
            [23, 59, 59, 99, 23, 0, 59, 0, 1],
            [23, 59, 59, 99, 23, 0, 59, 99, 1],
            [23, 59, 59, 99, 23, 59, 0, 0, 1],
            [23, 59, 59, 99, 23, 59, 0, 99, 1],
            [23, 59, 59, 99, 23, 59, 59, 0, 1],
            [23, 59, 59, 99, 23, 59, 59, 99, 0],
        ];
    }

    public function testAtDate()
    {
        $time = LocalTime::of(12, 34, 56, 789);
        $date = LocalDate::of(2014, 11, 30);

        $this->assertLocalDateTimeIs(2014, 11, 30, 12, 34, 56, 789, $time->atDate($date));
    }

    /**
     * @dataProvider providerToSecondOfDay
     *
     * @param integer $hour
     * @param integer $minute
     * @param integer $second
     * @param integer $result
     */
    public function testToSecondOfDay($hour, $minute, $second, $result)
    {
        $time = LocalTime::of($hour, $minute, $second);

        $this->assertSame($result, $time->toSecondOfDay());
        $this->assertSame($result, $time->withNano(123)->toSecondOfDay());
    }

    /**
     * @return array
     */
    public function providerToSecondOfDay()
    {
        return [
            [0, 0, 0, 0],
            [1, 0, 0, 3600],
            [0, 1, 0, 60],
            [0, 0, 1, 1],
            [12, 34, 56, 45296],
            [23, 59, 59, 86399]
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param integer $h The hour.
     * @param integer $m The minute.
     * @param integer $s The second.
     * @param integer $n The nanosecond.
     * @param integer $r The expected result.
     */
    public function testToString($h, $m, $s, $n, $r)
    {
        $this->assertSame($r, (string) LocalTime::of($h, $m, $s, $n));
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [1, 2, 0, 0, '01:02'],
            [1, 2, 3, 0, '01:02:03'],
            [1, 2, 3, 4, '01:02:03.000000004'],
            [1, 2, 0, 3, '01:02:00.000000003'],
            [12, 34, 56, 789000000, '12:34:56.789'],
            [12, 34, 56, 78900000, '12:34:56.0789'],
        ];
    }

    public function testMinMaxOf()
    {
        $a = LocalTime::of(11, 45);
        $b = LocalTime::of(14, 30);
        $c = LocalTime::of(17, 15);

        $this->assertSame($a, LocalTime::minOf([$a, $b, $c]));
        $this->assertSame($c, LocalTime::maxOf([$a, $b, $c]));
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testMinOfEmptyArrayThrowsException()
    {
        LocalTime::minOf([]);
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testMaxOfEmptyArrayThrowsException()
    {
        LocalTime::maxOf([]);
    }
}
