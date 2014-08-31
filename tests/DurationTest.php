<?php

namespace Brick\Tests\DateTime;

use Brick\DateTime\Duration;
use Brick\DateTime\Instant;

/**
 * Unit tests for class Duration.
 */
class DurationTest extends AbstractTestCase
{
    public function testZero()
    {
        $this->assertDurationEquals(0, 0, Duration::zero());
    }

    /**
     * @dataProvider providerOfSeconds
     *
     * @param integer $seconds         The duration in seconds.
     * @param integer $nanoAdjustment  The nanoseconds adjustement to the duration.
     * @param integer $expectedSeconds The expected adjusted duration seconds.
     * @param integer $expectedNanos   The expected adjusted duration nanoseconds.
     */
    public function testOfSeconds($seconds, $nanoAdjustment, $expectedSeconds, $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanoAdjustment);
        $this->assertDurationEquals($expectedSeconds, $expectedNanos, $duration);
    }

    /**
     * @return array
     */
    public function providerOfSeconds()
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
     * @param integer $seconds1
     * @param integer $nanos1
     * @param integer $seconds2
     * @param integer $nanos2
     * @param integer $seconds
     * @param integer $nanos
     */
    public function testBetween($seconds1, $nanos1, $seconds2, $nanos2, $seconds, $nanos)
    {
        $i1 = Instant::of($seconds1, $nanos1);
        $i2 = Instant::of($seconds2, $nanos2);

        $this->assertDurationEquals($seconds, $nanos, Duration::between($i1, $i2));
    }

    /**
     * @return array
     */
    public function providerBetween()
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
     * @param string  $text    The string to test.
     * @param integer $seconds The expected seconds.
     * @param integer $nanos   The expected nanos.
     */
    public function testParse($text, $seconds, $nanos)
    {
        $this->assertDurationEquals($seconds, $nanos, Duration::parse($text));
    }

    /**
     * @return array
     */
    public function providerParse()
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
    public function testParseFailureThrowsException($text)
    {
        Duration::parse($text);
    }

    /**
     * @return array
     */
    public function providerParseFailureThrowsException()
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
     * @param integer $seconds The seconds of the duration.
     * @param integer $nanos   The nanos of the duration.
     * @param integer $cmp     The comparison value.
     */
    public function testIsZero($seconds, $nanos, $cmp)
    {
        $this->assertSame($cmp === 0, Duration::ofSeconds($seconds, $nanos)->isZero());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param integer $seconds The seconds of the duration.
     * @param integer $nanos   The nanos of the duration.
     * @param integer $cmp     The comparison value.
     */
    public function testIsPositive($seconds, $nanos, $cmp)
    {
        $this->assertSame($cmp > 0, Duration::ofSeconds($seconds, $nanos)->isPositive());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param integer $seconds The seconds of the duration.
     * @param integer $nanos   The nanos of the duration.
     * @param integer $cmp     The comparison value.
     */
    public function testIsPositiveOrZero($seconds, $nanos, $cmp)
    {
        $this->assertSame($cmp >= 0, Duration::ofSeconds($seconds, $nanos)->isPositiveOrZero());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param integer $seconds The seconds of the duration.
     * @param integer $nanos   The nanos of the duration.
     * @param integer $cmp     The comparison value.
     */
    public function testIsNegative($seconds, $nanos, $cmp)
    {
        $this->assertSame($cmp < 0, Duration::ofSeconds($seconds, $nanos)->isNegative());
    }

    /**
     * @dataProvider providerCompareToZero
     *
     * @param integer $seconds The seconds of the duration.
     * @param integer $nanos   The nanos of the duration.
     * @param integer $cmp     The comparison value.
     */
    public function testIsNegativeOrZero($seconds, $nanos, $cmp)
    {
        $this->assertSame($cmp <= 0, Duration::ofSeconds($seconds, $nanos)->isNegativeOrZero());
    }

    /**
     * @return array
     */
    public function providerCompareToZero()
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
     * @param integer $seconds1 The seconds of the 1st duration.
     * @param integer $nanos1   The nanoseconds of the 1st duration.
     * @param integer $seconds2 The seconds of the 2nd duration.
     * @param integer $nanos2   The nanoseconds of the 2nd duration.
     * @param integer $expected The expected return value.
     */
    public function testCompareTo($seconds1, $nanos1, $seconds2, $nanos2, $expected)
    {
        $duration1 = Duration::ofSeconds($seconds1, $nanos1);
        $duration2 = Duration::ofSeconds($seconds2, $nanos2);

        $this->assertSame($expected, $duration1->compareTo($duration2));
    }

    /**
     * @return array
     */
    public function providerCompareTo()
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
     * @param integer $s1 The 1st duration's seconds.
     * @param integer $n1 The 1st duration's nanoseconds.
     * @param integer $s2 The 2nd duration's seconds.
     * @param integer $n2 The 2nd duration's nanoseconds.
     * @param integer $s  The expected seconds.
     * @param integer $n  The expected nanoseconds.
     */
    public function testPlus($s1, $n1, $s2, $n2, $s, $n)
    {
        $duration1 = Duration::ofSeconds($s1, $n1);
        $duration2 = Duration::ofSeconds($s2, $n2);

        $this->assertDurationEquals($s, $n, $duration1->plus($duration2));
    }

    /**
     * @dataProvider providerPlus
     *
     * @param integer $s1 The 1st duration's seconds.
     * @param integer $n1 The 1st duration's nanoseconds.
     * @param integer $s2 The 2nd duration's seconds.
     * @param integer $n2 The 2nd duration's nanoseconds.
     * @param integer $s  The expected seconds.
     * @param integer $n  The expected nanoseconds.
     */
    public function testMinus($s1, $n1, $s2, $n2, $s, $n)
    {
        $duration1 = Duration::ofSeconds($s1, $n1);
        $duration2 = Duration::ofSeconds(-$s2, -$n2);

        $this->assertDurationEquals($s, $n, $duration1->minus($duration2));
    }

    /**
     * @return array
     */
    public function providerPlus()
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
     * @param integer $seconds
     * @param integer $nanos
     * @param integer $secondsToAdd
     * @param integer $expectedSeconds
     * @param integer $expectedNanos
     */
    public function testPlusSeconds($seconds, $nanos, $secondsToAdd, $expectedSeconds, $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos)->plusSeconds($secondsToAdd);
        $this->assertDurationEquals($expectedSeconds, $expectedNanos, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusSeconds()
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

            [~PHP_INT_MAX, 0, PHP_INT_MAX, -1, 0],
            [PHP_INT_MAX, 0, ~PHP_INT_MAX, -1, 0],
            [PHP_INT_MAX, 0, 0, PHP_INT_MAX, 0],

            [-1, -5,  2, 0,  999999995],
            [ 1,  5, -2, -1, 5],
        ];
    }

    /**
     * @dataProvider providerPlusMinutes
     *
     * @param integer $seconds
     * @param integer $minutesToAdd
     * @param integer $expectedSeconds
     */
    public function testPlusMinutes($seconds, $minutesToAdd, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->plusMinutes($minutesToAdd);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusMinutes()
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
     * @param integer $seconds
     * @param integer $hoursToAdd
     * @param integer $expectedSeconds
     */
    public function testPlusHours($seconds, $hoursToAdd, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->plusHours($hoursToAdd);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusHours()
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
     * @param integer $seconds
     * @param integer $daysToAdd
     * @param integer $expectedSeconds
     */
    public function testPlusDays($seconds, $daysToAdd, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->plusDays($daysToAdd);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerPlusDays()
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
     * @param integer $seconds
     * @param integer $secondsToSubtract
     * @param integer $expectedSeconds
     */
    public function testMinusSeconds($seconds, $secondsToSubtract, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusSeconds($secondsToSubtract);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusSeconds()
    {
        return [
            [0, 0, 0],
            [0, 1, -1],
            [0, -1, 1],
            [0, PHP_INT_MAX, - PHP_INT_MAX],
            [0, ~PHP_INT_MAX + 1, PHP_INT_MAX],
            [1, 0, 1],
            [1, 1, 0],
            [1, -1, 2],
            [1, PHP_INT_MAX - 1, - PHP_INT_MAX + 2],
            [1, ~PHP_INT_MAX + 2, PHP_INT_MAX],
            [1, PHP_INT_MAX, - PHP_INT_MAX + 1],
            [-1, 0, -1],
            [-1, 1, -2],
            [-1, -1, 0],
            [-1, PHP_INT_MAX, ~PHP_INT_MAX],
            [-1, ~PHP_INT_MAX + 1, PHP_INT_MAX - 1]
        ];
    }

    /**
     * @dataProvider providerMinusMinutes
     *
     * @param integer $seconds
     * @param integer $minutesToSubtract
     * @param integer $expectedSeconds
     */
    public function testMinusMinutes($seconds, $minutesToSubtract, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusMinutes($minutesToSubtract);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusMinutes()
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
     * @param integer $seconds
     * @param integer $hoursToSubtract
     * @param integer $expectedSeconds
     */
    public function testMinusHours($seconds, $hoursToSubtract, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusHours($hoursToSubtract);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusHours()
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
     * @param integer $seconds
     * @param integer $daysToSubtract
     * @param integer $expectedSeconds
     */
    public function testMinusDays($seconds, $daysToSubtract, $expectedSeconds)
    {
        $duration = Duration::ofSeconds($seconds)->minusDays($daysToSubtract);
        $this->assertDurationEquals($expectedSeconds, 0, $duration);
    }

    /**
     * @return array
     */
    public function providerMinusDays()
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

    public function testMultipliedBy()
    {
        for ($seconds = -3; $seconds <= 3; $seconds++) {
            for ($multiplicand = -3; $multiplicand <= 3; $multiplicand++) {
                $duration = Duration::ofSeconds($seconds)->multipliedBy($multiplicand);
                $this->assertDurationEquals($seconds * $multiplicand, 0, $duration);
            }
        }
    }

    public function testMultipliedByMax()
    {
        $duration = Duration::ofSeconds(1)->multipliedBy(PHP_INT_MAX);
        $this->assertTrue($duration->isEqualTo(Duration::ofSeconds(PHP_INT_MAX)));
    }

    public function testMultipliedByMin()
    {
        $duration = Duration::ofSeconds(1)->multipliedBy(~ PHP_INT_MAX);
        $this->assertTrue($duration->isEqualTo(Duration::ofSeconds(~ PHP_INT_MAX)));
    }

    /**
     * @dataProvider providerDividedBy
     *
     * @param integer $seconds
     * @param integer $nanos
     * @param integer $divisor
     * @param integer $expectedSeconds
     * @param integer $expectedNanos
     */
    public function testDividedBy($seconds, $nanos, $divisor, $expectedSeconds, $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos)->dividedBy($divisor);
        $this->assertDurationEquals($expectedSeconds, $expectedNanos, $duration);
    }

    /**
     * @return array
     */
    public function providerDividedBy()
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
     * @param integer $seconds         The duration in seconds.
     * @param integer $nanos           The nanoseconds adjustement to the duration.
     * @param integer $expectedSeconds The expected seconds of the negated duration.
     * @param integer $expectedNanos   The expected nanoseconds adjustment of the negated duration.
     */
    public function testNegated($seconds, $nanos, $expectedSeconds, $expectedNanos)
    {
        $duration = Duration::ofSeconds($seconds, $nanos);
        $this->assertDurationEquals($expectedSeconds, $expectedNanos, $duration->negated());
    }

    /**
     * @return array
     */
    public function providerNegated()
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
            $this->assertDurationEquals(abs($seconds), 0, $duration);
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
        for ($i = 0; $i < count($durations); $i++) {
            $a = $durations[$i];
            for ($j = 0; $j < count($durations); $j++) {
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
     * @dataProvider providerToString
     *
     * @param integer $seconds
     * @param integer $nanos
     * @param string  $expected
     */
    public function testToString($seconds, $nanos, $expected)
    {
        $this->assertSame($expected, (string) Duration::ofSeconds($seconds, $nanos));
    }

    /**
     * @return array
     */
    public function providerToString()
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
