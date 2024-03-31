<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\Instant;
use Brick\DateTime\Month;
use Brick\DateTime\MonthDay;
use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\TimeZone;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Unit tests for class MonthDay.
 */
class MonthDayTest extends AbstractTestCase
{
    #[DataProvider('providerOf')]
    public function testOf(int $month, int $day): void
    {
        self::assertMonthDayIs($month, $day, MonthDay::of($month, $day));
        self::assertMonthDayIs($month, $day, MonthDay::of(Month::from($month), $day));
    }

    public static function providerOf(): array
    {
        return [
            [1, 1],
            [1, 31],
            [2, 29],
            [3, 31],
            [4, 30],
            [5, 31],
            [6, 30],
            [7, 31],
            [8, 31],
            [9, 30],
            [10, 31],
            [11, 30],
            [12, 31],
        ];
    }

    #[DataProvider('providerOfThrowsExceptionOnInvalidMonthDay')]
    public function testOfThrowsExceptionOnInvalidMonthDay(int $month, int $day): void
    {
        $this->expectException(DateTimeException::class);
        MonthDay::of($month, $day);
    }

    public static function providerOfThrowsExceptionOnInvalidMonthDay(): array
    {
        return [
            [0, 1],
            [1, 0],
            [13, 1],
            [1, 32],
            [2, 30],
            [3, 32],
            [4, 31],
            [5, 32],
            [6, 31],
            [7, 32],
            [8, 32],
            [9, 31],
            [10, 32],
            [11, 32],
            [12, 32],
        ];
    }

    /**
     * @param string $text  The text to parse.
     * @param int    $month The expected month.
     * @param int    $day   The expected day.
     */
    #[DataProvider('providerParse')]
    public function testParse(string $text, int $month, int $day): void
    {
        self::assertMonthDayIs($month, $day, MonthDay::parse($text));
    }

    public static function providerParse(): array
    {
        return [
            ['--01-01', 1, 1],
            ['--02-29', 2, 29],
            ['--12-31', 12, 31],
        ];
    }

    #[DataProvider('providerParseInvalidStringThrowsException')]
    public function testParseInvalidStringThrowsException(string $text): void
    {
        $this->expectException(DateTimeParseException::class);
        MonthDay::parse($text);
    }

    public static function providerParseInvalidStringThrowsException(): array
    {
        return [
            ['01-01'],
            ['-01-01'],
            ['---01-01'],
            [' --01-01'],
            ['--01-01 '],
            ['--1-1'],
            ['--1-01'],
            ['--01-1'],
            ['--123-01'],
            ['--01-123'],
            ['--1X-01'],
            ['--01-1X'],
        ];
    }

    #[DataProvider('providerParseInvalidDateThrowsException')]
    public function testParseInvalidDateThrowsException(string $text): void
    {
        $this->expectException(DateTimeException::class);
        MonthDay::parse($text);
    }

    public static function providerParseInvalidDateThrowsException(): array
    {
        return [
            ['--00-01'],
            ['--01-00'],
            ['--01-32'],
            ['--02-30'],
        ];
    }

    /**
     * @param int    $epochSecond The epoch second.
     * @param string $timeZone    The time-zone.
     * @param int    $month       The expected month.
     * @param int    $day         The expected day.
     */
    #[DataProvider('providerNow')]
    public function testNow(int $epochSecond, string $timeZone, int $month, int $day): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        self::assertMonthDayIs($month, $day, MonthDay::now(TimeZone::parse($timeZone), $clock));
    }

    public static function providerNow(): array
    {
        return [
            [946684799, '+00:00', 12, 31],
            [946684799, 'America/Los_Angeles', 12, 31],
            [946684799, '+01:00', 1, 1],
            [946684799, 'Europe/Paris', 1, 1],
        ];
    }

    /**
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    #[DataProvider('providerCompareTo')]
    public function testCompareTo(int $m1, int $d1, int $m2, int $d2, int $result): void
    {
        self::assertSame($result, MonthDay::of($m1, $d1)->compareTo(MonthDay::of($m2, $d2)));
    }

    /**
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    #[DataProvider('providerCompareTo')]
    public function testIsEqualTo(int $m1, int $d1, int $m2, int $d2, int $result): void
    {
        self::assertSame($result === 0, MonthDay::of($m1, $d1)->isEqualTo(MonthDay::of($m2, $d2)));
    }

    /**
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    #[DataProvider('providerCompareTo')]
    public function testIsBefore(int $m1, int $d1, int $m2, int $d2, int $result): void
    {
        self::assertSame($result === -1, MonthDay::of($m1, $d1)->isBefore(MonthDay::of($m2, $d2)));
    }

    /**
     * @param int $m1     The month of the base month-day.
     * @param int $d1     The day of the base month-day.
     * @param int $m2     The month of the month-day to compare to.
     * @param int $d2     The day of the month-day to compare to.
     * @param int $result The expected result.
     */
    #[DataProvider('providerCompareTo')]
    public function testIsAfter(int $m1, int $d1, int $m2, int $d2, int $result): void
    {
        self::assertSame($result === 1, MonthDay::of($m1, $d1)->isAfter(MonthDay::of($m2, $d2)));
    }

    public static function providerCompareTo(): array
    {
        return [
            [1, 1, 1, 1,  0],
            [1, 1, 1, 2, -1],
            [1, 1, 2, 1, -1],
            [1, 1, 2, 2, -1],
            [1, 2, 1, 1,  1],
            [1, 2, 1, 2,  0],
            [1, 2, 2, 1, -1],
            [1, 2, 2, 2, -1],
            [2, 1, 1, 1,  1],
            [2, 1, 1, 2,  1],
            [2, 1, 2, 1,  0],
            [2, 1, 2, 2, -1],
            [2, 2, 1, 1,  1],
            [2, 2, 1, 2,  1],
            [2, 2, 2, 1,  1],
            [2, 2, 2, 2,  0],
        ];
    }

    /**
     * @param int  $month   The month of the month-day to test.
     * @param int  $day     The day of the month-day to test.
     * @param int  $year    The year to test against.
     * @param bool $isValid The expected result.
     */
    #[DataProvider('providerIsValidYear')]
    public function testIsValidYear(int $month, int $day, int $year, bool $isValid): void
    {
        self::assertSame($isValid, MonthDay::of($month, $day)->isValidYear($year));
    }

    public static function providerIsValidYear(): array
    {
        return [
            [1, 1, 2000, true],
            [1, 31, 2000, true],
            [2, 1, 2000, true],
            [2, 28, 2000, true],
            [12, 1, 2000, true],
            [12, 31, 2000, true],
            [1, 1, 2001, true],
            [1, 31, 2001, true],
            [2, 1, 2001, true],
            [2, 28, 2001, true],
            [12, 1, 2001, true],
            [12, 31, 2001, true],
            [2, 29, 2000, true],
            [2, 29, 2001, false],
            [2, 29, 2002, false],
            [2, 29, 2003, false],
            [2, 29, 2004, true],
        ];
    }

    /**
     * @param int $month       The month of the base month-day to test.
     * @param int $day         The day of base the month-day to test.
     * @param int $newMonth    The new month to apply.
     * @param int $expectedDay The expected day of the resulting month-day.
     */
    #[DataProvider('providerWithMonth')]
    public function testWithMonth(int $month, int $day, int $newMonth, int $expectedDay): void
    {
        $monthDay = MonthDay::of($month, $day);
        self::assertMonthDayIs($month, $day, $monthDay);

        self::assertMonthDayIs($newMonth, $expectedDay, $monthDay->withMonth($newMonth));
        self::assertMonthDayIs($newMonth, $expectedDay, $monthDay->withMonth(Month::from($newMonth)));
    }

    public static function providerWithMonth(): array
    {
        return [
            [1, 1, 1, 1],
            [1, 1, 12, 1],
            [1, 31, 12, 31],
            [1, 31, 11, 30],
            [1, 31, 10, 31],
            [1, 31, 9, 30],
            [1, 31, 2, 29],
            [1, 30, 2, 29],
            [1, 29, 2, 29],
            [1, 28, 2, 28],
            [2, 29, 2, 29],
            [2, 29, 3, 29],
            [2, 29, 4, 29],
            [11, 30, 11, 30],
            [11, 30, 12, 30],
            [11, 30, 2, 29],
        ];
    }

    /**
     * @param int $month    The month of the base month-day to test.
     * @param int $day      The day of base the month-day to test.
     * @param int $newMonth The new month to apply.
     */
    #[DataProvider('providerWithInvalidMonthThrowsException')]
    public function testWithInvalidMonthThrowsException(int $month, int $day, int $newMonth): void
    {
        $this->expectException(DateTimeException::class);
        MonthDay::of($month, $day)->withMonth($newMonth);
    }

    public static function providerWithInvalidMonthThrowsException(): array
    {
        return [
            [1, 1, 0],
            [12, 31, 13],
        ];
    }

    public function testWithDayWithSameDay(): void
    {
        $month = 1;
        $day = 20;
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withDay($day);

        self::assertMonthDayIs($month, $day, $monthDay);
        self::assertMonthDayIs($month, $day, $newMonthDay);
    }

    /**
     * @param int $month  The month of the base month-day to test.
     * @param int $day    The day of base the month-day to test.
     * @param int $newDay The new day to apply.
     */
    #[DataProvider('providerWithDay')]
    public function testWithDay(int $month, int $day, int $newDay): void
    {
        $monthDay = MonthDay::of($month, $day);
        $newMonthDay = $monthDay->withDay($newDay);

        self::assertMonthDayIs($month, $day, $monthDay);
        self::assertMonthDayIs($month, $newDay, $newMonthDay);
    }

    public static function providerWithDay(): array
    {
        return [
            [1, 1, 31],
            [1, 31, 1],
            [12, 1, 31],
            [12, 31, 1],
            [2, 1, 29],
            [2, 29, 1],
        ];
    }

    /**
     * @param int $month  The month of the base month-day to test.
     * @param int $day    The day of base the month-day to test.
     * @param int $newDay The new day to apply.
     */
    #[DataProvider('providerWithInvalidDayThrowsException')]
    public function testWithInvalidDayThrowsException(int $month, int $day, int $newDay): void
    {
        $this->expectException(DateTimeException::class);
        MonthDay::of($month, $day)->withDay($newDay);
    }

    public static function providerWithInvalidDayThrowsException(): array
    {
        return [
            [12, 31, 32],
            [12, 1, 0],
            [11, 30, 31],
            [11, 1, 0],
            [10, 31, 32],
            [10, 1, 0],
            [9, 30, 31],
            [9, 1, 0],
            [8, 31, 32],
            [8, 1, 0],
            [7, 31, 32],
            [7, 1, 0],
            [6, 30, 31],
            [6, 1, 0],
            [5, 31, 32],
            [5, 1, 0],
            [4, 30, 31],
            [4, 1, 0],
            [3, 31, 32],
            [3, 1, 0],
            [2, 29, 30],
            [2, 1, 0],
            [1, 31, 32],
            [1, 1, 0],
        ];
    }

    /**
     * @param int $month       The month of the base month-day.
     * @param int $day         The day of the base month-day.
     * @param int $year        The year to combine with the month-day.
     * @param int $expectedDay The expected day of the resulting date.
     */
    #[DataProvider('providerAtYear')]
    public function testAtYear(int $month, int $day, int $year, int $expectedDay): void
    {
        self::assertLocalDateIs($year, $month, $expectedDay, MonthDay::of($month, $day)->atYear($year));
    }

    public static function providerAtYear(): array
    {
        return [
            [1, 31, 2000, 31],
            [4, 30, 2001, 30],
            [2, 28, 2000, 28],
            [2, 29, 2000, 29],
            [2, 28, 2001, 28],
            [2, 29, 2001, 28],
        ];
    }

    #[DataProvider('providerAtInvalidYearThrowsException')]
    public function testAtInvalidYearThrowsException(int $year): void
    {
        $this->expectException(DateTimeException::class);
        MonthDay::of(1, 1)->atYear($year);
    }

    public static function providerAtInvalidYearThrowsException(): array
    {
        return [
            [-1234567890],
            [1234567890],
        ];
    }

    /**
     * @param int    $month  The month of the month-day to test.
     * @param int    $day    The day of the month-day to test.
     * @param string $string The expected result string.
     */
    #[DataProvider('providerToString')]
    public function testJsonSerialize(int $month, int $day, string $string): void
    {
        self::assertSame(json_encode($string, JSON_THROW_ON_ERROR), json_encode(MonthDay::of($month, $day), JSON_THROW_ON_ERROR));
    }

    /**
     * @param int    $month  The month of the month-day to test.
     * @param int    $day    The day of the month-day to test.
     * @param string $string The expected result string.
     */
    #[DataProvider('providerToString')]
    public function testToISOString(int $month, int $day, string $string): void
    {
        self::assertSame($string, MonthDay::of($month, $day)->toISOString());
    }

    /**
     * @param int    $month  The month of the month-day to test.
     * @param int    $day    The day of the month-day to test.
     * @param string $string The expected result string.
     */
    #[DataProvider('providerToString')]
    public function testToString(int $month, int $day, string $string): void
    {
        self::assertSame($string, (string) MonthDay::of($month, $day));
    }

    public static function providerToString(): array
    {
        return [
            [1, 1, '--01-01'],
            [1, 31, '--01-31'],
            [12, 1, '--12-01'],
            [12, 31, '--12-31'],
        ];
    }
}
