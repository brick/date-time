<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Clock\Clock;
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * Unit tests for class Instant.
 */
class InstantTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     *
     * @param integer $seconds         The duration in seconds.
     * @param integer $nanoAdjustment  The nanoseconds adjustement to the duration.
     * @param integer $expectedSeconds The expected adjusted duration seconds.
     * @param integer $expectedNanos   The expected adjusted duration nanoseconds.
     */
    public function testOf($seconds, $nanoAdjustment, $expectedSeconds, $expectedNanos)
    {
        $duration = Instant::of($seconds, $nanoAdjustment);

        $this->assertSame($expectedSeconds, $duration->getEpochSecond());
        $this->assertSame($expectedNanos, $duration->getNano());
    }

    /**
     * @return array
     */
    public function providerOf()
    {
        return [
            [3, 1, 3, 1],
            [4, -999999999, 3, 1],
            [2, 1000000001, 3, 1],
            [-3, 1, -3, 1],
            [-4, 1000000001, -3, 1],
            [-2, -999999999, -3, 1],
            [1, -1000000001, -1, 999999999],
            [-1, -1000000001, -3, 999999999]
        ];
    }

    public function testEpoch()
    {
        $this->assertReadableInstantEquals(0, 0, Instant::epoch());
    }

    public function testNow()
    {
        Clock::setDefault(new FixedClock(Instant::of(123456789, 987654321)));
        $this->assertReadableInstantEquals(123456789, 987654321, Instant::now());
    }

    public function testMin()
    {
        $this->assertReadableInstantEquals(~PHP_INT_MAX, 0, Instant::min());
    }

    public function testMax()
    {
        $this->assertReadableInstantEquals(PHP_INT_MAX, 999999999, Instant::max());
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusSeconds    The seconds of the duration to add.
     * @param integer $plusNanos      The nanos of the duration to add.
     * @param integer $expectedSecond The expected second of the result.
     * @param integer $expectedNano   The expected nano of the result.
     */
    public function testPlus($second, $nano, $plusSeconds, $plusNanos, $expectedSecond, $expectedNano)
    {
        $result = Instant::of($second, $nano)->plus(Duration::ofSeconds($plusSeconds, $plusNanos));
        $this->assertReadableInstantEquals($expectedSecond, $expectedNano, $result);
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusSeconds    The seconds of the duration to add.
     * @param integer $plusNanos      The nanos of the duration to add.
     * @param integer $expectedSecond The expected second of the result.
     * @param integer $expectedNano   The expected nano of the result.
     */
    public function testMinus($second, $nano, $plusSeconds, $plusNanos, $expectedSecond, $expectedNano)
    {
        $result = Instant::of($second, $nano)->minus(Duration::ofSeconds(-$plusSeconds, -$plusNanos));
        $this->assertReadableInstantEquals($expectedSecond, $expectedNano, $result);
    }

    /**
     * @return array
     */
    public function providerPlus()
    {
        return [
            [123456, 789, 0, 0, 123456, 789],
            [123456, 789, 123, 456789, 123579, 457578],
            [123456, 789, -123, -456789, 123332, 999544000],
            [123456789, 999999999, 1, 1, 123456791, 0],
            [123456789, 0, -1, -1, 123456787, 999999999]
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusSeconds    The number of seconds to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testPlusSeconds($second, $nano, $plusSeconds, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusSeconds($plusSeconds);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusSeconds    The number of seconds to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testMinusSeconds($second, $nano, $plusSeconds, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusSeconds(-$plusSeconds);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds()
    {
        return [
            [123456, 789, 0, 123456, 789],
            [123456, 789, 1000, 124456],
            [123456, 789, -1000, 122456],
            [123456, 123456789, 456, 123912],
            [123456, 123456789, -456, 123000],
            [123456, 987, 1000000, 1123456],
            [123456, 987, -1000000, -876544],
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusMinutes    The number of minutes to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testPlusMinutes($second, $nano, $plusMinutes, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusMinutes($plusMinutes);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusMinutes    The number of minutes to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testMinusMinutes($second, $nano, $plusMinutes, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusMinutes(-$plusMinutes);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes()
    {
        return [
            [123456, 789, 0, 123456, 789],
            [123456, 789, 1000, 183456],
            [123456, 789, -1000, 63456],
            [123456, 123456789, 456, 150816],
            [123456, 123456789, -456, 96096],
            [123456, 987, 1000000, 60123456],
            [123456, 987, -1000000, -59876544],
        ];
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusHours      The number of hours to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testPlusHours($second, $nano, $plusHours, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusHours($plusHours);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusHours      The number of hours to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testMinusHours($second, $nano, $plusHours, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusHours(-$plusHours);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusHours()
    {
        return [
            [123456, 789, 0, 123456, 789],
            [123456, 789, 1000, 3723456],
            [123456, 789, -1000, -3476544],
            [123456, 123456789, 456, 1765056],
            [123456, 123456789, -456, -1518144],
            [123456, 987, 1, 127056],
            [123456, 987, -1, 119856],
        ];
    }

    /**
     * @dataProvider providerPlusDays
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusDays       The number of days to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testPlusDays($second, $nano, $plusDays, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusDays($plusDays);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusDays
     *
     * @param integer $second         The base second.
     * @param integer $nano           The base nano-of-second.
     * @param integer $plusDays       The number of days to add.
     * @param integer $expectedSecond The expected second of the result.
     */
    public function testMinusDays($second, $nano, $plusDays, $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusDays(-$plusDays);
        $this->assertReadableInstantEquals($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusDays()
    {
        return [
            [123456, 789, 0, 123456, 789],
            [123456, 789, 1000, 86523456],
            [123456, 789, -1000, -86276544],
            [123456, 123456789, 456, 39521856],
            [123456, 123456789, -456, -39274944],
            [123456, 987, 1, 209856],
            [123456, 987, -1, 37056],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $s1  The epoch second of the 1st instant.
     * @param integer $n1  The nanosecond adjustment of the 1st instant.
     * @param integer $s2  The epoch second of the 2nd instant.
     * @param integer $n2  The nanosecond adjustment of the 2nd instant.
     * @param integer $cmp The expected comparison value.
     */
    public function testCompareTo($s1, $n1, $s2, $n2, $cmp)
    {
        $this->assertSame($cmp, Instant::of($s1, $n1)->compareTo(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $s1  The epoch second of the 1st instant.
     * @param integer $n1  The nanosecond adjustment of the 1st instant.
     * @param integer $s2  The epoch second of the 2nd instant.
     * @param integer $n2  The nanosecond adjustment of the 2nd instant.
     * @param integer $cmp The comparison value.
     */
    public function testIsEqualTo($s1, $n1, $s2, $n2, $cmp)
    {
        $this->assertSame($cmp === 0, Instant::of($s1, $n1)->isEqualTo(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $s1  The epoch second of the 1st instant.
     * @param integer $n1  The nanosecond adjustment of the 1st instant.
     * @param integer $s2  The epoch second of the 2nd instant.
     * @param integer $n2  The nanosecond adjustment of the 2nd instant.
     * @param integer $cmp The comparison value.
     */
    public function testIsAfter($s1, $n1, $s2, $n2, $cmp)
    {
        $this->assertSame($cmp === 1, Instant::of($s1, $n1)->isAfter(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $s1  The epoch second of the 1st instant.
     * @param integer $n1  The nanosecond adjustment of the 1st instant.
     * @param integer $s2  The epoch second of the 2nd instant.
     * @param integer $n2  The nanosecond adjustment of the 2nd instant.
     * @param integer $cmp The comparison value.
     */
    public function testIsBefore($s1, $n1, $s2, $n2, $cmp)
    {
        $this->assertSame($cmp === -1, Instant::of($s1, $n1)->isBefore(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $testSecond The second of the test instant.
     * @param integer $testNano   The nanosecond adjustment to the test instant.
     * @param integer $nowSecond  The second of the current time.
     * @param integer $nowNano    The nanosecond adjustment to the current time.
     * @param integer $cmp        The comparison value.
     */
    public function testIsFuture($testSecond, $testNano, $nowSecond, $nowNano, $cmp)
    {
        Clock::setDefault(new FixedClock(Instant::of($nowSecond, $nowNano)));
        $this->assertSame($cmp === 1, Instant::of($testSecond, $testNano)->isFuture());
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param integer $testSecond The second of the test instant.
     * @param integer $testNano   The nanosecond adjustment to the test instant.
     * @param integer $nowSecond  The second of the current time.
     * @param integer $nowNano    The nanosecond adjustment to the current time.
     * @param integer $cmp        The comparison value.
     */
    public function testIsPast($testSecond, $testNano, $nowSecond, $nowNano, $cmp)
    {
        Clock::setDefault(new FixedClock(Instant::of($nowSecond, $nowNano)));
        $this->assertSame($cmp === -1, Instant::of($testSecond, $testNano)->isPast());
    }

    /**
     * @return array
     */
    public function providerCompareTo()
    {
        return [
            [-1, -1, -1, -1,  0],
            [-1, -1, -1,  0, -1],
            [-1, -1, -1,  1, -1],
            [-1, -1,  0, -1, -1],
            [-1, -1,  0,  0, -1],
            [-1, -1,  0,  1, -1],
            [-1, -1,  1, -1, -1],
            [-1, -1,  1,  0, -1],
            [-1, -1,  1,  1, -1],
            [-1,  0, -1, -1,  1],
            [-1,  0, -1,  0,  0],
            [-1,  0, -1,  1, -1],
            [-1,  0,  0, -1, -1],
            [-1,  0,  0,  0, -1],
            [-1,  0,  0,  1, -1],
            [-1,  0,  1, -1, -1],
            [-1,  0,  1,  0, -1],
            [-1,  0,  1,  1, -1],
            [-1,  1, -1, -1,  1],
            [-1,  1, -1,  0,  1],
            [-1,  1, -1,  1,  0],
            [-1,  1,  0, -1, -1],
            [-1,  1,  0,  0, -1],
            [-1,  1,  0,  1, -1],
            [-1,  1,  1, -1, -1],
            [-1,  1,  1,  0, -1],
            [-1,  1,  1,  1, -1],
            [ 0, -1, -1, -1,  1],
            [ 0, -1, -1,  0,  1],
            [ 0, -1, -1,  1,  1],
            [ 0, -1,  0, -1,  0],
            [ 0, -1,  0,  0, -1],
            [ 0, -1,  0,  1, -1],
            [ 0, -1,  1, -1, -1],
            [ 0, -1,  1,  0, -1],
            [ 0, -1,  1,  1, -1],
            [ 0,  0, -1, -1,  1],
            [ 0,  0, -1,  0,  1],
            [ 0,  0, -1,  1,  1],
            [ 0,  0,  0, -1,  1],
            [ 0,  0,  0,  0,  0],
            [ 0,  0,  0,  1, -1],
            [ 0,  0,  1, -1, -1],
            [ 0,  0,  1,  0, -1],
            [ 0,  0,  1,  1, -1],
            [ 0,  1, -1, -1,  1],
            [ 0,  1, -1,  0,  1],
            [ 0,  1, -1,  1,  1],
            [ 0,  1,  0, -1,  1],
            [ 0,  1,  0,  0,  1],
            [ 0,  1,  0,  1,  0],
            [ 0,  1,  1, -1, -1],
            [ 0,  1,  1,  0, -1],
            [ 0,  1,  1,  1, -1],
            [ 1, -1, -1, -1,  1],
            [ 1, -1, -1,  0,  1],
            [ 1, -1, -1,  1,  1],
            [ 1, -1,  0, -1,  1],
            [ 1, -1,  0,  0,  1],
            [ 1, -1,  0,  1,  1],
            [ 1, -1,  1, -1,  0],
            [ 1, -1,  1,  0, -1],
            [ 1, -1,  1,  1, -1],
            [ 1,  0, -1, -1,  1],
            [ 1,  0, -1,  0,  1],
            [ 1,  0, -1,  1,  1],
            [ 1,  0,  0, -1,  1],
            [ 1,  0,  0,  0,  1],
            [ 1,  0,  0,  1,  1],
            [ 1,  0,  1, -1,  1],
            [ 1,  0,  1,  0,  0],
            [ 1,  0,  1,  1, -1],
            [ 1,  1, -1, -1,  1],
            [ 1,  1, -1,  0,  1],
            [ 1,  1, -1,  1,  1],
            [ 1,  1,  0, -1,  1],
            [ 1,  1,  0,  0,  1],
            [ 1,  1,  0,  1,  1],
            [ 1,  1,  1, -1,  1],
            [ 1,  1,  1,  0,  1],
            [ 1,  1,  1,  1,  0],
        ];
    }

    public function testGetInstant()
    {
        $instant = Instant::of(987654321, 123456789);
        $this->assertSame($instant, $instant->getInstant());
    }

    /**
     * @dataProvider providerToString
     *
     * @param integer $epochSecond    The epoch second to test.
     * @param integer $nano           The nano adjustment to the epoch second.
     * @param string  $expectedString The expected string output.
     */
    public function testToString($epochSecond, $nano, $expectedString)
    {
        $this->assertSame($expectedString, (string) Instant::of($epochSecond, $nano));
    }

    /**
     * @return array
     */
    public function providerToString()
    {
        return [
            [-2000000000, 0, '1906-08-16T20:26:40Z'],
            [-1, 0, '1969-12-31T23:59:59Z'],
            [-1, 123, '1969-12-31T23:59:59.000000123Z'],
            [0, 0, '1970-01-01T00:00Z'],
            [0, 123456, '1970-01-01T00:00:00.000123456Z'],
            [1, 0, '1970-01-01T00:00:01Z'],
            [1, 123456789, '1970-01-01T00:00:01.123456789Z'],
            [2000000000, 0, '2033-05-18T03:33:20Z'],
        ];
    }
}
