<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\LocalTime;

/**
 * Unit tests for class LocalTime.
 */
class LocalTimeTest extends AbstractTestCase
{
    public function testMidnight()
    {
        $this->assertLocalTimeEquals(0, 0, 0, 0, LocalTime::midnight());
    }

    public function testNoon()
    {
        $this->assertLocalTimeEquals(12, 0, 0, 0, LocalTime::noon());
    }

    public function testMin()
    {
        $this->assertLocalTimeEquals(0, 0, 0, 0, LocalTime::min());
    }

    public function testMax()
    {
        $this->assertLocalTimeEquals(23, 59, 59, 999999999, LocalTime::max());
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
        $localTime = LocalTime::ofSecondOfDay($secondOfDay, 123456789);
        $this->assertLocalTimeEquals($hour, $minute, $second, 123456789, $localTime);
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

    public function testGetHourMinuteSecond()
    {
        $date = LocalTime::of(23, 29, 59);

        $this->assertSame(23, $date->getHour());
        $this->assertSame(29, $date->getMinute());
        $this->assertSame(59, $date->getSecond());
    }

    public function testCompare()
    {
        $a = LocalTime::of(12, 30, 45);
        $b = LocalTime::of(23, 30, 00);

        $this->assertLessThan(0, $a->compareTo($b));
        $this->assertGreaterThan(0, $b->compareTo($a));
        $this->assertSame(0, $a->compareTo($a));
        $this->assertSame(0, $b->compareTo($b));
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

    /**
     * @dataProvider providerPlusHours
     *
     * @param integer $hoursToAdd
     * @param integer $expectedHour
     */
    public function testPlusHours($hoursToAdd, $expectedHour)
    {
        $time = LocalTime::of(14, 0);

        $actual = $time->plusHours($hoursToAdd);
        $expected = LocalTime::of($expectedHour, 0);

        $this->assertTrue($actual->isEqualTo($expected));
    }

    /**
     * @return array
     */
    public function providerPlusHours()
    {
        return [
            [-25, 13],
            [-24, 14],
            [-23, 15],
            [-15, 23],
            [-14, 0],
            [-13, 1],
            [-1, 13],
            [0, 14],
            [1, 15],
            [9, 23],
            [10, 0],
            [11, 1],
            [23, 13],
            [24, 14],
            [25, 15]
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param integer $minutesToAdd
     * @param integer $expectedHour
     * @param integer $expectedMinute
     */
    public function testPlusMinutes($minutesToAdd, $expectedHour, $expectedMinute)
    {
        $time = LocalTime::of(12, 45);

        $actual = $time->plusMinutes($minutesToAdd);
        $expected = LocalTime::of($expectedHour, $expectedMinute);

        $this->assertTrue($actual->isEqualTo($expected));
    }

    /**
     * @return array
     */
    public function providerPlusMinutes()
    {
        return [
            [-1441, 12, 44],
            [-1440, 12, 45],
            [-1439, 12, 46],
            [-766, 23, 59],
            [-765, 0, 0],
            [-764, 0, 1],
            [-1, 12, 44],
            [0, 12, 45],
            [1, 12, 46],
            [674, 23, 59],
            [675, 0, 0],
            [676, 0, 1],
            [1439, 12, 44],
            [1440, 12, 45],
            [1441, 12, 46]
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param integer $secondsToAdd
     * @param integer $expectedHour
     * @param integer $expectedMinute
     * @param integer $expectedSecond
     */
    public function testPlusSeconds($secondsToAdd, $expectedHour, $expectedMinute, $expectedSecond)
    {
        $time = LocalTime::of(15, 30, 45);

        $actual = $time->plusSeconds($secondsToAdd);
        $expected = LocalTime::of($expectedHour, $expectedMinute, $expectedSecond);

        $this->assertTrue($actual->isEqualTo($expected));
    }

    /**
     * @return array
     */
    public function providerPlusSeconds()
    {
        return [
            [-86401, 15, 30, 44],
            [-86400, 15, 30, 45],
            [-86399, 15, 30, 46],
            [-55846, 23, 59, 59],
            [-55845, 0, 0, 0],
            [-55844, 0, 0, 1],
            [-1, 15, 30, 44],
            [0, 15, 30, 45],
            [1, 15, 30, 46],
            [30554, 23, 59, 59],
            [30555, 0, 0, 0],
            [30556, 0, 0, 1],
            [86399, 15, 30, 44],
            [86400, 15, 30, 45],
            [86401, 15, 30, 46]
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
     * @param integer $eh The expected hour.
     * @param integer $em The expected minute.
     * @param integer $es The expected second.
     * @param integer $en The expected nanosecond.
     */
    public function testPlusNanos($h, $m, $s, $n, $d, $eh, $em, $es, $en)
    {
        $time = LocalTime::of($h, $m, $s, $n)->plusNanos($d);

        $this->assertSame($eh, $time->getHour());
        $this->assertSame($em, $time->getMinute());
        $this->assertSame($es, $time->getSecond());
        $this->assertSame($en, $time->getNano());
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
     * @dataProvider providerMinusHours
     *
     * @param integer $hoursToSubtract
     * @param integer $expectedHour
     */
    public function testMinusHours($hoursToSubtract, $expectedHour)
    {
        $time = LocalTime::of(14, 0);

        $actual = $time->minusHours($hoursToSubtract);
        $expected = LocalTime::of($expectedHour, 0);

        $this->assertTrue($actual->isEqualTo($expected));
    }

    /**
     * @return array
     */
    public function providerMinusHours()
    {
        return [
            [-25, 15],
            [-24, 14],
            [-23, 13],
            [-11, 1],
            [-10, 0],
            [-9, 23],
            [-1, 15],
            [0, 14],
            [1, 13],
            [13, 1],
            [14, 0],
            [15, 23],
            [23, 15],
            [24, 14],
            [25, 13]
        ];
    }

    /**
     * @dataProvider providerMinusMinutes
     *
     * @param integer $minutesToSubtract
     * @param integer $expectedHour
     * @param integer $expectedMinute
     */
    public function testMinusMinutes($minutesToSubtract, $expectedHour, $expectedMinute)
    {
        $time = LocalTime::of(12, 45);

        $actual = $time->minusMinutes($minutesToSubtract);
        $expected = LocalTime::of($expectedHour, $expectedMinute);

        $this->assertTrue($actual->isEqualTo($expected));
    }

    /**
     * @return array
     */
    public function providerMinusMinutes()
    {
        return [
            [-1441, 12, 46],
            [-1440, 12, 45],
            [-1439, 12, 44],
            [-676, 0, 1],
            [-675, 0, 0],
            [-674, 23, 59],
            [-1, 12, 46],
            [0, 12, 45],
            [1, 12, 44],
            [764, 0, 1],
            [765, 0, 0],
            [766, 23, 59],
            [1439, 12, 46],
            [1440, 12, 45],
            [1441, 12, 44]
        ];
    }

    /**
     * @dataProvider providerMinusSeconds
     *
     * @param integer $secondsToSubtract
     * @param integer $expectedHour
     * @param integer $expectedMinute
     * @param integer $expectedSecond
     */
    public function testMinusSeconds($secondsToSubtract, $expectedHour, $expectedMinute, $expectedSecond)
    {
        $time = LocalTime::of(15, 30, 45);

        $actual = $time->minusSeconds($secondsToSubtract);
        $expected = LocalTime::of($expectedHour, $expectedMinute, $expectedSecond);

        $this->assertTrue($actual->isEqualTo($expected));
    }

    /**
     * @return array
     */
    public function providerMinusSeconds()
    {
        return [
            [-86401, 15, 30, 46],
            [-86400, 15, 30, 45],
            [-86399, 15, 30, 44],
            [-30556, 0, 0, 1],
            [-30555, 0, 0, 0],
            [-30554, 23, 59, 59],
            [-1, 15, 30, 46],
            [0, 15, 30, 45],
            [1, 15, 30, 44],
            [55844, 0, 0, 1],
            [55845, 0, 0, 0],
            [55846, 23, 59, 59],
            [86399, 15, 30, 46],
            [86400, 15, 30, 45],
            [86401, 15, 30, 44]
        ];
    }

    /**
     * @dataProvider providerMinusNanos
     *
     * @param integer $h  The base hour.
     * @param integer $m  The base minute.
     * @param integer $s  The base second.
     * @param integer $n  The base nanosecond.
     * @param integer $d  The nanoseconds to add.
     * @param integer $eh The expected hour.
     * @param integer $em The expected minute.
     * @param integer $es The expected second.
     * @param integer $en The expected nanosecond.
     */
    public function testMinusNanos($h, $m, $s, $n, $d, $eh, $em, $es, $en)
    {
        $time = LocalTime::of($h, $m, $s, $n)->minusNanos($d);

        $this->assertSame($eh, $time->getHour());
        $this->assertSame($em, $time->getMinute());
        $this->assertSame($es, $time->getSecond());
        $this->assertSame($en, $time->getNano());
    }

    /**
     * @return array
     */
    public function providerMinusNanos()
    {
        return [
            [0, 0, 0, 1, 1999999999, 23, 59, 58, 2],
            [0, 0, 0, 1, 999999999, 23, 59, 59, 2],
            [0, 0, 0, 1, 1, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 0, 0, 0, 1],
            [0, 0, 0, 1, -1, 0, 0, 0, 2],
            [0, 0, 0, 1, 999999999, 23, 59, 59, 2],
            [0, 0, 0, 1, 1999999999, 23, 59, 58, 2],
            [23, 59, 59, 999999999, 1999999999, 23, 59, 58, 0],
            [23, 59, 59, 999999999, 999999999, 23, 59, 59, 0],
            [23, 59, 59, 999999999, 1, 23, 59, 59, 999999998],
            [23, 59, 59, 999999999, 0, 23, 59, 59, 999999999],
            [23, 59, 59, 999999999, -1, 0, 0, 0, 0],
            [23, 59, 59, 999999999, -999999999, 0, 0, 0, 999999998],
            [23, 59, 59, 999999999, -1999999999, 0, 0, 1, 999999998]
        ];
    }

    public function testMinMaxOf()
    {
        $a = LocalTime::of(11, 45);
        $b = LocalTime::of(14, 30);
        $c = LocalTime::of(17, 15);

        $this->assertTrue(LocalTime::minOf([$a, $b, $c])->isEqualTo($a));
        $this->assertTrue(LocalTime::maxOf([$a, $b, $c])->isEqualTo($c));
    }
}
