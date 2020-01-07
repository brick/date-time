<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\Duration;
use Brick\DateTime\Instant;
use Brick\DateTime\TimeZone;

/**
 * Unit tests for class Instant.
 */
class InstantTest extends AbstractTestCase
{
    /**
     * @dataProvider providerOf
     *
     * @param int $seconds         The duration in seconds.
     * @param int $nanoAdjustment  The nanoseconds adjustement to the duration.
     * @param int $expectedSeconds The expected adjusted duration seconds.
     * @param int $expectedNanos   The expected adjusted duration nanoseconds.
     */
    public function testOf(int $seconds, int $nanoAdjustment, int $expectedSeconds, int $expectedNanos)
    {
        $duration = Instant::of($seconds, $nanoAdjustment);

        $this->assertSame($expectedSeconds, $duration->getEpochSecond());
        $this->assertSame($expectedNanos, $duration->getNano());
    }

    /**
     * @return array
     */
    public function providerOf() : array
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
        $this->assertInstantIs(0, 0, Instant::epoch());
    }

    public function testNow()
    {
        $clock = new FixedClock(Instant::of(123456789, 987654321));
        $this->assertInstantIs(123456789, 987654321, Instant::now($clock));
    }

    public function testMin()
    {
        $this->assertInstantIs(~\PHP_INT_MAX, 0, Instant::min());
    }

    public function testMax()
    {
        $this->assertInstantIs(\PHP_INT_MAX, 999999999, Instant::max());
    }

    /**
     * @dataProvider providerPlus
     *
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusSeconds    The seconds of the duration to add.
     * @param int $plusNanos      The nanos of the duration to add.
     * @param int $expectedSecond The expected second of the result.
     * @param int $expectedNano   The expected nano of the result.
     */
    public function testPlus(int $second, int $nano, int $plusSeconds, int $plusNanos, int $expectedSecond, int $expectedNano)
    {
        $result = Instant::of($second, $nano)->plus(Duration::ofSeconds($plusSeconds, $plusNanos));
        $this->assertInstantIs($expectedSecond, $expectedNano, $result);
    }

    /**
     * @dataProvider providerPlus
     *
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusSeconds    The seconds of the duration to add.
     * @param int $plusNanos      The nanos of the duration to add.
     * @param int $expectedSecond The expected second of the result.
     * @param int $expectedNano   The expected nano of the result.
     */
    public function testMinus(int $second, int $nano, int $plusSeconds, int $plusNanos, int $expectedSecond, int $expectedNano)
    {
        $result = Instant::of($second, $nano)->minus(Duration::ofSeconds(-$plusSeconds, -$plusNanos));
        $this->assertInstantIs($expectedSecond, $expectedNano, $result);
    }

    /**
     * @return array
     */
    public function providerPlus() : array
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
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusSeconds    The number of seconds to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testPlusSeconds(int $second, int $nano, int $plusSeconds, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusSeconds($plusSeconds);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusSeconds    The number of seconds to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testMinusSeconds(int $second, int $nano, int $plusSeconds, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusSeconds(-$plusSeconds);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds() : array
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
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusMinutes    The number of minutes to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testPlusMinutes(int $second, int $nano, int $plusMinutes, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusMinutes($plusMinutes);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusMinutes    The number of minutes to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testMinusMinutes(int $second, int $nano, int $plusMinutes, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusMinutes(-$plusMinutes);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes() : array
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
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusHours      The number of hours to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testPlusHours(int $second, int $nano, int $plusHours, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusHours($plusHours);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusHours      The number of hours to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testMinusHours(int $second, int $nano, int $plusHours, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusHours(-$plusHours);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusHours() : array
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
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusDays       The number of days to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testPlusDays(int $second, int $nano, int $plusDays, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->plusDays($plusDays);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @dataProvider providerPlusDays
     *
     * @param int $second         The base second.
     * @param int $nano           The base nano-of-second.
     * @param int $plusDays       The number of days to add.
     * @param int $expectedSecond The expected second of the result.
     */
    public function testMinusDays(int $second, int $nano, int $plusDays, int $expectedSecond)
    {
        $result = Instant::of($second, $nano)->minusDays(-$plusDays);
        $this->assertInstantIs($expectedSecond, $nano, $result);
    }

    /**
     * @return array
     */
    public function providerPlusDays() : array
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

    public function testWithEpochSecond()
    {
        $instant = Instant::of(1234567890, 987654321);
        $this->assertInstantIs(2345678901, 987654321, $instant->withEpochSecond(2345678901));
    }

    public function testWithNano()
    {
        $instant = Instant::of(1234567890, 987654321);
        $this->assertInstantIs(1234567890, 123456789, $instant->withNano(123456789));
    }

    /**
     * @dataProvider providerWithInvalidNanoThrowsException
     *
     * @param int $nano
     */
    public function testWithInvalidNanoThrowsException(int $nano)
    {
        $instant = Instant::of(1234567890, 987654321);

        $this->expectException(DateTimeException::class);
        $instant->withNano($nano);
    }

    /**
     * @return array
     */
    public function providerWithInvalidNanoThrowsException() : array
    {
        return [
            [-1],
            [1000000000]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $s1  The epoch second of the 1st instant.
     * @param int $n1  The nanosecond adjustment of the 1st instant.
     * @param int $s2  The epoch second of the 2nd instant.
     * @param int $n2  The nanosecond adjustment of the 2nd instant.
     * @param int $cmp The expected comparison value.
     */
    public function testCompareTo(int $s1, int $n1, int $s2, int $n2, int $cmp)
    {
        $this->assertSame($cmp, Instant::of($s1, $n1)->compareTo(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $s1  The epoch second of the 1st instant.
     * @param int $n1  The nanosecond adjustment of the 1st instant.
     * @param int $s2  The epoch second of the 2nd instant.
     * @param int $n2  The nanosecond adjustment of the 2nd instant.
     * @param int $cmp The comparison value.
     */
    public function testIsEqualTo(int $s1, int $n1, int $s2, int $n2, int $cmp)
    {
        $this->assertSame($cmp === 0, Instant::of($s1, $n1)->isEqualTo(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $s1  The epoch second of the 1st instant.
     * @param int $n1  The nanosecond adjustment of the 1st instant.
     * @param int $s2  The epoch second of the 2nd instant.
     * @param int $n2  The nanosecond adjustment of the 2nd instant.
     * @param int $cmp The comparison value.
     */
    public function testIsAfter(int $s1, int $n1, int $s2, int $n2, int $cmp)
    {
        $this->assertSame($cmp === 1, Instant::of($s1, $n1)->isAfter(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $s1  The epoch second of the 1st instant.
     * @param int $n1  The nanosecond adjustment of the 1st instant.
     * @param int $s2  The epoch second of the 2nd instant.
     * @param int $n2  The nanosecond adjustment of the 2nd instant.
     * @param int $cmp The comparison value.
     */
    public function testIsAfterOrEqualTo(int $s1, int $n1, int $s2, int $n2, int $cmp)
    {
        $this->assertSame($cmp >= 0, Instant::of($s1, $n1)->isAfterOrEqualTo(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $s1  The epoch second of the 1st instant.
     * @param int $n1  The nanosecond adjustment of the 1st instant.
     * @param int $s2  The epoch second of the 2nd instant.
     * @param int $n2  The nanosecond adjustment of the 2nd instant.
     * @param int $cmp The comparison value.
     */
    public function testIsBefore(int $s1, int $n1, int $s2, int $n2, int $cmp)
    {
        $this->assertSame($cmp === -1, Instant::of($s1, $n1)->isBefore(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $s1  The epoch second of the 1st instant.
     * @param int $n1  The nanosecond adjustment of the 1st instant.
     * @param int $s2  The epoch second of the 2nd instant.
     * @param int $n2  The nanosecond adjustment of the 2nd instant.
     * @param int $cmp The comparison value.
     */
    public function testIsBeforeOrEqualTo(int $s1, int $n1, int $s2, int $n2, int $cmp)
    {
        $this->assertSame($cmp <= 0, Instant::of($s1, $n1)->isBeforeOrEqualTo(Instant::of($s2, $n2)));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $testSecond The second of the test instant.
     * @param int $testNano   The nanosecond adjustment to the test instant.
     * @param int $nowSecond  The second of the current time.
     * @param int $nowNano    The nanosecond adjustment to the current time.
     * @param int $cmp        The comparison value.
     */
    public function testIsFuture(int $testSecond, int $testNano, int $nowSecond, int $nowNano, int $cmp)
    {
        $clock = new FixedClock(Instant::of($nowSecond, $nowNano));
        $this->assertSame($cmp === 1, Instant::of($testSecond, $testNano)->isFuture($clock));
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $testSecond The second of the test instant.
     * @param int $testNano   The nanosecond adjustment to the test instant.
     * @param int $nowSecond  The second of the current time.
     * @param int $nowNano    The nanosecond adjustment to the current time.
     * @param int $cmp        The comparison value.
     */
    public function testIsPast(int $testSecond, int $testNano, int $nowSecond, int $nowNano, int $cmp)
    {
        $clock = new FixedClock(Instant::of($nowSecond, $nowNano));
        $this->assertSame($cmp === -1, Instant::of($testSecond, $testNano)->isPast($clock));
    }

    /**
     * @return array
     */
    public function providerCompareTo() : array
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

    /**
     * @dataProvider providerIsBetweenInclusive
     *
     * @param int  $seconds   The seconds value.
     * @param int  $nanos     The nano seconds value.
     * @param bool $isBetween Check the secs and nanos are between.
     */
    public function testIsBetweenInclusive(int $seconds, int $nanos, $isBetween)
    {
        $this->assertSame($isBetween, Instant::of($seconds, $nanos)->isBetweenInclusive(
            Instant::of(-1, -1),
            Instant::of(1, 1)
        ));
    }

    /**
     * @dataProvider providerIsBetweenExclusive
     *
     * @param int  $seconds   The seconds value.
     * @param int  $nanos     The nano seconds value.
     * @param bool $isBetween Check the secs and nanos are between.
     */
    public function testIsBetweenExclusive(int $seconds, int $nanos, $isBetween)
    {
        $this->assertSame($isBetween, Instant::of($seconds, $nanos)->isBetweenExclusive(
            Instant::of(-1, -1),
            Instant::of(1, 1)
        ));
    }

    /**
     * @return array
     */
    public function providerIsBetweenExclusive() : array
    {
        return [
            [-1, -2, false],
            [-1, -1, false],
            [-1,  0, true],
            [-1, 1, true],
            [0, -1, true],
            [0, 0, true],
            [0, 1, true],
            [1, -1, true],
            [1, 0, true],
            [1, 1, false],
            [1, 2, false],
        ];
    }

    /**
     * @return array
     */
    public function providerIsBetweenInclusive() : array
    {
        return [
            [-1, -2, false],
            [-1, -1, true],
            [-1,  0, true],
            [-1, 1, true],
            [0, -1, true],
            [0, 0, true],
            [0, 1, true],
            [1, -1, true],
            [1, 0, true],
            [1, 1, true],
            [1, 2, false],
        ];
    }

    /**
     * @dataProvider providerToDecimal
     *
     * @param int    $second   The epoch second.
     * @param int    $nano     The nano adjustment.
     * @param string $expected The expected decimal output.
     */
    public function testToDecimal(int $second, int $nano, string $expected)
    {
        $this->assertSame($expected, Instant::of($second, $nano)->toDecimal());
    }

    /**
     * @return array
     */
    public function providerToDecimal()
    {
        return [
            [123456789, 0, '123456789'],
            [123456789, 1, '123456789.000000001'],
            [123456789, 10, '123456789.00000001'],
            [123456789, 100, '123456789.0000001'],
            [123456789, 1000, '123456789.000001'],
            [123456789, 10000, '123456789.00001'],
            [123456789, 100000, '123456789.0001'],
            [123456789, 1000000, '123456789.001'],
            [123456789, 10000000, '123456789.01'],
            [123456789, 100000000, '123456789.1'],
            [123456789, 550000000, '123456789.55'],
        ];
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $epochSecond    The epoch second to test.
     * @param int    $nano           The nano adjustment to the epoch second.
     * @param string $expectedString The expected string output.
     */
    public function testJsonSerialize(int $epochSecond, int $nano, string $expectedString)
    {
        $this->assertSame(json_encode($expectedString), json_encode(Instant::of($epochSecond, $nano)));
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $epochSecond    The epoch second to test.
     * @param int    $nano           The nano adjustment to the epoch second.
     * @param string $expectedString The expected string output.
     */
    public function testToString(int $epochSecond, int $nano, string $expectedString)
    {
        $this->assertSame($expectedString, (string) Instant::of($epochSecond, $nano));
    }

    /**
     * @return array
     */
    public function providerToString() : array
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

    public function testAtTimeZone()
    {
        $timeZone = TimeZone::utc();
        $instant = Instant::of(1000000000);
        $result = $instant->atTimeZone($timeZone);
        $this->assertSame(1000000000, $result->getInstant()->getEpochSecond());
        $this->assertSame('2001-09-09T01:46:40', (string) $result->getDateTime());
    }
}
