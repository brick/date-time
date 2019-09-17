<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * Unit tests for class Duration.
 */
class DurationTest extends AbstractTestCase
{
    public function testZero()
    {
        $this->assertDurationIs(0, 0, Duration::zero());
    }

    /**
     * @dataProvider providerOfSeconds
     *
     * @param int $seconds         The duration in seconds.
     * @param int $nanoAdjustment  The nanoseconds adjustement to the duration.
     * @param int $expectedSeconds The expected adjusted duration seconds.
     * @param int $expectedNanos   The expected adjusted duration nanoseconds.
     */
    public function testOfSeconds(int $seconds, int $nanoAdjustment, int $expectedSeconds, int $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanoAdjustment);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration);
    }

    /**
     * @return array
     */
    public function providerOfSeconds() : array
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

    /**
     * @param int $milliseconds
     * @dataProvider providerOfMilliseconds
     */
    public function testOfMilliseconds(int $milliseconds)
    {
        $duration = Duration::ofMilliseconds($milliseconds);
        $this->assertEquals($milliseconds, $duration->getTotalMillis());
    }

    public function providerOfMilliseconds()
    {
        return [
            [1000],
            [1],
            [55555],
            [-1],
            [-23423423],
        ];
    }

    public function testOfMinutes()
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 60, Duration::ofMinutes($i)->getSeconds());
        }
    }

    public function testOfHours()
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 3600, Duration::ofHours($i)->getSeconds());
        }
    }

    public function testOfDays()
    {
        for ($i = -2; $i <= 2; $i++) {
            $this->assertSame($i * 86400, Duration::ofDays($i)->getSeconds());
        }
    }

    /**
     * @dataProvider providerBetween
     *
     * @param int $seconds1
     * @param int $nanos1
     * @param int $seconds2
     * @param int $nanos2
     * @param int $seconds
     * @param int $nanos
     */
    public function testBetween(int $seconds1, int $nanos1, int $seconds2, int $nanos2, int $seconds, int $nanos)
    {
        $i1 = Instant::of($seconds1, $nanos1);
        $i2 = Instant::of($seconds2, $nanos2);

        $this->assertDurationIs($seconds, $nanos, Duration::between($i1, $i2));
    }

    /**
     * @return array
     */
    public function providerBetween() : array
    {
        return [
            [0, 0, 0, 0, 0, 0],
            [3, 0, 7, 0, 4, 0],
            [7, 0, 3, 0, -4, 0],

            [0, 500000000, 1, 500000000, 1, 0],
            [0, 500000000, 1, 750000000, 1, 250000000],
            [0, 500000000, 1, 250000000, 0, 750000000],

            [-1, 500000000, 0, 0, 0, 500000000],
            [-1, 500000000, 0, 500000000, 1, 0],
            [-1, 999999999, 2, 2, 2, 3],

            [0, 0, -1, 500000000, -1, 500000000],
            [0, 500000000, -1, 500000000, -1, 0],
            [2, 2, -1, 999999999, -3, 999999997],
        ];
    }

    /**
     * @dataProvider providerParse
     *
     * @param string $text    The string to test.
     * @param int    $seconds The expected seconds.
     * @param int    $nanos   The expected nanos.
     */
    public function testParse(string $text, int $seconds, int $nanos)
    {
        $this->assertDurationIs($seconds, $nanos, Duration::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse() : array
    {
        return [
            ['PT0S', 0, 0],
            ['pT-0S', 0, 0],
            ['Pt0S', 0, 0],
            ['PT-0s', 0, 0],

            ['PT0.1S', 0, 100000000],
            ['PT-0.1S', -1, 900000000],

            ['PT1.001S', 1, 1000000],
            ['PT-1.001S', -2, 999000000],
            ['PT-1.999S', -2, 1000000],

            ['PT0.000000001S', 0, 1],
            ['PT-0.000000001S', -1, 999999999],
            ['PT-0.999999999S', -1, 1],

            ['PT-0.999999999S', -1, 1],

            ['PT1S', 1, 0],
            ['PT12S', 12, 0],
            ['PT123456789S', 123456789, 0],

            ['PT-1S', -1, 0],
            ['PT-12S', -12, 0],
            ['PT-123456789S', -123456789, 0],

            ['P1D', 86400, 0],
            ['PT24H', 86400, 0],
            ['PT1440M', 86400, 0],
            ['PT86400S', 86400, 0],
            ['PT86400.0S', 86400, 0],

            ['P1DT1H', 90000, 0],
            ['P1DT1H1M', 90060, 0],
            ['P1DT1H1M1S', 90061, 0],
            ['P1DT1H1M1.1S', 90061, 100000000],

            ['P123D', 10627200, 0],
            ['P123DT456H', 12268800, 0],
            ['P123DT456H789M', 12316140, 0],
            ['P123DT456H789M123S', 12316263, 0],

            ['+P+123D', 10627200, 0],
            ['+P+123DT456H', 12268800, 0],
            ['+P123DT+456H789M', 12316140, 0],
            ['+P123DT456H+789M123S', 12316263, 0],
            ['+P123DT456H789M+123.456789S', 12316263, 456789000],

            ['-P123D', -10627200, 0],
            ['-P123DT456H', -12268800, 0],
            ['-P123DT456H789M', -12316140, 0],
            ['-P123DT456H789M123S', -12316263, 0],

            ['P123DT456H789M123.456789S', 12316263, 456789000],
            ['P+123DT456H789M123.456789S', 12316263, 456789000],
            ['P-123DT456H789M123.456789S', -8938137, 456789000],
            ['P-123DT+456H789M123.456789S', -8938137, 456789000],
            ['P-123DT-456H789M123.456789S', -12221337, 456789000],
            ['P-123DT-456H+789M123.456789S', -12221337, 456789000],
            ['P-123DT-456H-789M123.456789S', -12316017, 456789000],
            ['P-123DT-456H-789M+123.456789S', -12316017, 456789000],
            ['P-123DT-456H-789M-123.456789S', -12316264, 543211000],

            ['-P123DT456H789M123.456789S', -12316264, 543211000],
            ['-P+123DT456H789M123.456789S', -12316264, 543211000],
            ['-P-123DT456H789M123.456789S', 8938136, 543211000],
            ['-P-123DT+456H789M123.456789S', 8938136, 543211000],
            ['-P-123DT-456H789M123.456789S', 12221336, 543211000],
            ['-P-123DT-456H+789M123.456789S', 12221336, 543211000],
            ['-P-123DT-456H-789M123.456789S', 12316016, 543211000],
            ['-P-123DT-456H-789M+123.456789S', 12316016, 543211000],
            ['-P-123DT-456H-789M-123.456789S', 12316263, 456789000],

            ['PT1M0.001S', 60, 1000000],
            ['PT1M-0.001S', 59, 999000000],
            ['PT-1M-0.001S', -61, 999000000],
            ['PT-1M0.001S', -60, 1000000],

            ['-PT1M0.001S', -61, 999000000],
            ['-PT1M-0.001S', -60, 1000000],
            ['-PT-1M-0.001S', 60, 1000000],
            ['-PT-1M0.001S', 59, 999000000]
        ];
    }

    /**
     * @dataProvider providerParseFailureThrowsException
     * @expectedException \Brick\DateTime\Parser\DateTimeParseException
     *
     * @param string $text The string to test.
     */
    public function testParseFailureThrowsException(string $text)
    {
        Duration::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseFailureThrowsException() : array
    {
        return [
            [''],
            ['P'],
            ['-P'],
            ['+P'],
            ['PD'],
            ['PT'],
            ['PDT'],
            ['PD0T'],
            ['-PT'],
            ['+PT'],
            ['P1DT'],
            ['P1DTS'],
            ['PTS'],
            ['PT0'],
            ['PTS'],
            ['PT+S'],
            ['PT-S'],
            ['PT.S'],
            ['XT0S'],
            ['PX0S'],
            ['PT0X'],
            ['PTAS'],
            ['PT1X2S'],

            [' PT0S'],
            ['PT0S '],

            ['PT-.S'],
            ['PT+.S'],
            ['PT0.S'],
            ['PT.0S'],

            ['PT0.1234567890S'],
        ];
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param int $seconds The seconds of the duration.
     * @param int $nanos   The nanos of the duration.
     * @param int $cmp     The comparison value.
     */
    public function testIsZero(int $seconds, int $nanos, int $cmp)
    {
        $this->assertSame($cmp === 0, Duration::ofSeconds($seconds, $nanos)->isZero());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param int $seconds The seconds of the duration.
     * @param int $nanos   The nanos of the duration.
     * @param int $cmp     The comparison value.
     */
    public function testIsPositive(int $seconds, int $nanos, int $cmp)
    {
        $this->assertSame($cmp > 0, Duration::ofSeconds($seconds, $nanos)->isPositive());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param int $seconds The seconds of the duration.
     * @param int $nanos   The nanos of the duration.
     * @param int $cmp     The comparison value.
     */
    public function testIsPositiveOrZero(int $seconds, int $nanos, int $cmp)
    {
        $this->assertSame($cmp >= 0, Duration::ofSeconds($seconds, $nanos)->isPositiveOrZero());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param int $seconds The seconds of the duration.
     * @param int $nanos   The nanos of the duration.
     * @param int $cmp     The comparison value.
     */
    public function testIsNegative(int $seconds, int $nanos, int $cmp)
    {
        $this->assertSame($cmp < 0, Duration::ofSeconds($seconds, $nanos)->isNegative());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param int $seconds The seconds of the duration.
     * @param int $nanos   The nanos of the duration.
     * @param int $cmp     The comparison value.
     */
    public function testIsNegativeOrZero(int $seconds, int $nanos, int $cmp)
    {
        $this->assertSame($cmp <= 0, Duration::ofSeconds($seconds, $nanos)->isNegativeOrZero());
    }

    /**
     * @return array
     */
    public function providerCompareToZero() : array
    {
        return [
            [-1, -1, -1],
            [-1,  0, -1],
            [-1,  1, -1],
            [ 0, -1, -1],
            [ 0,  0,  0],
            [ 0,  1,  1],
            [ 1, -1,  1],
            [ 1,  0,  1],
            [ 1,  1,  1]
        ];
    }

    /**
     * @dataProvider providerCompareTo
     *
     * @param int $seconds1 The seconds of the 1st duration.
     * @param int $nanos1   The nanoseconds of the 1st duration.
     * @param int $seconds2 The seconds of the 2nd duration.
     * @param int $nanos2   The nanoseconds of the 2nd duration.
     * @param int $expected The expected return value.
     */
    public function testCompareTo(int $seconds1, int $nanos1, int $seconds2, int $nanos2, int $expected)
    {
        $duration1 = Duration::ofSeconds($seconds1, $nanos1);
        $duration2 = Duration::ofSeconds($seconds2, $nanos2);

        $this->assertSame($expected, $duration1->compareTo($duration2));
    }

    /**
     * @return array
     */
    public function providerCompareTo() : array
    {
        return [
            [-1, -1, -1, -1, 0],
            [-1, -1, -1, 0, -1],
            [-1, -1, 0, -1, -1],
            [-1, -1, 0, 0, -1],
            [-1, -1, 0, 1, -1],
            [-1, -1, 1, 0, -1],
            [-1, -1, 1, 1, -1],
            [-1, 0, -1, -1, 1],
            [-1, 0, -1, 0, 0],
            [-1, 0, 0, -1, -1],
            [-1, 0, 0, 0, -1],
            [-1, 0, 0, 1, -1],
            [-1, 0, 1, 0, -1],
            [-1, 0, 1, 1, -1],
            [0, -1, -1, -1, 1],
            [0, -1, -1, 0, 1],
            [0, -1, 0, -1, 0],
            [0, -1, 0, 0, -1],
            [0, -1, 0, 1, -1],
            [0, -1, 1, 0, -1],
            [0, -1, 1, 1, -1],
            [0, 0, -1, -1, 1],
            [0, 0, -1, 0, 1],
            [0, 0, 0, -1, 1],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 1, -1],
            [0, 0, 1, 0, -1],
            [0, 0, 1, 1, -1],
            [0, 1, -1, -1, 1],
            [0, 1, -1, 0, 1],
            [0, 1, 0, -1, 1],
            [0, 1, 0, 0, 1],
            [0, 1, 0, 1, 0],
            [0, 1, 1, 0, -1],
            [0, 1, 1, 1, -1],
            [1, 0, -1, -1, 1],
            [1, 0, -1, 0, 1],
            [1, 0, 0, -1, 1],
            [1, 0, 0, 0, 1],
            [1, 0, 0, 1, 1],
            [1, 0, 1, 0, 0],
            [1, 0, 1, 1, -1],
            [1, 1, -1, -1, 1],
            [1, 1, -1, 0, 1],
            [1, 1, 0, -1, 1],
            [1, 1, 0, 0, 1],
            [1, 1, 0, 1, 1],
            [1, 1, 1, 0, 1],
            [1, 1, 1, 1, 0]
        ];
    }

    /**
     * @dataProvider providerPlus
     *
     * @param int $s1 The 1st duration's seconds.
     * @param int $n1 The 1st duration's nanoseconds.
     * @param int $s2 The 2nd duration's seconds.
     * @param int $n2 The 2nd duration's nanoseconds.
     * @param int $s  The expected seconds.
     * @param int $n  The expected nanoseconds.
     */
    public function testPlus(int $s1, int $n1, int $s2, int $n2, int $s, int $n)
    {
        $duration1 = Duration::ofSeconds($s1, $n1);
        $duration2 = Duration::ofSeconds($s2, $n2);

        $this->assertDurationIs($s, $n, $duration1->plus($duration2));
    }

    /**
     * @dataProvider providerPlus
     *
     * @param int $s1 The 1st duration's seconds.
     * @param int $n1 The 1st duration's nanoseconds.
     * @param int $s2 The 2nd duration's seconds.
     * @param int $n2 The 2nd duration's nanoseconds.
     * @param int $s  The expected seconds.
     * @param int $n  The expected nanoseconds.
     */
    public function testMinus(int $s1, int $n1, int $s2, int $n2, int $s, int $n)
    {
        $duration1 = Duration::ofSeconds($s1, $n1);
        $duration2 = Duration::ofSeconds(-$s2, -$n2);

        $this->assertDurationIs($s, $n, $duration1->minus($duration2));
    }

    /**
     * @return array
     */
    public function providerPlus() : array
    {
        return [
            [-1, -1, -1, -1, -3, 999999998],
            [-1, -1, -1, 0, -3, 999999999],
            [-1, -1, 0, -1, -2, 999999998],
            [-1, -1, 0, 0, -2, 999999999],
            [-1, -1, 0, 1, -1, 0],
            [-1, -1, 1, 0, -1, 999999999],
            [-1, -1, 1, 1, 0, 0],
            [-1, 0, -1, -1, -3, 999999999],
            [-1, 0, -1, 0, -2, 0],
            [-1, 0, 0, -1, -2, 999999999],
            [-1, 0, 0, 0, -1, 0],
            [-1, 0, 0, 1, -1, 1],
            [-1, 0, 1, 0, 0, 0],
            [-1, 0, 1, 1, 0, 1],
            [0, -1, -1, -1, -2, 999999998],
            [0, -1, -1, 0, -2, 999999999],
            [0, -1, 0, -1, -1, 999999998],
            [0, -1, 0, 0, -1, 999999999],
            [0, -1, 0, 1, 0, 0],
            [0, -1, 1, 0, 0, 999999999],
            [0, -1, 1, 1, 1, 0],
            [0, 0, -1, -1, -2, 999999999],
            [0, 0, -1, 0, -1, 0],
            [0, 0, 0, -1, -1, 999999999],
            [0, 0, 0, 0, 0, 0],
            [0, 0, 0, 1, 0, 1],
            [0, 0, 1, 0, 1, 0],
            [0, 0, 1, 1, 1, 1],
            [0, 1, -1, -1, -1, 0],
            [0, 1, -1, 0, -1, 1],
            [0, 1, 0, -1, 0, 0],
            [0, 1, 0, 0, 0, 1],
            [0, 1, 0, 1, 0, 2],
            [0, 1, 1, 0, 1, 1],
            [0, 1, 1, 1, 1, 2],
            [1, 0, -1, -1, -1, 999999999],
            [1, 0, -1, 0, 0, 0],
            [1, 0, 0, -1, 0, 999999999],
            [1, 0, 0, 0, 1, 0],
            [1, 0, 0, 1, 1, 1],
            [1, 0, 1, 0, 2, 0],
            [1, 0, 1, 1, 2, 1],
            [1, 1, -1, -1, 0, 0],
            [1, 1, -1, 0, 0, 1],
            [1, 1, 0, -1, 1, 0],
            [1, 1, 0, 0, 1, 1],
            [1, 1, 0, 1, 1, 2],
            [1, 1, 1, 0, 2, 1],
            [1, 1, 1, 1, 2, 2],

            [1, 999999999, 1, 1, 3, 0],
            [1, 999999999, -1, -1, 0, 999999998],
            [-1, -999999999, 1, 999999998, -1, 999999999],
            [-1, -999999999, -1, -1, -3, 0],
        ];
    }

    /**
     * @dataProvider providerPlusSeconds
     *
     * @param int $seconds
     * @param int $nanos
     * @param int $secondsToAdd
     * @param int $expectedSeconds
     * @param int $expectedNanos
     */
    public function testPlusSeconds(int $seconds, int $nanos, int $secondsToAdd, int $expectedSeconds, int $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos)->plusSeconds($secondsToAdd);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds() : array
    {
        return [
            [-1, 0, -1, -2, 0],
            [-1, 0,  0, -1, 0],
            [-1, 0,  1,  0, 0],
            [-1, 0,  2,  1, 0],

            [0, 0, -1, -1, 0],
            [0, 0,  0,  0, 0],
            [0, 0,  1,  1, 0],

            [1, 0, -2, -1, 0],
            [1, 0, -1,  0, 0],
            [1, 0,  0,  1, 0],
            [1, 0,  1,  2, 0],

            [~\PHP_INT_MAX, 0, \PHP_INT_MAX, -1, 0],
            [\PHP_INT_MAX, 0, ~\PHP_INT_MAX, -1, 0],
            [\PHP_INT_MAX, 0, 0, \PHP_INT_MAX, 0],

            [-1, -5,  2, 0,  999999995],
            [ 1,  5, -2, -1, 5],
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param int $seconds
     * @param int $minutesToAdd
     * @param int $expectedSeconds
     */
    public function testPlusMinutes(int $seconds, int $minutesToAdd, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->plusMinutes($minutesToAdd);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes() : array
    {
        return [
            [-1, -1, -61],
            [-1, 0, -1],
            [-1, 1, 59],
            [-1, 2, 119],

            [0, -1, -60],
            [0, 0, 0],
            [0, 1, 60],

            [1, -2, -119],
            [1, -1, -59],
            [1, 0, 1],
            [1, 1, 61]
        ];
    }

    /**
     * @dataProvider providerPlusHours
     *
     * @param int $seconds
     * @param int $hoursToAdd
     * @param int $expectedSeconds
     */
    public function testPlusHours(int $seconds, int $hoursToAdd, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->plusHours($hoursToAdd);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusHours() : array
    {
        return [
            [-1, -1, -3601],
            [-1, 0, -1],
            [-1, 1, 3599],
            [-1, 2, 7199],

            [0, -1, -3600],
            [0, 0, 0],
            [0, 1, 3600],

            [1, -2, -7199],
            [1, -1, -3599],
            [1, 0, 1],
            [1, 1, 3601]
        ];
    }

    /**
     * @dataProvider providerPlusDays
     *
     * @param int $seconds
     * @param int $daysToAdd
     * @param int $expectedSeconds
     */
    public function testPlusDays(int $seconds, int $daysToAdd, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->plusDays($daysToAdd);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusDays() : array
    {
        return [
            [-1, -1, -86401],
            [-1, 0, -1],
            [-1, 1, 86399],
            [-1, 2, 172799],

            [0, -1, -86400],
            [0, 0, 0],
            [0, 1, 86400],

            [1, -2, -172799],
            [1, -1, -86399],
            [1, 0, 1],
            [1, 1, 86401]
        ];
    }

    /**
     * @dataProvider providerMinusSeconds
     *
     * @param int $seconds
     * @param int $secondsToSubtract
     * @param int $expectedSeconds
     */
    public function testMinusSeconds(int $seconds, int $secondsToSubtract, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusSeconds($secondsToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusSeconds() : array
    {
        return [
            [0, 0, 0],
            [0, 1, -1],
            [0, -1, 1],
            [0, \PHP_INT_MAX, - \PHP_INT_MAX],
            [0, ~\PHP_INT_MAX + 1, \PHP_INT_MAX],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [1, \PHP_INT_MAX - 1, - \PHP_INT_MAX + 2],
            [1, ~\PHP_INT_MAX + 2, \PHP_INT_MAX],
            [1, \PHP_INT_MAX, - \PHP_INT_MAX + 1],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
            [-1, \PHP_INT_MAX, ~\PHP_INT_MAX],
            [-1, ~\PHP_INT_MAX + 1, \PHP_INT_MAX - 1]
        ];
    }

    /**
     * @dataProvider providerMinusMinutes
     *
     * @param int $seconds
     * @param int $minutesToSubtract
     * @param int $expectedSeconds
     */
    public function testMinusMinutes(int $seconds, int $minutesToSubtract, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusMinutes($minutesToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusMinutes() : array
    {
        return [
            [-1, -1, 59],
            [-1, 0, -1],
            [-1, 1, -61],
            [-1, 2, -121],

            [0, -1, 60],
            [0, 0, 0],
            [0, 1, -60],

            [1, -2, 121],
            [1, -1, 61],
            [1, 0, 1],
            [1, 1, -59]
        ];
    }

    /**
     * @dataProvider providerMinusHours
     *
     * @param int $seconds
     * @param int $hoursToSubtract
     * @param int $expectedSeconds
     */
    public function testMinusHours(int $seconds, int $hoursToSubtract, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusHours($hoursToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusHours() : array
    {
        return [
            [-1, -1, 3599],
            [-1, 0, -1],
            [-1, 1, -3601],
            [-1, 2, -7201],

            [0, -1, 3600],
            [0, 0, 0],
            [0, 1, -3600],

            [1, -2, 7201],
            [1, -1, 3601],
            [1, 0, 1],
            [1, 1, -3599]
        ];
    }

    /**
     * @dataProvider providerMinusDays
     *
     * @param int $seconds
     * @param int $daysToSubtract
     * @param int $expectedSeconds
     */
    public function testMinusDays(int $seconds, int $daysToSubtract, int $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusDays($daysToSubtract);
        $this->assertDurationIs($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusDays() : array
    {
        return [
            [-1, -1, 86399],
            [-1, 0, -1],
            [-1, 1, -86401],
            [-1, 2, -172801],

            [0, -1, 86400],
            [0, 0, 0],
            [0, 1, -86400],

            [1, -2, 172801],
            [1, -1, 86401],
            [1, 0, 1],
            [1, 1, -86399]
        ];
    }

    /**
     * @dataProvider providerMultipliedBy
     *
     * @param int $second
     * @param int $nano
     * @param int $multiplicand
     * @param int $expectedSecond
     * @param int $expectedNano
     */
    public function testMultipliedBy(int $second, int $nano, int $multiplicand, int $expectedSecond, int $expectedNano)
    {
        $duration = Duration::ofSeconds($second, $nano);
        $duration = $duration->multipliedBy($multiplicand);

        $this->assertDurationIs($expectedSecond, $expectedNano, $duration);
    }

    /**
     * @return array
     */
    public function providerMultipliedBy() : array
    {
        return [
            [-3, 0, -3, 9, 0],
            [-3, 1000, -3, 8, 999997000],
            [-3, 999999999, -3, 6, 000000003],
            [-3, 0, -1, 3, 0],
            [-3, 1000, -1, 2, 999999000],
            [-3, 999999999, -1, 2, 000000001],
            [-3, 0, 0, 0, 0],
            [-3, 1000, 0, 0, 0],
            [-3, 999999999, 0, 0, 0],
            [-3, 0, 1, -3, 0],
            [-3, 1000, 1, -3, 1000],
            [-3, 999999999, 1, -3, 999999999],
            [-3, 0, 3, -9, 0],
            [-3, 1000, 3, -9, 3000],
            [-3, 999999999, 3, -7, 999999997],
            [-1, 0, -3, 3, 0],
            [-1, 1000, -3, 2, 999997000],
            [-1, 999999999, -3, 0, 3],
            [-1, 0, -1, 1, 0],
            [-1, 1000, -1, 0, 999999000],
            [-1, 999999999, -1, 0, 1],
            [-1, 0, 0, 0, 0],
            [-1, 1000, 0, 0, 0],
            [-1, 999999999, 0, 0, 0],
            [-1, 0, 1, -1, 0],
            [-1, 1000, 1, -1, 1000],
            [-1, 999999999, 1, -1, 999999999],
            [-1, 0, 3, -3, 0],
            [-1, 1000, 3, -3, 3000],
            [-1, 999999999, 3, -1, 999999997],
            [0, 0, -3, 0, 0],
            [0, 1000, -3, -1, 999997000],
            [0, 999999999, -3, -3, 3],
            [0, 0, -1, 0, 0],
            [0, 1000, -1, -1, 999999000],
            [0, 999999999, -1, -1, 1],
            [0, 0, 0, 0, 0],
            [0, 1000, 0, 0, 0],
            [0, 999999999, 0, 0, 0],
            [0, 0, 1, 0, 0],
            [0, 1000, 1, 0, 1000],
            [0, 999999999, 1, 0, 999999999],
            [0, 0, 3, 0, 0],
            [0, 1000, 3, 0, 3000],
            [0, 999999999, 3, 2, 999999997],
            [1, 0, -3, -3, 0],
            [1, 1000, -3, -4, 999997000],
            [1, 999999999, -3, -6, 3],
            [1, 0, -1, -1, 0],
            [1, 1000, -1, -2, 999999000],
            [1, 999999999, -1, -2, 1],
            [1, 0, 0, 0, 0],
            [1, 1000, 0, 0, 0],
            [1, 999999999, 0, 0, 0],
            [1, 0, 1, 1, 0],
            [1, 1000, 1, 1, 1000],
            [1, 999999999, 1, 1, 999999999],
            [1, 0, 3, 3, 0],
            [1, 1000, 3, 3, 3000],
            [1, 999999999, 3, 5, 999999997],
            [3, 0, -3, -9, 0],
            [3, 1000, -3, -10, 999997000],
            [3, 999999999, -3, -12, 3],
            [3, 0, -1, -3, 0],
            [3, 1000, -1, -4, 999999000],
            [3, 999999999, -1, -4, 1],
            [3, 0, 0, 0, 0],
            [3, 1000, 0, 0, 0],
            [3, 999999999, 0, 0, 0],
            [3, 0, 1, 3, 0],
            [3, 1000, 1, 3, 1000],
            [3, 999999999, 1, 3, 999999999],
            [3, 0, 3, 9, 0],
            [3, 1000, 3, 9, 3000],
            [3, 999999999, 3, 11, 999999997],
            [1, 0,  \PHP_INT_MAX, \PHP_INT_MAX, 0],
            [1, 0, \PHP_INT_MIN, \PHP_INT_MIN, 0],
        ];
    }

    /**
     * @dataProvider providerDividedBy
     *
     * @param int $seconds
     * @param int $nanos
     * @param int $divisor
     * @param int $expectedSeconds
     * @param int $expectedNanos
     */
    public function testDividedBy(int $seconds, int $nanos, int $divisor, int $expectedSeconds, int $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos)->dividedBy($divisor);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration);
    }

    /**
     * @return array
     */
    public function providerDividedBy() : array
    {
        return [
            [3, 0, 1, 3, 0],
            [3, 0, 2, 1, 500000000],
            [3, 0, 3, 1, 0],
            [3, 0, 4, 0, 750000000],
            [3, 0, 5, 0, 600000000],
            [3, 0, 6, 0, 500000000],
            [3, 0, 7, 0, 428571428],
            [3, 0, 8, 0, 375000000],
            [0, 2, 2, 0, 1],
            [0, 1, 2, 0, 0],

            [3, 0, -1, -3, 0],
            [3, 0, -2, -2, 500000000],
            [3, 0, -3, -1, 0],
            [3, 0, -4, -1, 250000000],
            [3, 0, -5, -1, 400000000],
            [3, 0, -6, -1, 500000000],
            [3, 0, -7, -1, 571428572],
            [3, 0, -8, -1, 625000000],
            [0, 2, -2, -1, 999999999],
            [0, 1, -2, 0, 0],

            [-3, 0, 1, -3, 0],
            [-3, 0, 2, -2, 500000000],
            [-3, 0, 3, -1, 0],
            [-3, 0, 4, -1, 250000000],
            [-3, 0, 5, -1, 400000000],
            [-3, 0, 6, -1, 500000000],
            [-3, 0, 7, -1, 571428572],
            [-3, 0, 8, -1, 625000000],
            [-1, 999999998, 2, -1, 999999999],
            [-1, 999999999, 2, 0, 0],

            [-3, 0, -1, 3, 0],
            [-3, 0, -2, 1, 500000000],
            [-3, 0, -3, 1, 0],
            [-3, 0, -4, 0, 750000000],
            [-3, 0, -5, 0, 600000000],
            [-3, 0, -6, 0, 500000000],
            [-3, 0, -7, 0, 428571428],
            [-3, 0, -8, 0, 375000000],
            [-1, 999999998, -2, 0, 1],
            [-1, 999999999, -2, 0, 0],

            [10, 1, 7, 1, 428571428],
            [10, 2, 7, 1, 428571428],
            [10, 3, 7, 1, 428571429],
            [10, 1, -7, -2, 571428572],
            [10, 2, -7, -2, 571428572],
            [10, 3, -7, -2, 571428571],
        ];
    }

    /**
     * @expectedException \Brick\DateTime\DateTimeException
     */
    public function testDividedByZeroThrowsException()
    {
        Duration::zero()->dividedBy(0);
    }

    /**
     * @dataProvider providerNegated
     *
     * @param int $seconds         The duration in seconds.
     * @param int $nanos           The nanoseconds adjustement to the duration.
     * @param int $expectedSeconds The expected seconds of the negated duration.
     * @param int $expectedNanos   The expected nanoseconds adjustment of the negated duration.
     */
    public function testNegated(int $seconds, int $nanos, int $expectedSeconds, int $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertDurationIs($expectedSeconds, $expectedNanos, $duration->negated());
    }

    /**
     * @return array
     */
    public function providerNegated() : array
    {
        return [
            [0, 0, 0, 0],
            [1, 0, -1, 0],
            [-1, 0, 1, 0],
            [1, 1, -2, 999999999],
            [-2, 999999999, 1, 1],
            [-1, 1, 0, 999999999],
            [0, 999999999, -1, 1]
        ];
    }

    public function testAbs()
    {
        for ($seconds = -3; $seconds <= 3; $seconds++) {
            $duration = Duration::ofSeconds($seconds)->abs();
            $this->assertDurationIs(\abs($seconds), 0, $duration);
        }
    }

    public function testComparisons()
    {
        $this->doTestComparisons([
            Duration::ofDays(-1),
            Duration::ofHours(-2),
            Duration::ofHours(-1),
            Duration::ofMinutes(-2),
            Duration::ofMinutes(-1),
            Duration::ofSeconds(-2),
            Duration::ofSeconds(-1),
            Duration::zero(),
            Duration::ofSeconds(1),
            Duration::ofSeconds(2),
            Duration::ofMinutes(1),
            Duration::ofMinutes(2),
            Duration::ofHours(1),
            Duration::ofHours(2),
            Duration::ofDays(1),
        ]);
    }

    /**
     * @param Duration[] $durations
     */
    private function doTestComparisons(array $durations)
    {
        $count = \count($durations);

        for ($i = 0; $i < $count; $i++) {
            $a = $durations[$i];
            for ($j = 0; $j < $count; $j++) {
                $b = $durations[$j];
                if ($i < $j) {
                    $this->assertLessThan(0, $a->compareTo($b), $a . ' <=> ' . $b);
                    $this->assertTrue($a->isLessThan($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isGreaterThan($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isEqualTo($b), $a . ' <=> ' . $b);
                }
                elseif ($i > $j) {
                    $this->assertGreaterThan(0, $a->compareTo($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isLessThan($b), $a . ' <=> ' . $b);
                    $this->assertTrue($a->isGreaterThan($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isEqualTo($b), $a . ' <=> ' . $b);
                }
                else {
                    $this->assertSame(0, $a->compareTo($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isLessThan($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isGreaterThan($b), $a . ' <=> ' . $b);
                    $this->assertTrue($a->isEqualTo($b), $a . ' <=> ' . $b);
                }

                if ($i <= $j) {
                    $this->assertLessThanOrEqual(0, $a->compareTo($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isGreaterThan($b), $a . ' <=> ' . $b);
                }
                if ($i >= $j) {
                    $this->assertGreaterThanOrEqual(0, $a->compareTo($b), $a . ' <=> ' . $b);
                    $this->assertFalse($a->isLessThan($b), $a . ' <=> ' . $b);
                }
            }
        }
    }

    public function testEquals()
    {
        $test5a = Duration::ofSeconds(5);
        $test5b = Duration::ofSeconds(5);
        $test6a = Duration::ofSeconds(6);
        $test6b = Duration::ofSeconds(6);

        $this->assertTrue($test5a->isEqualTo($test5a));
        $this->assertTrue($test5a->isEqualTo($test5b));
        $this->assertFalse($test5a->isEqualTo($test6a));
        $this->assertFalse($test5a->isEqualTo($test6b));

        $this->assertTrue($test5b->isEqualTo($test5a));
        $this->assertTrue($test5b->isEqualTo($test5b));
        $this->assertFalse($test5b->isEqualTo($test6a));
        $this->assertFalse($test5b->isEqualTo($test6b));

        $this->assertFalse($test6a->isEqualTo($test5a));
        $this->assertFalse($test6a->isEqualTo($test5b));
        $this->assertTrue($test6a->isEqualTo($test6a));
        $this->assertTrue($test6a->isEqualTo($test6b));

        $this->assertFalse($test6b->isEqualTo($test5a));
        $this->assertFalse($test6b->isEqualTo($test5b));
        $this->assertTrue($test6b->isEqualTo($test6a));
        $this->assertTrue($test6b->isEqualTo($test6b));
    }

    /**
     * @dataProvider providerGetTotalMillis
     *
     * @param int $seconds        The duration in seconds.
     * @param int $nanos          The nanoseconds adjustment to the duration.
     * @param int $expectedMillis The expected total number of milliseconds.
     */
    public function testGetTotalMillis(int $seconds, int $nanos, int $expectedMillis)
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertSame($expectedMillis, $duration->getTotalMillis());
    }

    /**
     * @return array
     */
    public function providerGetTotalMillis() : array
    {
        return [
            [-123, 456000001, -122544],
            [-123, 456999999, -122544],
            [ 123, 456000001,  123456],
            [ 123, 456999999,  123456]
        ];
    }

    /**
     * @dataProvider providerGetTotalMicros
     *
     * @param int $seconds        The duration in seconds.
     * @param int $nanos          The nanoseconds adjustment to the duration.
     * @param int $expectedMicros The expected total number of microseconds.
     */
    public function testGetTotalMicros(int $seconds, int $nanos, int $expectedMicros)
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertSame($expectedMicros, $duration->getTotalMicros());
    }

    /**
     * @return array
     */
    public function providerGetTotalMicros() : array
    {
        return [
            [-123, 456789001, -122543211],
            [-123, 456789999, -122543211],
            [ 123, 456789001,  123456789],
            [ 123, 456789999,  123456789]
        ];
    }

    /**
     * @dataProvider providerGetTotalNanos
     *
     * @param int $seconds       The duration in seconds.
     * @param int $nanos         The nanoseconds adjustment to the duration.
     * @param int $expectedNanos The expected total number of nanoseconds.
     */
    public function testGetTotalNanos(int $seconds, int $nanos, int $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertSame($expectedNanos, $duration->getTotalNanos());
    }

    /**
     * @return array
     */
    public function providerGetTotalNanos() : array
    {
        return [
            [-2, 000000001, -1999999999],
            [-2, 999999999, -1000000001],
            [ 1, 000000001,  1000000001],
            [ 1, 999999999,  1999999999]
        ];
    }

    /**
     * @return array
     */
    public function providerToDaysPart() : array
    {
        return [
            [Duration::ofSeconds(365 * 86400 + 5 * 3600 + 48 * 60 + 46, 123456789), 365],
            [Duration::ofSeconds(-365 * 86400 - 5 * 3600 - 48 * 60 - 46, -123456789), -365],
            [Duration::ofSeconds(5 * 3600 + 48 * 60 + 46, 123456789), 0],
            [Duration::ofDays(365), 365],
            [Duration::ofHours(2), 0],
            [Duration::ofHours(-2), 0],
        ];
    }

    /**
     * @dataProvider providerToDaysPart
     *
     * @param Duration $duration
     * @param int      $days
     */
    public function testToDaysPart(Duration $duration, int $days)
    {
        $this->assertSame($days, $duration->toDaysPart());
    }

    /**
     * @return array
     */
    public function providerToHoursPart() : array
    {
        return [
            [Duration::ofSeconds(365 * 86400 + 5 * 3600 + 48 * 60 + 46, 123456789), 5],
            [Duration::ofSeconds(-365 * 86400 - 5 * 3600 - 48 * 60 - 46, -123456789), -5],
            [Duration::ofSeconds(48 * 60 + 46, 123456789), 0],
            [Duration::ofHours(2), 2],
            [Duration::ofHours(-2), -2],
        ];
    }

    /**
     * @dataProvider providerToHoursPart
     *
     * @param Duration $duration
     * @param int      $hours
     */
    public function testToHoursPart(Duration $duration, int $hours)
    {
        $this->assertSame($hours, $duration->toHoursPart());
    }

    /**
     * @return array
     */
    public function providerToMinutesPart() : array
    {
        return [
            [Duration::ofSeconds(365 * 86400 + 5 * 3600 + 48 * 60 + 46, 123456789), 48],
            [Duration::ofSeconds(-365 * 86400 - 5 * 3600 - 48 * 60 - 46, -123456789), -48],
            [Duration::ofSeconds(46, 123456789), 0],
            [Duration::ofHours(2), 0],
            [Duration::ofHours(-2), 0],
        ];
    }

    /**
     * @dataProvider providerToMinutesPart
     *
     * @param Duration $duration
     * @param int      $minutes
     */
    public function testToMinutesPart(Duration $duration, int $minutes)
    {
        $this->assertSame($minutes, $duration->toMinutesPart());
    }

    /**
     * @return array
     */
    public function providerToSecondsPart() : array
    {
        return [
            [Duration::ofSeconds(365 * 86400 + 5 * 3600 + 48 * 60 + 46, 123456789), 46],
            [Duration::ofSeconds(-365 * 86400 - 5 * 3600 - 48 * 60 - 46, -123456789), -47],
            [Duration::ofSeconds(0, 123456789), 0],
            [Duration::ofSeconds(46), 46],
            [Duration::ofHours(2), 0],
            [Duration::ofHours(-2), 0],
        ];
    }

    /**
     * @dataProvider providerToSecondsPart
     *
     * @param Duration $duration
     * @param int      $seconds
     */
    public function testToSecondsPart(Duration $duration, int $seconds)
    {
        $this->assertSame($seconds, $duration->toSecondsPart());
    }

    /**
     * @return array
     */
    public function providerToMillisPart() : array
    {
        return [
            [Duration::ofSeconds(365 * 86400 + 5 * 3600 + 48 * 60 + 46, 123456789), 123],
            [Duration::ofSeconds(-365 * 86400 - 5 * 3600 - 48 * 60 - 46, -123456789), 876],
            [Duration::ofSeconds(5 * 3600 + 48 * 60 + 46, 0), 0],
            [Duration::ofMilliseconds(123), 123],
            [Duration::ofHours(2), 0],
            [Duration::ofHours(-2), 0],
        ];
    }

    /**
     * @dataProvider providerToMillisPart
     *
     * @param Duration $duration
     * @param int      $millis
     */
    public function testToMillisPart(Duration $duration, int $millis)
    {
        $this->assertSame($millis, $duration->toMillisPart());
    }

    /**
     * @return array
     */
    public function providerToNanosPart() : array
    {
        return [
            [Duration::ofSeconds(365 * 86400 + 5 * 3600 + 48 * 60 + 46, 123456789), 123456789],
            [Duration::ofSeconds(-365 * 86400 - 5 * 3600 - 48 * 60 - 46, -123456789), 876543211],
            [Duration::ofSeconds(5 * 3600 + 48 * 60 + 46, 0), 0],
            [Duration::ofSeconds(0, 123456789), 123456789],
            [Duration::ofHours(2), 0],
            [Duration::ofHours(-2), 0],
        ];
    }

    /**
     * @dataProvider providerToNanosPart
     *
     * @param Duration $duration
     * @param int      $nanos
     */
    public function testToNanosPart(Duration $duration, int $nanos)
    {
        $this->assertSame($nanos, $duration->toNanosPart());
    }

    /**
     * @dataProvider providerToString
     *
     * @param int    $seconds
     * @param int    $nanos
     * @param string $expected
     */
    public function testToString(int $seconds, int $nanos, string $expected)
    {
        $this->assertSame($expected, (string) Duration::ofSeconds($seconds, $nanos));
    }

    /**
     * @return array
     */
    public function providerToString() : array
    {
        return [
            [0, 0, 'PT0S'],
            [0, 1, 'PT0.000000001S'],
            [1, 0, 'PT1S'],
            [1, 1, 'PT1.000000001S'],
            [60, 0, 'PT1M'],
            [60, 1, 'PT1M0.000000001S'],
            [61, 0, 'PT1M1S'],
            [61, 1, 'PT1M1.000000001S'],
            [3600, 0, 'PT1H'],
            [3600, 1, 'PT1H0.000000001S'],
            [3601, 0, 'PT1H1S'],
            [3601, 1, 'PT1H1.000000001S'],
            [3660, 0, 'PT1H1M'],
            [3660, 1, 'PT1H1M0.000000001S'],
            [3661, 0, 'PT1H1M1S'],
            [3661, 1, 'PT1H1M1.000000001S'],
            [86400, 0, 'PT24H'],
            [86400, 1, 'PT24H0.000000001S'],
            [90000, 0, 'PT25H'],
            [90000, 1, 'PT25H0.000000001S'],
            [90001, 0, 'PT25H1S'],
            [90001, 1, 'PT25H1.000000001S'],
            [90060, 0, 'PT25H1M'],
            [90060, 1, 'PT25H1M0.000000001S'],
            [90061, 0, 'PT25H1M1S'],
            [90061, 1, 'PT25H1M1.000000001S'],

            [-1, 0, 'PT-1S'],
            [-1, 1, 'PT-0.999999999S'],
            [-60, 0, 'PT-1M'],
            [-60, 1, 'PT-59.999999999S'],
            [-61, 0, 'PT-1M-1S'],
            [-61, 1, 'PT-1M-0.999999999S'],
            [-62, 0, 'PT-1M-2S'],
            [-62, 1, 'PT-1M-1.999999999S'],
            [-3600, 0, 'PT-1H'],
            [-3600, 1, 'PT-59M-59.999999999S'],
            [-3601, 0, 'PT-1H-1S'],
            [-3601, 1, 'PT-1H-0.999999999S'],
            [-3602, 0, 'PT-1H-2S'],
            [-3602, 1, 'PT-1H-1.999999999S'],
            [-3660, 0, 'PT-1H-1M'],
            [-3660, 1, 'PT-1H-59.999999999S'],
            [-3661, 0, 'PT-1H-1M-1S'],
            [-3661, 1, 'PT-1H-1M-0.999999999S'],
            [-3662, 0, 'PT-1H-1M-2S'],
            [-3662, 1, 'PT-1H-1M-1.999999999S'],
            [-86400, 0, 'PT-24H'],
            [-86400, 1, 'PT-23H-59M-59.999999999S'],
            [-86401, 0, 'PT-24H-1S'],
            [-86401, 1, 'PT-24H-0.999999999S'],
            [-90000, 0, 'PT-25H'],
            [-90000, 1, 'PT-24H-59M-59.999999999S'],
            [-90001, 0, 'PT-25H-1S'],
            [-90001, 1, 'PT-25H-0.999999999S'],
            [-90060, 0, 'PT-25H-1M'],
            [-90060, 1, 'PT-25H-59.999999999S'],
            [-90061, 0, 'PT-25H-1M-1S'],
            [-90061, 1, 'PT-25H-1M-0.999999999S'],
        ];
    }
}
