<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\Quarter;
use Brick\DateTime\TimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Unit tests for class Quarter.
 */
class QuarterTest extends AbstractTestCase
{
    /**
     * @param int     $expectedValue The expected value of the constant.
     * @param Quarter $quarter       The quarter instance.
     */
    #[DataProvider('providerValues')]
    public function testValues(int $expectedValue, Quarter $quarter): void
    {
        self::assertSame($expectedValue, $quarter->value);
    }

    public static function providerValues(): array
    {
        return [
            [1, Quarter::Q1],
            [2, Quarter::Q2],
            [3, Quarter::Q3],
            [4, Quarter::Q4],
        ];
    }

    /**
     * @param int     $epochSecond     The epoch second to set the clock time to.
     * @param string  $timeZone        The time-zone to get the current quarter in.
     * @param Quarter $expectedQuarter The expected quarter.
     */
    #[DataProvider('providerNow')]
    public function testNow(int $epochSecond, string $timeZone, Quarter $expectedQuarter): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        self::assertSame($expectedQuarter, Quarter::now(TimeZone::parse($timeZone), $clock));
    }

    public static function providerNow(): array
    {
        return [
            // End of Q4 2023 / Start of Q1 2024 boundary (Dec 31 23:59:59 / Jan 1 00:00:00)
            [1704067199, '-01:00', Quarter::Q4], // 2023-12-31 23:59:59 UTC-1 (still Dec 31)
            [1704067199, '+00:00', Quarter::Q4], // 2023-12-31 23:59:59 UTC (still Dec 31)
            [1704067199, '+01:00', Quarter::Q1], // 2024-01-01 00:59:59 UTC+1 (already Jan 1)
            [1704067200, '-01:00', Quarter::Q4], // 2023-12-31 23:00:00 UTC-1 (still Dec 31)
            [1704067200, '+00:00', Quarter::Q1], // 2024-01-01 00:00:00 UTC (now Jan 1)
            [1704067200, '+01:00', Quarter::Q1], // 2024-01-01 01:00:00 UTC+1 (already Jan 1)

            // End of Q1 / Start of Q2 boundary (Mar 31 23:59:59 / Apr 1 00:00:00)
            [1711929599, '-01:00', Quarter::Q1], // 2024-03-31 22:59:59 UTC-1 (still Mar 31)
            [1711929599, '+00:00', Quarter::Q1], // 2024-03-31 23:59:59 UTC (still Mar 31)
            [1711929599, '+01:00', Quarter::Q2], // 2024-04-01 00:59:59 UTC+1 (already Apr 1)
            [1711929600, '-01:00', Quarter::Q1], // 2024-03-31 23:00:00 UTC-1 (still Mar 31)
            [1711929600, '+00:00', Quarter::Q2], // 2024-04-01 00:00:00 UTC (now Apr 1)
            [1711929600, '+01:00', Quarter::Q2], // 2024-04-01 01:00:00 UTC+1 (already Apr 1)

            // End of Q2 / Start of Q3 boundary (Jun 30 23:59:59 / Jul 1 00:00:00)
            [1719791999, '-01:00', Quarter::Q2], // 2024-06-30 22:59:59 UTC-1 (still Jun 30)
            [1719791999, '+00:00', Quarter::Q2], // 2024-06-30 23:59:59 UTC (still Jun 30)
            [1719791999, '+01:00', Quarter::Q3], // 2024-07-01 00:59:59 UTC+1 (already Jul 1)
            [1719792000, '-01:00', Quarter::Q2], // 2024-06-30 23:00:00 UTC-1 (still Jun 30)
            [1719792000, '+00:00', Quarter::Q3], // 2024-07-01 00:00:00 UTC (now Jul 1)
            [1719792000, '+01:00', Quarter::Q3], // 2024-07-01 01:00:00 UTC+1 (already Jul 1)

            // End of Q3 / Start of Q4 boundary (Sep 30 23:59:59 / Oct 1 00:00:00)
            [1727740799, '-01:00', Quarter::Q3], // 2024-09-30 22:59:59 UTC-1 (still Sep 30)
            [1727740799, '+00:00', Quarter::Q3], // 2024-09-30 23:59:59 UTC (still Sep 30)
            [1727740799, '+01:00', Quarter::Q4], // 2024-10-01 00:59:59 UTC+1 (already Oct 1)
            [1727740800, '-01:00', Quarter::Q3], // 2024-09-30 23:00:00 UTC-1 (still Sep 30)
            [1727740800, '+00:00', Quarter::Q4], // 2024-10-01 00:00:00 UTC (now Oct 1)
            [1727740800, '+01:00', Quarter::Q4], // 2024-10-01 01:00:00 UTC+1 (already Oct 1)
        ];
    }

    /**
     * @param Quarter $quarter      The quarter.
     * @param string  $expectedJson The representation in json of the quarter.
     */
    #[DataProvider('provideJsonSerialize')]
    public function testJsonSerialize(Quarter $quarter, string $expectedJson): void
    {
        self::assertSame($expectedJson, json_encode($quarter, JSON_THROW_ON_ERROR));
    }

    public static function provideJsonSerialize(): array
    {
        return [
            [Quarter::Q1, '1'],
            [Quarter::Q2, '2'],
            [Quarter::Q3, '3'],
            [Quarter::Q4, '4'],
        ];
    }
}
