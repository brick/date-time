<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DateTimeException;
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Month;
use Brick\DateTime\Period;
use Brick\DateTime\Quarter;
use Brick\DateTime\TimeZone;
use Brick\DateTime\Year;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * Unit tests for class LocalDate.
 */
class LocalDateTest extends AbstractTestCase
{
    public function testOf(): void
    {
        self::assertLocalDateIs(2007, 7, 15, LocalDate::of(2007, 7, 15));
        self::assertLocalDateIs(2007, 7, 15, LocalDate::of(2007, Month::JULY, 15));
    }

    #[DataProvider('providerGetMonth')]
    public function testGetMonth(int $monthValue, Month $month): void
    {
        self::assertSame($month, LocalDate::of(2000, $monthValue, 1)->getMonth());
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

    /**
     * @param int $year  The year of the invalid date.
     * @param int $month The month of the invalid date.
     * @param int $day   The day of the invalid date.
     */
    #[DataProvider('providerOfInvalidDateThrowsException')]
    public function testOfInvalidDateThrowsException(int $year, int $month, int $day): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::of($year, $month, $day);
    }

    public static function providerOfInvalidDateThrowsException(): array
    {
        return [
            [2007, 2, 29],
            [2007, 4, 31],
            [2007, 1, 0],
            [2007, 1, 32],
            [2007, 0, 1],
            [2007, 13, 1],
            [PHP_INT_MIN, 1, 1],
            [PHP_INT_MAX, 1, 1],
        ];
    }

    /**
     * @param int $year      The year.
     * @param int $dayOfYear The day-of-year.
     */
    #[DataProvider('providerOfInvalidYearDayThrowsException')]
    public function testOfInvalidYearDayThrowsException(int $year, int $dayOfYear): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::ofYearDay($year, $dayOfYear);
    }

    public static function providerOfInvalidYearDayThrowsException(): array
    {
        return [
            [2007, 366],
            [2007, 0],
            [2007, 367],
            [~PHP_INT_MAX, 1],
            [PHP_INT_MAX, 1],
        ];
    }

    /**
     * @param int $epochDay The epoch day.
     * @param int $year     The expected year.
     * @param int $month    The expected month.
     * @param int $day      The expected day.
     */
    #[DataProvider('providerEpochDay')]
    public function testOfEpochDay(int $epochDay, int $year, int $month, int $day): void
    {
        self::assertLocalDateIs($year, $month, $day, LocalDate::ofEpochDay($epochDay));
    }

    public function testOfEpochDayOutOfRangeThrowsException(): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::ofEpochDay(500000000);
    }

    /**
     * @param int $epochDay The expected epoch day.
     * @param int $year     The year.
     * @param int $month    The month.
     * @param int $day      The day.
     */
    #[DataProvider('providerEpochDay')]
    public function testToEpochDay(int $epochDay, int $year, int $month, int $day): void
    {
        self::assertSame($epochDay, LocalDate::of($year, $month, $day)->toEpochDay());
    }

    public static function providerEpochDay(): array
    {
        return [
            [-1000000, -768,  2,  4],
            [-100000, 1696,  3, 17],
            [-10000, 1942,  8, 16],
            [-1000, 1967,  4,  7],
            [-100, 1969,  9, 23],
            [-10, 1969, 12, 22],
            [-1, 1969, 12, 31],
            [0, 1970,  1,  1],
            [1, 1970,  1,  2],
            [10, 1970,  1, 11],
            [100, 1970,  4, 11],
            [1000, 1972,  9, 27],
            [10000, 1997,  5, 19],
            [100000, 2243, 10, 17],
            [1000000, 4707, 11, 29],
        ];
    }

    #[DataProvider('providerPreviousDayOfWeek')]
    public function testPreviousDayOfWeek(string $date, DayOfWeek $dayOfWeek, string $expectedDate): void
    {
        $actualDate = LocalDate::parse($date)->previousDayOfWeek($dayOfWeek);
        self::assertSame($expectedDate, $actualDate->toISOString());
    }

    public static function providerPreviousDayOfWeek(): array
    {
        return [
            ['2024-06-02', DayOfWeek::SATURDAY, '2024-06-01'],
            ['2024-06-02', DayOfWeek::FRIDAY, '2024-05-31'],
            ['2024-06-02', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-06-02', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-06-02', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-06-02', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-06-02', DayOfWeek::SUNDAY, '2024-05-26'],

            ['2024-06-01', DayOfWeek::FRIDAY, '2024-05-31'],
            ['2024-06-01', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-06-01', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-06-01', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-06-01', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-06-01', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-06-01', DayOfWeek::SATURDAY, '2024-05-25'],

            ['2024-05-31', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-05-31', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-05-31', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-31', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-31', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-31', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-31', DayOfWeek::FRIDAY, '2024-05-24'],

            ['2024-05-30', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-05-30', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-30', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-30', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-30', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-30', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-30', DayOfWeek::THURSDAY, '2024-05-23'],

            ['2024-05-29', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-29', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-29', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-29', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-29', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-29', DayOfWeek::THURSDAY, '2024-05-23'],
            ['2024-05-29', DayOfWeek::WEDNESDAY, '2024-05-22'],

            ['2024-05-28', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-28', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-28', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-28', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-28', DayOfWeek::THURSDAY, '2024-05-23'],
            ['2024-05-28', DayOfWeek::WEDNESDAY, '2024-05-22'],
            ['2024-05-28', DayOfWeek::TUESDAY, '2024-05-21'],

            ['2024-05-27', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-27', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-27', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-27', DayOfWeek::THURSDAY, '2024-05-23'],
            ['2024-05-27', DayOfWeek::WEDNESDAY, '2024-05-22'],
            ['2024-05-27', DayOfWeek::TUESDAY, '2024-05-21'],
            ['2024-05-27', DayOfWeek::MONDAY, '2024-05-20'],
        ];
    }

    #[DataProvider('providerPreviousOrSameDayOfWeek')]
    public function testPreviousOrSameDayOfWeek(string $date, DayOfWeek $dayOfWeek, string $expectedDate): void
    {
        $actualDate = LocalDate::parse($date)->previousOrSameDayOfWeek($dayOfWeek);
        self::assertSame($expectedDate, $actualDate->toISOString());
    }

    public static function providerPreviousOrSameDayOfWeek(): array
    {
        return [
            ['2024-06-02', DayOfWeek::SUNDAY, '2024-06-02'],
            ['2024-06-02', DayOfWeek::SATURDAY, '2024-06-01'],
            ['2024-06-02', DayOfWeek::FRIDAY, '2024-05-31'],
            ['2024-06-02', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-06-02', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-06-02', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-06-02', DayOfWeek::MONDAY, '2024-05-27'],

            ['2024-06-01', DayOfWeek::SATURDAY, '2024-06-01'],
            ['2024-06-01', DayOfWeek::FRIDAY, '2024-05-31'],
            ['2024-06-01', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-06-01', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-06-01', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-06-01', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-06-01', DayOfWeek::SUNDAY, '2024-05-26'],

            ['2024-05-31', DayOfWeek::FRIDAY, '2024-05-31'],
            ['2024-05-31', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-05-31', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-05-31', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-31', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-31', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-31', DayOfWeek::SATURDAY, '2024-05-25'],

            ['2024-05-30', DayOfWeek::THURSDAY, '2024-05-30'],
            ['2024-05-30', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-05-30', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-30', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-30', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-30', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-30', DayOfWeek::FRIDAY, '2024-05-24'],

            ['2024-05-29', DayOfWeek::WEDNESDAY, '2024-05-29'],
            ['2024-05-29', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-29', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-29', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-29', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-29', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-29', DayOfWeek::THURSDAY, '2024-05-23'],

            ['2024-05-28', DayOfWeek::TUESDAY, '2024-05-28'],
            ['2024-05-28', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-28', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-28', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-28', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-28', DayOfWeek::THURSDAY, '2024-05-23'],
            ['2024-05-28', DayOfWeek::WEDNESDAY, '2024-05-22'],

            ['2024-05-27', DayOfWeek::MONDAY, '2024-05-27'],
            ['2024-05-27', DayOfWeek::SUNDAY, '2024-05-26'],
            ['2024-05-27', DayOfWeek::SATURDAY, '2024-05-25'],
            ['2024-05-27', DayOfWeek::FRIDAY, '2024-05-24'],
            ['2024-05-27', DayOfWeek::THURSDAY, '2024-05-23'],
            ['2024-05-27', DayOfWeek::WEDNESDAY, '2024-05-22'],
            ['2024-05-27', DayOfWeek::TUESDAY, '2024-05-21'],
        ];
    }

    #[DataProvider('providerNextDayOfWeek')]
    public function testNextDayOfWeek(string $date, DayOfWeek $dayOfWeek, string $expectedDate): void
    {
        $actualDate = LocalDate::parse($date)->nextDayOfWeek($dayOfWeek);
        self::assertSame($expectedDate, $actualDate->toISOString());
    }

    public static function providerNextDayOfWeek(): array
    {
        return [
            ['2024-06-23', DayOfWeek::MONDAY, '2024-06-24'],
            ['2024-06-23', DayOfWeek::TUESDAY, '2024-06-25'],
            ['2024-06-23', DayOfWeek::WEDNESDAY, '2024-06-26'],
            ['2024-06-23', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-23', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-23', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-23', DayOfWeek::SUNDAY, '2024-06-30'],

            ['2024-06-24', DayOfWeek::TUESDAY, '2024-06-25'],
            ['2024-06-24', DayOfWeek::WEDNESDAY, '2024-06-26'],
            ['2024-06-24', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-24', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-24', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-24', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-24', DayOfWeek::MONDAY, '2024-07-01'],

            ['2024-06-25', DayOfWeek::WEDNESDAY, '2024-06-26'],
            ['2024-06-25', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-25', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-25', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-25', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-25', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-25', DayOfWeek::TUESDAY, '2024-07-02'],

            ['2024-06-26', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-26', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-26', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-26', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-26', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-26', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-26', DayOfWeek::WEDNESDAY, '2024-07-03'],

            ['2024-06-27', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-27', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-27', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-27', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-27', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-27', DayOfWeek::WEDNESDAY, '2024-07-03'],
            ['2024-06-27', DayOfWeek::THURSDAY, '2024-07-04'],

            ['2024-06-28', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-28', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-28', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-28', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-28', DayOfWeek::WEDNESDAY, '2024-07-03'],
            ['2024-06-28', DayOfWeek::THURSDAY, '2024-07-04'],
            ['2024-06-28', DayOfWeek::FRIDAY, '2024-07-05'],

            ['2024-06-29', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-29', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-29', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-29', DayOfWeek::WEDNESDAY, '2024-07-03'],
            ['2024-06-29', DayOfWeek::THURSDAY, '2024-07-04'],
            ['2024-06-29', DayOfWeek::FRIDAY, '2024-07-05'],
            ['2024-06-29', DayOfWeek::SATURDAY, '2024-07-06'],
        ];
    }

    #[DataProvider('providerNextOrSameDayOfWeek')]
    public function testNextOrSameDayOfWeek(string $date, DayOfWeek $dayOfWeek, string $expectedDate): void
    {
        $actualDate = LocalDate::parse($date)->nextOrSameDayOfWeek($dayOfWeek);
        self::assertSame($expectedDate, $actualDate->toISOString());
    }

    public static function providerNextOrSameDayOfWeek(): array
    {
        return [
            ['2024-06-24', DayOfWeek::MONDAY, '2024-06-24'],
            ['2024-06-24', DayOfWeek::TUESDAY, '2024-06-25'],
            ['2024-06-24', DayOfWeek::WEDNESDAY, '2024-06-26'],
            ['2024-06-24', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-24', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-24', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-24', DayOfWeek::SUNDAY, '2024-06-30'],

            ['2024-06-25', DayOfWeek::TUESDAY, '2024-06-25'],
            ['2024-06-25', DayOfWeek::WEDNESDAY, '2024-06-26'],
            ['2024-06-25', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-25', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-25', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-25', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-25', DayOfWeek::MONDAY, '2024-07-01'],

            ['2024-06-26', DayOfWeek::WEDNESDAY, '2024-06-26'],
            ['2024-06-26', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-26', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-26', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-26', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-26', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-26', DayOfWeek::TUESDAY, '2024-07-02'],

            ['2024-06-27', DayOfWeek::THURSDAY, '2024-06-27'],
            ['2024-06-27', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-27', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-27', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-27', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-27', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-27', DayOfWeek::WEDNESDAY, '2024-07-03'],

            ['2024-06-28', DayOfWeek::FRIDAY, '2024-06-28'],
            ['2024-06-28', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-28', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-28', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-28', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-28', DayOfWeek::WEDNESDAY, '2024-07-03'],
            ['2024-06-28', DayOfWeek::THURSDAY, '2024-07-04'],

            ['2024-06-29', DayOfWeek::SATURDAY, '2024-06-29'],
            ['2024-06-29', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-29', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-29', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-29', DayOfWeek::WEDNESDAY, '2024-07-03'],
            ['2024-06-29', DayOfWeek::THURSDAY, '2024-07-04'],
            ['2024-06-29', DayOfWeek::FRIDAY, '2024-07-05'],

            ['2024-06-30', DayOfWeek::SUNDAY, '2024-06-30'],
            ['2024-06-30', DayOfWeek::MONDAY, '2024-07-01'],
            ['2024-06-30', DayOfWeek::TUESDAY, '2024-07-02'],
            ['2024-06-30', DayOfWeek::WEDNESDAY, '2024-07-03'],
            ['2024-06-30', DayOfWeek::THURSDAY, '2024-07-04'],
            ['2024-06-30', DayOfWeek::FRIDAY, '2024-07-05'],
            ['2024-06-30', DayOfWeek::SATURDAY, '2024-07-06'],
        ];
    }

    public function testFromNativeDateTime(): void
    {
        $dateTime = new DateTime('2018-07-21');
        self::assertLocalDateIs(2018, 7, 21, LocalDate::fromNativeDateTime($dateTime));
    }

    /**
     * @param int    $epochSecond The epoch second to set the clock to.
     * @param string $timeZone    The time-zone to get the date in.
     * @param int    $year        The expected year.
     * @param int    $month       The expected month.
     * @param int    $day         The expected day.
     */
    #[DataProvider('providerNow')]
    public function testNow(int $epochSecond, string $timeZone, int $year, int $month, int $day): void
    {
        $clock = new FixedClock(Instant::of($epochSecond));
        self::assertLocalDateIs($year, $month, $day, LocalDate::now(TimeZone::parse($timeZone), $clock));
    }

    public static function providerNow(): array
    {
        return [
            [0, '-01:00', 1969, 12, 31],
            [0, '+00:00', 1970, 1, 1],
            [1407970800, '+01:00', 2014, 8, 14],
            [1407970800, '-01:00', 2014, 8, 13],
        ];
    }

    public function testMin(): void
    {
        $min = LocalDate::min();

        self::assertLocalDateIs(Year::MIN_VALUE, 1, 1, $min);
        self::assertSame($min, LocalDate::min());
    }

    public function testMax(): void
    {
        $max = LocalDate::max();

        self::assertLocalDateIs(Year::MAX_VALUE, 12, 31, $max);
        self::assertSame($max, LocalDate::max());
    }

    #[DataProvider('providerGetYearMonth')]
    public function testGetYearMonth(int $year, int $month, int $day): void
    {
        self::assertYearMonthIs($year, $month, LocalDate::of($year, $month, $day)->getYearMonth());
    }

    public static function providerGetYearMonth(): array
    {
        return [
            [2001, 2, 28],
            [2002, 3, 1],
            [2018, 12, 31],
        ];
    }

    /**
     * @param string    $localDate The local date to test, as a string.
     * @param DayOfWeek $dayOfWeek The expected day-of-week.
     */
    #[DataProvider('providerDayOfWeek')]
    public function testGetDayOfWeek(string $localDate, DayOfWeek $dayOfWeek): void
    {
        self::assertSame($dayOfWeek, LocalDate::parse($localDate)->getDayOfWeek());
    }

    public static function providerDayOfWeek(): array
    {
        return [
            ['2000-01-01', DayOfWeek::SATURDAY],
            ['2000-01-03', DayOfWeek::MONDAY],
            ['2000-02-08', DayOfWeek::TUESDAY],
            ['2000-03-08', DayOfWeek::WEDNESDAY],
            ['2000-04-06', DayOfWeek::THURSDAY],
            ['2000-05-05', DayOfWeek::FRIDAY],
            ['2000-06-03', DayOfWeek::SATURDAY],
            ['2000-07-09', DayOfWeek::SUNDAY],
            ['2000-08-07', DayOfWeek::MONDAY],
            ['2000-09-05', DayOfWeek::TUESDAY],
            ['2000-10-11', DayOfWeek::WEDNESDAY],
            ['2000-11-16', DayOfWeek::THURSDAY],
            ['2000-12-29', DayOfWeek::FRIDAY],
            ['2001-01-01', DayOfWeek::MONDAY],
            ['2001-02-06', DayOfWeek::TUESDAY],
            ['2001-03-07', DayOfWeek::WEDNESDAY],
            ['2001-04-05', DayOfWeek::THURSDAY],
            ['2001-05-04', DayOfWeek::FRIDAY],
            ['2001-06-09', DayOfWeek::SATURDAY],
            ['2001-07-08', DayOfWeek::SUNDAY],
            ['2001-08-06', DayOfWeek::MONDAY],
            ['2001-09-04', DayOfWeek::TUESDAY],
            ['2001-10-10', DayOfWeek::WEDNESDAY],
            ['2001-11-15', DayOfWeek::THURSDAY],
            ['2001-12-21', DayOfWeek::FRIDAY],
            ['2002-01-01', DayOfWeek::TUESDAY],
            ['2003-01-01', DayOfWeek::WEDNESDAY],
            ['2004-01-01', DayOfWeek::THURSDAY],
            ['2005-01-01', DayOfWeek::SATURDAY],
            ['2006-01-01', DayOfWeek::SUNDAY],
            ['2007-01-01', DayOfWeek::MONDAY],
            ['2008-01-01', DayOfWeek::TUESDAY],
            ['2009-01-01', DayOfWeek::THURSDAY],
            ['2010-01-01', DayOfWeek::FRIDAY],
            ['2011-01-01', DayOfWeek::SATURDAY],
            ['2012-01-01', DayOfWeek::SUNDAY],
        ];
    }

    /**
     * @param int $year      The year.
     * @param int $month     The expected month.
     * @param int $day       The expected day.
     * @param int $dayOfYear The day-of-year.
     */
    #[DataProvider('providerDayOfYear')]
    public function testOfYearDay(int $year, int $month, int $day, int $dayOfYear): void
    {
        self::assertLocalDateIs($year, $month, $day, LocalDate::ofYearDay($year, $dayOfYear));
    }

    /**
     * @param int $year      The year to test.
     * @param int $month     The month to test.
     * @param int $day       The day-of-month to test.
     * @param int $dayOfYear The expected day-of-year number.
     */
    #[DataProvider('providerDayOfYear')]
    public function testGetDayOfYear(int $year, int $month, int $day, int $dayOfYear): void
    {
        self::assertSame($dayOfYear, LocalDate::of($year, $month, $day)->getDayOfYear());
    }

    public static function providerDayOfYear(): array
    {
        return [
            [2000, 1, 1, 1],
            [2000, 1, 31, 31],
            [2000, 2, 1, 32],
            [2000, 2, 29, 60],
            [2000, 3, 1, 61],
            [2000, 3, 31, 91],
            [2000, 4, 1, 92],
            [2000, 4, 30, 121],
            [2000, 5, 1, 122],
            [2000, 5, 31, 152],
            [2000, 6, 1, 153],
            [2000, 6, 30, 182],
            [2000, 7, 1, 183],
            [2000, 7, 31, 213],
            [2000, 8, 1, 214],
            [2000, 8, 31, 244],
            [2000, 9, 1, 245],
            [2000, 9, 30, 274],
            [2000, 10, 1, 275],
            [2000, 10, 31, 305],
            [2000, 11, 1, 306],
            [2000, 11, 30, 335],
            [2000, 12, 1, 336],
            [2000, 12, 31, 366],
            [2001, 1, 1, 1],
            [2001, 1, 31, 31],
            [2001, 2, 1, 32],
            [2001, 2, 28, 59],
            [2001, 3, 1, 60],
            [2001, 3, 31, 90],
            [2001, 4, 1, 91],
            [2001, 4, 30, 120],
            [2001, 5, 1, 121],
            [2001, 5, 31, 151],
            [2001, 6, 1, 152],
            [2001, 6, 30, 181],
            [2001, 7, 1, 182],
            [2001, 7, 31, 212],
            [2001, 8, 1, 213],
            [2001, 8, 31, 243],
            [2001, 9, 1, 244],
            [2001, 9, 30, 273],
            [2001, 10, 1, 274],
            [2001, 10, 31, 304],
            [2001, 11, 1, 305],
            [2001, 11, 30, 334],
            [2001, 12, 1, 335],
            [2001, 12, 31, 365],
        ];
    }

    public static function providerGetYearWeek(): array
    {
        return [
            [2000,  1,  1, 1999, 52],
            [2000,  1,  2, 1999, 52],
            [2000,  1,  3, 2000,  1],
            [2000,  1,  9, 2000,  1],
            [2000,  1, 10, 2000,  2],
            [2000,  1, 16, 2000,  2],
            [2000,  1, 17, 2000,  3],
            [2000,  1, 23, 2000,  3],
            [2000,  1, 24, 2000,  4],
            [2000,  1, 30, 2000,  4],
            [2000,  1, 31, 2000,  5],
            [2000,  2,  6, 2000,  5],
            [2000,  2,  7, 2000,  6],
            [2000,  2, 13, 2000,  6],
            [2000,  2, 14, 2000,  7],
            [2000,  2, 20, 2000,  7],
            [2000,  2, 21, 2000,  8],
            [2000,  2, 27, 2000,  8],
            [2000,  2, 28, 2000,  9],
            [2000,  3,  5, 2000,  9],
            [2000,  3,  6, 2000, 10],
            [2000,  3, 12, 2000, 10],
            [2000,  3, 13, 2000, 11],
            [2000,  3, 19, 2000, 11],
            [2000,  3, 20, 2000, 12],
            [2000,  3, 26, 2000, 12],
            [2000,  3, 27, 2000, 13],
            [2000,  4,  2, 2000, 13],
            [2000,  4,  3, 2000, 14],
            [2000,  4,  9, 2000, 14],
            [2000,  4, 10, 2000, 15],
            [2000,  4, 16, 2000, 15],
            [2000,  4, 17, 2000, 16],
            [2000,  4, 23, 2000, 16],
            [2000,  4, 24, 2000, 17],
            [2000,  4, 30, 2000, 17],
            [2000,  5,  1, 2000, 18],
            [2000,  5,  7, 2000, 18],
            [2000,  5,  8, 2000, 19],
            [2000,  5, 14, 2000, 19],
            [2000,  5, 15, 2000, 20],
            [2000,  5, 21, 2000, 20],
            [2000,  5, 22, 2000, 21],
            [2000,  5, 28, 2000, 21],
            [2000,  5, 29, 2000, 22],
            [2000,  6,  4, 2000, 22],
            [2000,  6,  5, 2000, 23],
            [2000,  6, 11, 2000, 23],
            [2000,  6, 12, 2000, 24],
            [2000,  6, 18, 2000, 24],
            [2000,  6, 19, 2000, 25],
            [2000,  6, 25, 2000, 25],
            [2000,  6, 26, 2000, 26],
            [2000,  7,  2, 2000, 26],
            [2000,  7,  3, 2000, 27],
            [2000,  7,  9, 2000, 27],
            [2000,  7, 10, 2000, 28],
            [2000,  7, 16, 2000, 28],
            [2000,  7, 17, 2000, 29],
            [2000,  7, 23, 2000, 29],
            [2000,  7, 24, 2000, 30],
            [2000,  7, 30, 2000, 30],
            [2000,  7, 31, 2000, 31],
            [2000,  8,  6, 2000, 31],
            [2000,  8,  7, 2000, 32],
            [2000,  8, 13, 2000, 32],
            [2000,  8, 14, 2000, 33],
            [2000,  8, 20, 2000, 33],
            [2000,  8, 21, 2000, 34],
            [2000,  8, 27, 2000, 34],
            [2000,  8, 28, 2000, 35],
            [2000,  9,  3, 2000, 35],
            [2000,  9,  4, 2000, 36],
            [2000,  9, 10, 2000, 36],
            [2000,  9, 11, 2000, 37],
            [2000,  9, 17, 2000, 37],
            [2000,  9, 18, 2000, 38],
            [2000,  9, 24, 2000, 38],
            [2000,  9, 25, 2000, 39],
            [2000, 10,  1, 2000, 39],
            [2000, 10,  2, 2000, 40],
            [2000, 10,  8, 2000, 40],
            [2000, 10,  9, 2000, 41],
            [2000, 10, 15, 2000, 41],
            [2000, 10, 16, 2000, 42],
            [2000, 10, 22, 2000, 42],
            [2000, 10, 23, 2000, 43],
            [2000, 10, 29, 2000, 43],
            [2000, 10, 30, 2000, 44],
            [2000, 11,  5, 2000, 44],
            [2000, 11,  6, 2000, 45],
            [2000, 11, 12, 2000, 45],
            [2000, 11, 13, 2000, 46],
            [2000, 11, 19, 2000, 46],
            [2000, 11, 20, 2000, 47],
            [2000, 11, 26, 2000, 47],
            [2000, 11, 27, 2000, 48],
            [2000, 12,  3, 2000, 48],
            [2000, 12,  4, 2000, 49],
            [2000, 12, 10, 2000, 49],
            [2000, 12, 11, 2000, 50],
            [2000, 12, 17, 2000, 50],
            [2000, 12, 18, 2000, 51],
            [2000, 12, 24, 2000, 51],
            [2000, 12, 25, 2000, 52],
            [2000, 12, 31, 2000, 52],
            [2001,  1,  1, 2001,  1],
            [2001,  1,  7, 2001,  1],
            [2001,  1,  8, 2001,  2],
            [2001,  3, 25, 2001, 12],
            [2001,  3, 26, 2001, 13],
            [2001,  9,  9, 2001, 36],
            [2001,  9, 10, 2001, 37],
            [2001, 12, 30, 2001, 52],
            [2001, 12, 31, 2002,  1],
            [2002,  1,  1, 2002,  1],
            [2002,  1,  6, 2002,  1],
            [2002,  1,  7, 2002,  2],
            [2002, 12, 29, 2002, 52],
            [2002, 12, 30, 2003,  1],
            [2002, 12, 31, 2003,  1],
            [2003,  1,  1, 2003,  1],
            [2003,  1,  5, 2003,  1],
            [2003,  1,  6, 2003,  2],
            [2003, 12, 28, 2003, 52],
            [2003, 12, 29, 2004,  1],
            [2003, 12, 31, 2004,  1],
            [2004,  1,  1, 2004,  1],
            [2004,  1,  4, 2004,  1],
            [2004,  1,  5, 2004,  2],
            [2004, 12, 26, 2004, 52],
            [2004, 12, 27, 2004, 53],
            [2004, 12, 31, 2004, 53],
            [2005,  1,  1, 2004, 53],
            [2005,  1,  2, 2004, 53],
            [2005,  1,  3, 2005,  1],
        ];
    }

    #[DataProvider('providerGetYearWeek')]
    public function testGetYearWeek(int $year, int $month, int $day, int $expectedYear, int $expectedWeek): void
    {
        $yearWeek = LocalDate::of($year, $month, $day)->getYearWeek();
        self::assertYearWeekIs($expectedYear, $expectedWeek, $yearWeek);
    }

    /**
     * @param int $year        The base year.
     * @param int $month       The base month.
     * @param int $day         The base day-of-month.
     * @param int $newYear     The new year.
     * @param int $expectedDay The expected day-of-month of the resulting date.
     */
    #[DataProvider('providerWithYear')]
    public function testWithYear(int $year, int $month, int $day, int $newYear, int $expectedDay): void
    {
        $localDate = LocalDate::of($year, $month, $day)->withYear($newYear);
        self::assertLocalDateIs($newYear, $month, $expectedDay, $localDate);
    }

    public static function providerWithYear(): array
    {
        return [
            [2007, 3, 31, 2008, 31],
            [2007, 2, 28, 2008, 28],
            [2008, 2, 28, 2009, 28],
            [2008, 2, 29, 2008, 29],
            [2008, 2, 29, 2009, 28],
            [2008, 2, 29, 2012, 29],
        ];
    }

    /**
     * @param int $invalidYear The year to test.
     */
    #[DataProvider('providerWithInvalidYearThrowsException')]
    public function testWithInvalidYearThrowsException(int $invalidYear): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::of(2001, 2, 3)->withYear($invalidYear);
    }

    public static function providerWithInvalidYearThrowsException(): array
    {
        return [
            [-1000000],
            [1000000],
        ];
    }

    /**
     * @param int $year        The base year.
     * @param int $month       The base month.
     * @param int $day         The base day-of-month.
     * @param int $newMonth    The new month.
     * @param int $expectedDay The expected day-of-month of the resulting date.
     */
    #[DataProvider('providerWithMonth')]
    public function testWithMonth(int $year, int $month, int $day, int $newMonth, int $expectedDay): void
    {
        self::assertLocalDateIs($year, $newMonth, $expectedDay, LocalDate::of($year, $month, $day)->withMonth($newMonth));
        self::assertLocalDateIs($year, $newMonth, $expectedDay, LocalDate::of($year, $month, $day)->withMonth(Month::from($newMonth)));
    }

    public static function providerWithMonth(): array
    {
        return [
            [2007, 3, 31, 2, 28],
            [2008, 3, 31, 2, 29],
            [2007, 3, 31, 1, 31],
            [2008, 3, 31, 3, 31],
            [2007, 3, 31, 4, 30],
            [2008, 3, 31, 5, 31],
            [2007, 3, 31, 6, 30],
            [2008, 3, 31, 7, 31],
            [2007, 3, 31, 8, 31],
            [2008, 3, 31, 9, 30],
            [2007, 3, 31, 10, 31],
            [2008, 3, 31, 11, 30],
            [2007, 3, 31, 12, 31],
            [2008, 4, 30, 12, 30],
        ];
    }

    /**
     * @param int $invalidMonth The month to test.
     */
    #[DataProvider('providerWithInvalidYearThrowsException')]
    public function testWithInvalidMonthThrowsException(int $invalidMonth): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::of(2001, 2, 3)->atTime(LocalTime::of(4, 5, 6))->withMonth($invalidMonth);
    }

    public static function providerWithInvalidMonthThrowsException(): array
    {
        return [
            [0],
            [13],
        ];
    }

    /**
     * @param int $year   The base year.
     * @param int $month  The base month.
     * @param int $day    The base day-of-month.
     * @param int $newDay The new day-of-month.
     */
    #[DataProvider('providerWithDay')]
    public function testWithDay(int $year, int $month, int $day, int $newDay): void
    {
        $localDate = LocalDate::of($year, $month, $day)->withDay($newDay);
        self::assertLocalDateIs($year, $month, $newDay, $localDate);
    }

    public static function providerWithDay(): array
    {
        return [
            [2007, 6, 2, 2],
            [2007, 1, 1, 31],
            [2008, 2, 28, 29],
            [2010, 2, 27, 28],
        ];
    }

    /**
     * @param int $year   The base year.
     * @param int $month  The base month.
     * @param int $day    The base day-of-month.
     * @param int $newDay The new day-of-month.
     */
    #[DataProvider('providerWithInvalidDayThrowsException')]
    public function testWithInvalidDayThrowsException(int $year, int $month, int $day, int $newDay): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::of($year, $month, $day)->withDay($newDay);
    }

    public static function providerWithInvalidDayThrowsException(): array
    {
        return [
            [2007, 1, 1, 0],
            [2007, 1, 1, 32],
            [2007, 2, 1, 29],
            [2008, 2, 1, 30],
            [2009, 4, 1, 31],
        ];
    }

    /**
     * @param int $y  The year of the base date.
     * @param int $m  The month of the base date.
     * @param int $d  The day of the base date.
     * @param int $py The number of years in the period.
     * @param int $pm The number of months in the period.
     * @param int $pd The number of days in the period.
     * @param int $ey The expected year of the result date.
     * @param int $em The expected month of the result date.
     * @param int $ed The expected day of the result date.
     */
    #[DataProvider('providerPeriod')]
    public function testPlusPeriod(int $y, int $m, int $d, int $py, int $pm, int $pd, int $ey, int $em, int $ed): void
    {
        $date = LocalDate::of($y, $m, $d);
        $period = Period::of($py, $pm, $pd);

        self::assertLocalDateIs($ey, $em, $ed, $date->plusPeriod($period));
    }

    /**
     * @param int $y  The year of the base date.
     * @param int $m  The month of the base date.
     * @param int $d  The day of the base date.
     * @param int $py The number of years in the period.
     * @param int $pm The number of months in the period.
     * @param int $pd The number of days in the period.
     * @param int $ey The expected year of the result date.
     * @param int $em The expected month of the result date.
     * @param int $ed The expected day of the result date.
     */
    #[DataProvider('providerPeriod')]
    public function testMinusPeriod(int $y, int $m, int $d, int $py, int $pm, int $pd, int $ey, int $em, int $ed): void
    {
        $date = LocalDate::of($y, $m, $d);
        $period = Period::of(-$py, -$pm, -$pd);

        self::assertLocalDateIs($ey, $em, $ed, $date->minusPeriod($period));
    }

    public static function providerPeriod(): array
    {
        return [
            [2001, 2, 3,  0,   0,   0, 2001,  2,  3],
            [2001, 2, 3,  0,   0,   1, 2001,  2,  4],
            [2001, 2, 3,  0,   0,  -1, 2001,  2,  2],
            [2001, 2, 3,  0,   1,   0, 2001,  3,  3],
            [2001, 2, 3,  0,  -1,   0, 2001,  1,  3],
            [2001, 2, 3,  1,   0,   0, 2002,  2,  3],
            [2001, 2, 3, -1,   0,   0, 2000,  2,  3],
            [2001, 2, 3,  0,   0,  30, 2001,  3,  5],
            [2001, 2, 3,  0,  30,  50, 2003,  9, 22],
            [2001, 2, 3,  0,   0, -30, 2001,  1,  4],
            [2001, 2, 3,  0, -30, -50, 1998,  6, 14],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $ay The number of years to add.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerPlusYears')]
    public function testPlusYears(int $y, int $m, int $d, int $ay, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusYears($ay));
    }

    public static function providerPlusYears(): array
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2016, 2, 3],
            [2016, 3, 4, -1, 2015, 3, 4],
            [2000, 2, 29, 1, 2001, 2, 28],
            [2000, 2, 29, -1, 1999, 2, 28],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $am The number of months to add.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerPlusMonths')]
    public function testPlusMonths(int $y, int $m, int $d, int $am, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusMonths($am));
    }

    public static function providerPlusMonths(): array
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2015, 3, 3],
            [2015, 2, 3, 12, 2016, 2, 3],
            [2015, 2, 3, 13, 2016, 3, 3],
            [2016, 3, 4, -1, 2016, 2, 4],
            [2016, 3, 4, -3, 2015, 12, 4],
            [2016, 3, 4, -12, 2015, 3, 4],
            [2011, 12, 31, 1, 2012, 1, 31],
            [2011, 12, 31, 2, 2012, 2, 29],
            [2012, 12, 31, 1, 2013, 1, 31],
            [2012, 12, 31, 2, 2013, 2, 28],
            [2012, 12, 31, 3, 2013, 3, 31],
            [2013, 12, 31, 2, 2014, 2, 28],
            [2013, 12, 31, 4, 2014, 4, 30],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $aw The number of weeks to add.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerPlusWeeks')]
    public function testPlusWeeks(int $y, int $m, int $d, int $aw, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusWeeks($aw));
    }

    public static function providerPlusWeeks(): array
    {
        return [
            [2014, 7, 31, 0, 2014, 7, 31],
            [2014, 7, 31, 1, 2014, 8, 7],
            [2014, 7, 31, 5, 2014, 9, 4],
            [2014, 7, 31, 30, 2015, 2, 26],
            [2014, 8, 2, 30, 2015, 2, 28],
            [2014, 8, 3, 30, 2015, 3, 1],
            [2014, 7, 31, -9, 2014, 5, 29],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $ad The number of days to add.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerPlusDays')]
    public function testPlusDays(int $y, int $m, int $d, int $ad, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->plusDays($ad));
    }

    public static function providerPlusDays(): array
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2014, 1, 2, 29, 2014, 1, 31],
            [2014, 1, 2, 30, 2014, 2, 1],
            [2014, 1, 2, 365, 2015, 1, 2],
            [2012, 1, 1, 365, 2012, 12, 31],
            [2012, 1, 1, 366, 2013, 1, 1],
            [2012, 1, 2, -1, 2012, 1, 1],
            [2012, 1, 1, -1, 2011, 12, 31],
            [2014, 2, 1, 1, 2014, 2, 2],
            [2014, 2, 15, 1, 2014, 2, 16],
            [2014, 2, 28, 1, 2014, 3, 1],
            [2012, 2, 28, 1, 2012, 2, 29],
            [2012, 2, 29, 1, 2012, 3, 1],
            [2014, 12, 31, 1, 2015, 1, 1],
        ];
    }

    #[DataProvider('providerPlusWeekdays')]
    public function testPlusWeekdays(string $date, int $days, string $expectedDate): void
    {
        self::assertSame($expectedDate, (string) LocalDate::parse($date)->plusWeekdays($days));
    }

    #[DataProvider('providerPlusWeekdays')]
    public function testMinusWeekdays(string $date, int $days, string $expectedDate): void
    {
        self::assertSame($expectedDate, (string) LocalDate::parse($date)->minusWeekdays(-$days));
    }

    public static function providerPlusWeekdays(): array
    {
        return [
            ['2020-11-02', -10, '2020-10-19'],
            ['2020-11-02',  -9, '2020-10-20'],
            ['2020-11-02',  -8, '2020-10-21'],
            ['2020-11-02',  -7, '2020-10-22'],
            ['2020-11-02',  -6, '2020-10-23'],
            ['2020-11-02',  -5, '2020-10-26'],
            ['2020-11-02',  -4, '2020-10-27'],
            ['2020-11-02',  -3, '2020-10-28'],
            ['2020-11-02',  -2, '2020-10-29'],
            ['2020-11-02',  -1, '2020-10-30'],
            ['2020-11-02',   0, '2020-11-02'],
            ['2020-11-02',   1, '2020-11-03'],
            ['2020-11-02',   2, '2020-11-04'],
            ['2020-11-02',   3, '2020-11-05'],
            ['2020-11-02',   4, '2020-11-06'],
            ['2020-11-02',   5, '2020-11-09'],
            ['2020-11-02',   6, '2020-11-10'],
            ['2020-11-02',   7, '2020-11-11'],
            ['2020-11-02',   8, '2020-11-12'],
            ['2020-11-02',   9, '2020-11-13'],
            ['2020-11-02',  10, '2020-11-16'],

            ['2020-11-03', -10, '2020-10-20'],
            ['2020-11-03',  -9, '2020-10-21'],
            ['2020-11-03',  -8, '2020-10-22'],
            ['2020-11-03',  -7, '2020-10-23'],
            ['2020-11-03',  -6, '2020-10-26'],
            ['2020-11-03',  -5, '2020-10-27'],
            ['2020-11-03',  -4, '2020-10-28'],
            ['2020-11-03',  -3, '2020-10-29'],
            ['2020-11-03',  -2, '2020-10-30'],
            ['2020-11-03',  -1, '2020-11-02'],
            ['2020-11-03',   0, '2020-11-03'],
            ['2020-11-03',   1, '2020-11-04'],
            ['2020-11-03',   2, '2020-11-05'],
            ['2020-11-03',   3, '2020-11-06'],
            ['2020-11-03',   4, '2020-11-09'],
            ['2020-11-03',   5, '2020-11-10'],
            ['2020-11-03',   6, '2020-11-11'],
            ['2020-11-03',   7, '2020-11-12'],
            ['2020-11-03',   8, '2020-11-13'],
            ['2020-11-03',   9, '2020-11-16'],
            ['2020-11-03',  10, '2020-11-17'],

            ['2020-11-04', -10, '2020-10-21'],
            ['2020-11-04',  -9, '2020-10-22'],
            ['2020-11-04',  -8, '2020-10-23'],
            ['2020-11-04',  -7, '2020-10-26'],
            ['2020-11-04',  -6, '2020-10-27'],
            ['2020-11-04',  -5, '2020-10-28'],
            ['2020-11-04',  -4, '2020-10-29'],
            ['2020-11-04',  -3, '2020-10-30'],
            ['2020-11-04',  -2, '2020-11-02'],
            ['2020-11-04',  -1, '2020-11-03'],
            ['2020-11-04',   0, '2020-11-04'],
            ['2020-11-04',   1, '2020-11-05'],
            ['2020-11-04',   2, '2020-11-06'],
            ['2020-11-04',   3, '2020-11-09'],
            ['2020-11-04',   4, '2020-11-10'],
            ['2020-11-04',   5, '2020-11-11'],
            ['2020-11-04',   6, '2020-11-12'],
            ['2020-11-04',   7, '2020-11-13'],
            ['2020-11-04',   8, '2020-11-16'],
            ['2020-11-04',   9, '2020-11-17'],
            ['2020-11-04',  10, '2020-11-18'],

            ['2020-11-05', -10, '2020-10-22'],
            ['2020-11-05',  -9, '2020-10-23'],
            ['2020-11-05',  -8, '2020-10-26'],
            ['2020-11-05',  -7, '2020-10-27'],
            ['2020-11-05',  -6, '2020-10-28'],
            ['2020-11-05',  -5, '2020-10-29'],
            ['2020-11-05',  -4, '2020-10-30'],
            ['2020-11-05',  -3, '2020-11-02'],
            ['2020-11-05',  -2, '2020-11-03'],
            ['2020-11-05',  -1, '2020-11-04'],
            ['2020-11-05',   0, '2020-11-05'],
            ['2020-11-05',   1, '2020-11-06'],
            ['2020-11-05',   2, '2020-11-09'],
            ['2020-11-05',   3, '2020-11-10'],
            ['2020-11-05',   4, '2020-11-11'],
            ['2020-11-05',   5, '2020-11-12'],
            ['2020-11-05',   6, '2020-11-13'],
            ['2020-11-05',   7, '2020-11-16'],
            ['2020-11-05',   8, '2020-11-17'],
            ['2020-11-05',   9, '2020-11-18'],
            ['2020-11-05',  10, '2020-11-19'],

            ['2020-11-06', -10, '2020-10-23'],
            ['2020-11-06',  -9, '2020-10-26'],
            ['2020-11-06',  -8, '2020-10-27'],
            ['2020-11-06',  -7, '2020-10-28'],
            ['2020-11-06',  -6, '2020-10-29'],
            ['2020-11-06',  -5, '2020-10-30'],
            ['2020-11-06',  -4, '2020-11-02'],
            ['2020-11-06',  -3, '2020-11-03'],
            ['2020-11-06',  -2, '2020-11-04'],
            ['2020-11-06',  -1, '2020-11-05'],
            ['2020-11-06',   0, '2020-11-06'],
            ['2020-11-06',   1, '2020-11-09'],
            ['2020-11-06',   2, '2020-11-10'],
            ['2020-11-06',   3, '2020-11-11'],
            ['2020-11-06',   4, '2020-11-12'],
            ['2020-11-06',   5, '2020-11-13'],
            ['2020-11-06',   6, '2020-11-16'],
            ['2020-11-06',   7, '2020-11-17'],
            ['2020-11-06',   8, '2020-11-18'],
            ['2020-11-06',   9, '2020-11-19'],
            ['2020-11-06',  10, '2020-11-20'],

            ['2020-11-07', -10, '2020-10-26'],
            ['2020-11-07',  -9, '2020-10-27'],
            ['2020-11-07',  -8, '2020-10-28'],
            ['2020-11-07',  -7, '2020-10-29'],
            ['2020-11-07',  -6, '2020-10-30'],
            ['2020-11-07',  -5, '2020-11-02'],
            ['2020-11-07',  -4, '2020-11-03'],
            ['2020-11-07',  -3, '2020-11-04'],
            ['2020-11-07',  -2, '2020-11-05'],
            ['2020-11-07',  -1, '2020-11-06'],
            ['2020-11-07',   0, '2020-11-07'],
            ['2020-11-07',   1, '2020-11-09'],
            ['2020-11-07',   2, '2020-11-10'],
            ['2020-11-07',   3, '2020-11-11'],
            ['2020-11-07',   4, '2020-11-12'],
            ['2020-11-07',   5, '2020-11-13'],
            ['2020-11-07',   6, '2020-11-16'],
            ['2020-11-07',   7, '2020-11-17'],
            ['2020-11-07',   8, '2020-11-18'],
            ['2020-11-07',   9, '2020-11-19'],
            ['2020-11-07',  10, '2020-11-20'],

            ['2020-11-08', -10, '2020-10-26'],
            ['2020-11-08',  -9, '2020-10-27'],
            ['2020-11-08',  -8, '2020-10-28'],
            ['2020-11-08',  -7, '2020-10-29'],
            ['2020-11-08',  -6, '2020-10-30'],
            ['2020-11-08',  -5, '2020-11-02'],
            ['2020-11-08',  -4, '2020-11-03'],
            ['2020-11-08',  -3, '2020-11-04'],
            ['2020-11-08',  -2, '2020-11-05'],
            ['2020-11-08',  -1, '2020-11-06'],
            ['2020-11-08',   0, '2020-11-08'],
            ['2020-11-08',   1, '2020-11-09'],
            ['2020-11-08',   2, '2020-11-10'],
            ['2020-11-08',   3, '2020-11-11'],
            ['2020-11-08',   4, '2020-11-12'],
            ['2020-11-08',   5, '2020-11-13'],
            ['2020-11-08',   6, '2020-11-16'],
            ['2020-11-08',   7, '2020-11-17'],
            ['2020-11-08',   8, '2020-11-18'],
            ['2020-11-08',   9, '2020-11-19'],
            ['2020-11-08',  10, '2020-11-20'],

            ['2019-01-29',  400, '2020-08-11'],
            ['2019-01-29', -400, '2017-07-18'],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $sy The number of years to subtract.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerMinusYears')]
    public function tesMinusYears(int $y, int $m, int $d, int $sy, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusYears($sy));
    }

    public static function providerMinusYears(): array
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2014, 2, 3],
            [2016, 3, 4, -1, 2015, 3, 4],
            [2000, 2, 29, 1, 1999, 2, 28],
            [2000, 2, 29, -1, 2001, 2, 28],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $sm The number of months to subtract.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerMinusMonths')]
    public function testMinusMonths(int $y, int $m, int $d, int $sm, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusMonths($sm));
    }

    public static function providerMinusMonths(): array
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2015, 2, 3, 1, 2015, 1, 3],
            [2015, 2, 3, 12, 2014, 2, 3],
            [2015, 2, 3, 13, 2014, 1, 3],
            [2016, 3, 4, -1, 2016, 4, 4],
            [2016, 3, 4, -10, 2017, 1, 4],
            [2016, 3, 4, -12, 2017, 3, 4],
            [2012, 1, 31, 1, 2011, 12, 31],
            [2011, 12, 31, 10, 2011, 2, 28],
            [2013, 12, 31, 22, 2012, 2, 29],
            [2012, 12, 31, 1, 2012, 11, 30],
            [2012, 12, 31, 2, 2012, 10, 31],
            [2013, 12, 31, -2, 2014, 2, 28],
            [2013, 12, 31, -26, 2016, 2, 29],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $sw The number of weeks to subtract.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerMinusWeeks')]
    public function testMinusWeeks(int $y, int $m, int $d, int $sw, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusWeeks($sw));
    }

    public static function providerMinusWeeks(): array
    {
        return [
            [2014, 7, 31, 0, 2014, 7, 31],
            [2014, 7, 31, 1, 2014, 7, 24],
            [2014, 7, 31, 5, 2014, 6, 26],
            [2014, 7, 31, 30, 2014, 1, 2],
            [2014, 8, 2, 30, 2014, 1, 4],
            [2014, 8, 3, 50, 2013, 8, 18],
            [2014, 7, 31, -50, 2015, 7, 16],
        ];
    }

    /**
     * @param int $y  The base year.
     * @param int $m  The base month.
     * @param int $d  The base day.
     * @param int $sd The number of days to subtract.
     * @param int $ey The expected resulting year.
     * @param int $em The expected resulting month.
     * @param int $ed The expected resulting day.
     */
    #[DataProvider('providerMinusDays')]
    public function testMinusDays(int $y, int $m, int $d, int $sd, int $ey, int $em, int $ed): void
    {
        self::assertLocalDateIs($ey, $em, $ed, LocalDate::of($y, $m, $d)->minusDays($sd));
    }

    public static function providerMinusDays(): array
    {
        return [
            [2014, 1, 2, 0, 2014, 1, 2],
            [2014, 1, 2, -29, 2014, 1, 31],
            [2014, 1, 2, -30, 2014, 2, 1],
            [2014, 1, 2, 365, 2013, 1, 2],
            [2013, 1, 1, 365, 2012, 1, 2],
            [2013, 1, 1, 366, 2012, 1, 1],
            [2013, 1, 1, 367, 2011, 12, 31],
            [2013, 1, 1, 1000, 2010, 4, 7],
        ];
    }

    /**
     * @param int $y1 The year of the 1st date.
     * @param int $m1 The month of the 1st date.
     * @param int $d1 The day of the 1st date.
     * @param int $y2 The year of the 2nd date.
     * @param int $m2 The month of the 2nd date.
     * @param int $d2 The day of the 2nd date.
     * @param int $y  The expected number of years in the period.
     * @param int $m  The expected number of months in the period.
     * @param int $d  The expected number of days in the period.
     */
    #[DataProvider('providerUntil')]
    public function testUntil(int $y1, int $m1, int $d1, int $y2, int $m2, int $d2, int $y, int $m, int $d): void
    {
        $date1 = LocalDate::of($y1, $m1, $d1);
        $date2 = LocalDate::of($y2, $m2, $d2);

        self::assertPeriodIs($y, $m, $d, $date1->until($date2));
    }

    public static function providerUntil(): array
    {
        return [
            [2010, 1, 15, 2010, 1, 15, 0, 0, 0],
            [2010, 1, 15, 2010, 1, 18, 0, 0, 3],
            [2010, 1, 15, 2010, 3, 15, 0, 2, 0],
            [2010, 1, 15, 2010, 3, 18, 0, 2, 3],
            [2010, 1, 15, 2011, 1, 15, 1, 0, 0],
            [2010, 1, 15, 2011, 1, 18, 1, 0, 3],
            [2010, 1, 15, 2011, 3, 15, 1, 2, 0],
            [2010, 1, 15, 2011, 3, 18, 1, 2, 3],
            [2010, 1, 18, 2010, 1, 15, 0, 0, -3],
            [2010, 1, 18, 2010, 1, 18, 0, 0, 0],
            [2010, 1, 18, 2010, 3, 15, 0, 1, 25],
            [2010, 1, 18, 2010, 3, 18, 0, 2, 0],
            [2010, 1, 18, 2011, 1, 15, 0, 11, 28],
            [2010, 1, 18, 2011, 1, 18, 1, 0, 0],
            [2010, 1, 18, 2011, 3, 15, 1, 1, 25],
            [2010, 1, 18, 2011, 3, 18, 1, 2, 0],
            [2010, 3, 15, 2010, 1, 15, 0, -2, 0],
            [2010, 3, 15, 2010, 1, 18, 0, -1, -28],
            [2010, 3, 15, 2010, 3, 15, 0, 0, 0],
            [2010, 3, 15, 2010, 3, 18, 0, 0, 3],
            [2010, 3, 15, 2011, 1, 15, 0, 10, 0],
            [2010, 3, 15, 2011, 1, 18, 0, 10, 3],
            [2010, 3, 15, 2011, 3, 15, 1, 0, 0],
            [2010, 3, 15, 2011, 3, 18, 1, 0, 3],
            [2010, 3, 18, 2010, 1, 15, 0, -2, -3],
            [2010, 3, 18, 2010, 1, 18, 0, -2, 0],
            [2010, 3, 18, 2010, 3, 15, 0, 0, -3],
            [2010, 3, 18, 2010, 3, 18, 0, 0, 0],
            [2010, 3, 18, 2011, 1, 15, 0, 9, 28],
            [2010, 3, 18, 2011, 1, 18, 0, 10, 0],
            [2010, 3, 18, 2011, 3, 15, 0, 11, 25],
            [2010, 3, 18, 2011, 3, 18, 1, 0, 0],
            [2011, 1, 15, 2010, 1, 15, -1, 0, 0],
            [2011, 1, 15, 2010, 1, 18, 0, -11, -28],
            [2011, 1, 15, 2010, 3, 15, 0, -10, 0],
            [2011, 1, 15, 2010, 3, 18, 0, -9, -28],
            [2011, 1, 15, 2011, 1, 15, 0, 0, 0],
            [2011, 1, 15, 2011, 1, 18, 0, 0, 3],
            [2011, 1, 15, 2011, 3, 15, 0, 2, 0],
            [2011, 1, 15, 2011, 3, 18, 0, 2, 3],
            [2011, 1, 18, 2010, 1, 15, -1, 0, -3],
            [2011, 1, 18, 2010, 1, 18, -1, 0, 0],
            [2011, 1, 18, 2010, 3, 15, 0, -10, -3],
            [2011, 1, 18, 2010, 3, 18, 0, -10, 0],
            [2011, 1, 18, 2011, 1, 15, 0, 0, -3],
            [2011, 1, 18, 2011, 1, 18, 0, 0, 0],
            [2011, 1, 18, 2011, 3, 15, 0, 1, 25],
            [2011, 1, 18, 2011, 3, 18, 0, 2, 0],
            [2011, 3, 15, 2010, 1, 15, -1, -2, 0],
            [2011, 3, 15, 2010, 1, 18, -1, -1, -28],
            [2011, 3, 15, 2010, 3, 15, -1, 0, 0],
            [2011, 3, 15, 2010, 3, 18, 0, -11, -28],
            [2011, 3, 15, 2011, 1, 15, 0, -2, 0],
            [2011, 3, 15, 2011, 1, 18, 0, -1, -28],
            [2011, 3, 15, 2011, 3, 15, 0, 0, 0],
            [2011, 3, 15, 2011, 3, 18, 0, 0, 3],
            [2011, 3, 18, 2010, 1, 15, -1, -2, -3],
            [2011, 3, 18, 2010, 1, 18, -1, -2, 0],
            [2011, 3, 18, 2010, 3, 15, -1, 0, -3],
            [2011, 3, 18, 2010, 3, 18, -1, 0, 0],
            [2011, 3, 18, 2011, 1, 15, 0, -2, -3],
            [2011, 3, 18, 2011, 1, 18, 0, -2, 0],
            [2011, 3, 18, 2011, 3, 15, 0, 0, -3],
            [2011, 3, 18, 2011, 3, 18, 0, 0, 0],

            [2012, 1, 18, 2012, 3, 15, 0, 1, 26],
            [2012, 1, 18, 2013, 1, 15, 0, 11, 28],
            [2012, 1, 18, 2013, 3, 15, 1, 1, 25],
            [2012, 3, 15, 2012, 1, 18, 0, -1, -28],
            [2012, 3, 18, 2013, 1, 15, 0, 9, 28],
            [2012, 3, 18, 2013, 3, 15, 0, 11, 25],
            [2013, 1, 15, 2012, 1, 18, 0, -11, -28],
            [2013, 1, 15, 2012, 3, 18, 0, -9, -28],
            [2013, 1, 18, 2013, 3, 15, 0, 1, 25],
            [2013, 3, 15, 2012, 1, 18, -1, -1, -28],
            [2013, 3, 15, 2012, 3, 18, 0, -11, -28],
            [2013, 3, 15, 2013, 1, 18, 0, -1, -28],

            [2011, 1, 18, 2011, 3, 15, 0, 1, 25],
            [2011, 1, 18, 2012, 1, 15, 0, 11, 28],
            [2011, 1, 18, 2012, 3, 15, 1, 1, 26],
            [2011, 3, 15, 2011, 1, 18, 0, -1, -28],
            [2011, 3, 18, 2012, 1, 15, 0, 9, 28],
            [2011, 3, 18, 2012, 3, 15, 0, 11, 26],
            [2012, 1, 15, 2011, 1, 18, 0, -11, -28],
            [2012, 1, 15, 2011, 3, 18, 0, -9, -28],
            [2012, 1, 18, 2012, 3, 15, 0, 1, 26],
            [2012, 3, 15, 2011, 1, 18, -1, -1, -28],
            [2012, 3, 15, 2011, 3, 18, 0, -11, -28],
            [2012, 3, 15, 2012, 1, 18, 0, -1, -28],
        ];
    }

    #[DataProvider('providerDaysUntil')]
    public function testDaysUntil(string $date1, string $date2, int $expectedDays): void
    {
        $date1 = LocalDate::parse($date1);
        $date2 = LocalDate::parse($date2);

        self::assertSame($expectedDays, $date1->daysUntil($date2));
    }

    public static function providerDaysUntil(): array
    {
        return [
            ['2018-01-01', '2020-01-01', 730],
            ['2020-01-01', '2022-01-01', 731],
            ['2018-01-15', '2018-02-15', 31],
            ['2018-02-15', '2018-03-15', 28],
            ['1900-02-18', '2031-09-27', 48068],
        ];
    }

    public function testAtTime(): void
    {
        $localDateTime = LocalDate::of(1, 2, 3)->atTime(LocalTime::of(4, 5, 6, 7));
        self::assertLocalDateTimeIs(1, 2, 3, 4, 5, 6, 7, $localDateTime);
    }

    /**
     * @param int  $y      The year of the date to test.
     * @param int  $m      The month of the date to test (should not matter).
     * @param int  $d      The day of the date to test (should not matter).
     * @param bool $isLeap Whether the year is a leap year.
     */
    #[DataProvider('providerIsLeapYear')]
    public function testIsLeapYear(int $y, int $m, int $d, bool $isLeap): void
    {
        self::assertSame($isLeap, LocalDate::of($y, $m, $d)->isLeapYear());
    }

    /**
     * @param int  $y      The year of the date to test.
     * @param int  $m      The month of the date to test (should not matter).
     * @param int  $d      The day of the date to test (should not matter).
     * @param bool $isLeap Whether the year is a leap year.
     */
    #[DataProvider('providerIsLeapYear')]
    public function testGetLengthOfYear(int $y, int $m, int $d, bool $isLeap): void
    {
        self::assertSame($isLeap ? 366 : 365, LocalDate::of($y, $m, $d)->getLengthOfYear());
    }

    public static function providerIsLeapYear(): array
    {
        return [
            [1600, 1, 11, true],
            [1700, 2, 12, false],
            [1800, 3, 13, false],
            [1900, 4, 14, false],
            [1999, 5, 15, false],
            [2000, 6, 16, true],
            [2004, 7, 17, true],
            [2007, 8, 18, false],
            [2008, 9, 18, true],
        ];
    }

    /**
     * @param int $y      The year of the date to test.
     * @param int $m      The month of the date to test.
     * @param int $d      The day of the date to test (should not matter).
     * @param int $length The length of the month.
     */
    #[DataProvider('providerGetLengthOfMonth')]
    public function testGetLengthOfMonth(int $y, int $m, int $d, int $length): void
    {
        self::assertSame($length, LocalDate::of($y, $m, $d)->getLengthOfMonth());
    }

    public static function providerGetLengthOfMonth(): array
    {
        return [
            [2000,  1,  2, 31],
            [2000,  2,  3, 29],
            [2001,  2,  3, 28],
            [2002,  3,  4, 31],
            [2003,  4,  5, 30],
            [2004,  5,  6, 31],
            [2004,  6,  7, 30],
            [2004,  7,  8, 31],
            [2004,  8,  9, 31],
            [2004,  9, 10, 30],
            [2004, 10, 11, 31],
            [2004, 11, 12, 30],
            [2004, 12, 13, 31],
        ];
    }

    /**
     * @param string $date1 The first date.
     * @param string $date2 The second date.
     * @param int    $cmp   The comparison value.
     */
    #[DataProvider('providerCompareTo')]
    public function testCompareTo(string $date1, string $date2, int $cmp): void
    {
        $date1 = LocalDate::parse($date1);
        $date2 = LocalDate::parse($date2);

        self::assertSame($cmp, $date1->compareTo($date2));
        self::assertSame($cmp === 0, $date1->isEqualTo($date2));
        self::assertSame($cmp === -1, $date1->isBefore($date2));
        self::assertSame($cmp === 1, $date1->isAfter($date2));
        self::assertSame($cmp <= 0, $date1->isBeforeOrEqualTo($date2));
        self::assertSame($cmp >= 0, $date1->isAfterOrEqualTo($date2));
    }

    public static function providerCompareTo(): array
    {
        return [
            ['2015-01-01', '2014-12-31', 1],
            ['2015-01-01', '2015-01-01', 0],
            ['2015-01-01', '2015-01-02', -1],
            ['2016-02-05', '2016-01-01', 1],
            ['2016-02-05', '2016-01-31', 1],
            ['2016-02-05', '2016-02-04', 1],
            ['2016-02-05', '2016-02-05', 0],
            ['2016-02-05', '2016-02-06', -1],
            ['2016-02-05', '2016-03-01', -1],
        ];
    }

    /**
     * @param int    $year     The year.
     * @param int    $month    The month.
     * @param int    $day      The day-of-month.
     * @param string $expected The expected result string.
     */
    #[DataProvider('providerToString')]
    public function testJsonSerialize(int $year, int $month, int $day, string $expected): void
    {
        self::assertSame(json_encode($expected, JSON_THROW_ON_ERROR), json_encode(LocalDate::of($year, $month, $day), JSON_THROW_ON_ERROR));
    }

    /**
     * @param int    $year     The year.
     * @param int    $month    The month.
     * @param int    $day      The day-of-month.
     * @param string $expected The expected result string.
     */
    #[DataProvider('providerToString')]
    public function testToISOString(int $year, int $month, int $day, string $expected): void
    {
        self::assertSame($expected, LocalDate::of($year, $month, $day)->toISOString());
    }

    /**
     * @param int    $year     The year.
     * @param int    $month    The month.
     * @param int    $day      The day-of-month.
     * @param string $expected The expected result string.
     */
    #[DataProvider('providerToString')]
    public function testToString(int $year, int $month, int $day, string $expected): void
    {
        self::assertSame($expected, (string) LocalDate::of($year, $month, $day));
    }

    public static function providerToString(): array
    {
        return [
            [-999999, 12, 31, '-999999-12-31'],
            [-185321, 11, 2, '-185321-11-02'],
            [-18532, 11, 2, '-18532-11-02'],
            [-2023, 11, 28, '-2023-11-28'],
            [-2023, 11, 2, '-2023-11-02'],
            [-2023, 1, 2, '-2023-01-02'],
            [-999, 1, 2, '-0999-01-02'],
            [-2, 1, 1, '-0002-01-01'],
            [2, 1, 1, '0002-01-01'],
            [999, 1, 2, '0999-01-02'],
            [2023, 1, 2, '2023-01-02'],
            [2023, 11, 2, '2023-11-02'],
            [2023, 11, 28, '2023-11-28'],
            [18532, 11, 2, '18532-11-02'],
            [185321, 11, 2, '185321-11-02'],
            [999999, 12, 31, '999999-12-31'],
        ];
    }

    public function testMinMaxOf(): void
    {
        $a = LocalDate::of(2015, 9, 30);
        $b = LocalDate::of(2016, 7, 31);
        $c = LocalDate::of(2017, 2, 1);

        self::assertSame($a, LocalDate::minOf($a, $b, $c));
        self::assertSame($c, LocalDate::maxOf($a, $b, $c));
    }

    public function testMinOfZeroElementsThrowsException(): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::minOf();
    }

    public function testMaxOfZeroElementsThrowsException(): void
    {
        $this->expectException(DateTimeException::class);
        LocalDate::maxOf();
    }

    /**
     * @param string $dateTime The date-time string that will be parse()d by LocalDate.
     * @param string $expected The expected output from the native DateTime object.
     */
    #[DataProvider('providerToNativeDateTime')]
    public function testToNativeDateTime(string $dateTime, string $expected): void
    {
        $localDate = LocalDate::parse($dateTime);
        $dateTime = $localDate->toNativeDateTime();

        self::assertInstanceOf(DateTime::class, $dateTime);
        self::assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    /**
     * @param string $dateTime The date-time string that will be parse()d by LocalDate.
     * @param string $expected The expected output from the native DateTime object.
     */
    #[DataProvider('providerToNativeDateTime')]
    public function testToNativeDateTimeImmutable(string $dateTime, string $expected): void
    {
        $localDate = LocalDate::parse($dateTime);
        $dateTime = $localDate->toNativeDateTimeImmutable();

        self::assertInstanceOf(DateTimeImmutable::class, $dateTime);
        self::assertSame($expected, $dateTime->format('Y-m-d\TH:i:s.uO'));
    }

    public static function providerToNativeDateTime(): array
    {
        return [
            ['2011-07-31', '2011-07-31T00:00:00.000000+0000'],
            ['2018-10-18', '2018-10-18T00:00:00.000000+0000'],
        ];
    }

    /**
     * @param string  $localDate The local date to test, as a string.
     * @param Quarter $quarter   The expected quarter numbered from 1 to 4.
     */
    #[DataProvider('providerQuarter')]
    public function testGetQuarter(string $localDate, Quarter $quarter): void
    {
        self::assertSame($quarter, LocalDate::parse($localDate)->getQuarter());
    }

    public static function providerQuarter(): array
    {
        return [
            ['2000-01-01', Quarter::FIRST],
            ['2000-02-15', Quarter::FIRST],
            ['2000-03-31', Quarter::FIRST],
            ['2000-04-01', Quarter::SECOND],
            ['2000-06-30', Quarter::SECOND],
            ['2000-07-01', Quarter::THIRD],
            ['2000-09-30', Quarter::THIRD],
            ['2000-10-01', Quarter::FOURTH],
            ['2000-12-31', Quarter::FOURTH],
            ['2001-01-01', Quarter::FIRST],
        ];
    }
}
