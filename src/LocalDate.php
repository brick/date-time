<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Parser\IsoParsers;
use Brick\DateTime\Utility\Math;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;
use Stringable;

use function intdiv;
use function is_int;
use function min;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * A date without a time-zone in the ISO-8601 calendar system, such as `2007-12-03`.
 *
 * This class is immutable.
 */
final class LocalDate implements JsonSerializable, Stringable
{
    /**
     * The minimum supported year for instances of `LocalDate`, -999,999.
     */
    public const MIN_YEAR = -999_999;

    /**
     * The maximum supported year for instances of `LocalDate`, 999,999.
     */
    public const MAX_YEAR = 999_999;

    /**
     * The number of days from year zero to year 1970.
     */
    public const DAYS_0000_TO_1970 = 719528;

    /**
     * The number of days in a 400 year cycle.
     */
    public const DAYS_PER_CYCLE = 146097;

    /**
     * Private constructor. Use of() to obtain an instance.
     *
     * @param int        $year  The year, validated from MIN_YEAR to MAX_YEAR.
     * @param int<1, 12> $month The month-of-year.
     * @param int<1, 31> $day   The day-of-month, validated from 1 to 31, valid for the year-month.
     */
    private function __construct(
        private readonly int $year,
        private readonly int $month,
        private readonly int $day,
    ) {
    }

    /**
     * Obtains an instance of `LocalDate` from a year, month and day.
     *
     * @param int       $year  The year, from MIN_YEAR to MAX_YEAR.
     * @param int|Month $month The month-of-year, from 1 (January) to 12 (December).
     * @param int       $day   The day-of-month, from 1 to 31.
     *
     * @throws DateTimeException If the date is not valid.
     */
    public static function of(int $year, int|Month $month, int $day): LocalDate
    {
        Field\Year::check($year);

        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        } else {
            $month = $month->value;
        }

        Field\DayOfMonth::check($day, $month, $year);

        return new LocalDate($year, $month, $day);
    }

    /**
     * Obtains an instance of `LocalDate` from a year and day-of-year.
     *
     * @param int $year      The year, from MIN_YEAR to MAX_YEAR.
     * @param int $dayOfYear The day-of-year, from 1 to 366.
     *
     * @throws DateTimeException If either value is not valid.
     */
    public static function ofYearDay(int $year, int $dayOfYear): LocalDate
    {
        Field\Year::check($year);
        Field\DayOfYear::check($dayOfYear, $year);

        $isLeap = Field\Year::isLeap($year);

        $monthOfYear = Month::from(intdiv($dayOfYear - 1, 31) + 1);
        $monthEnd = $monthOfYear->getFirstDayOfYear($isLeap) + $monthOfYear->getLength($isLeap) - 1;

        if ($dayOfYear > $monthEnd) {
            $monthOfYear = $monthOfYear->plus(1);
        }

        $dayOfMonth = $dayOfYear - $monthOfYear->getFirstDayOfYear($isLeap) + 1;

        return LocalDate::of($year, $monthOfYear, $dayOfMonth);
    }

    /**
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If required fields are missing from the result.
     */
    public static function from(DateTimeParseResult $result): LocalDate
    {
        $year = (int) $result->getField(Field\Year::NAME);
        $month = (int) $result->getField(Field\MonthOfYear::NAME);
        $day = (int) $result->getField(Field\DayOfMonth::NAME);

        return LocalDate::of($year, $month, $day);
    }

    /**
     * Obtains an instance of `LocalDate` from a text string.
     *
     * @param string              $text   The text to parse, such as `2007-12-03`.
     * @param DateTimeParser|null $parser The parser to use, defaults to the ISO 8601 parser.
     *
     * @throws DateTimeException      If the date is not valid.
     * @throws DateTimeParseException If the text string does not follow the expected format.
     */
    public static function parse(string $text, ?DateTimeParser $parser = null): LocalDate
    {
        if ($parser === null) {
            $parser = IsoParsers::localDate();
        }

        return LocalDate::from($parser->parse($text));
    }

    /**
     * Creates a LocalDate from a native DateTime or DateTimeImmutable object.
     */
    public static function fromNativeDateTime(DateTimeInterface $dateTime): LocalDate
    {
        $year = (int) $dateTime->format('Y');

        /** @var int<1, 12> $month */
        $month = (int) $dateTime->format('n');

        /** @var int<1, 31> $day */
        $day = (int) $dateTime->format('j');

        return new LocalDate($year, $month, $day);
    }

    /**
     * Obtains an instance of `LocalDate` from the epoch day count.
     *
     * The Epoch Day count is a simple incrementing count of days
     * where day 0 is 1970-01-01. Negative numbers represent earlier days.
     *
     * @throws DateTimeException If the resulting date has a year out of range.
     */
    public static function ofEpochDay(int $epochDay): LocalDate
    {
        $zeroDay = $epochDay + self::DAYS_0000_TO_1970;
        // Find the march-based year.
        $zeroDay -= 60; // Adjust to 0000-03-01 so leap day is at end of four year cycle.
        $adjust = 0;
        if ($zeroDay < 0) {
            // Adjust negative years to positive for calculation.
            $adjustCycles = intdiv(($zeroDay + 1), self::DAYS_PER_CYCLE) - 1;
            $adjust = $adjustCycles * 400;
            $zeroDay += -$adjustCycles * self::DAYS_PER_CYCLE;
        }
        $yearEst = intdiv(400 * $zeroDay + 591, self::DAYS_PER_CYCLE);
        $doyEst = $zeroDay - (365 * $yearEst + intdiv($yearEst, 4) - intdiv($yearEst, 100) + intdiv($yearEst, 400));
        if ($doyEst < 0) {
            // Fix estimate.
            $yearEst--;
            $doyEst = $zeroDay - (365 * $yearEst + intdiv($yearEst, 4) - intdiv($yearEst, 100) + intdiv($yearEst, 400));
        }
        $yearEst += $adjust; // Reset any negative year.
        $marchDoy0 = $doyEst;

        // Convert march-based values back to January-based.
        $marchMonth0 = intdiv($marchDoy0 * 5 + 2, 153);

        /** @var int<1, 12> $month */
        $month = ($marchMonth0 + 2) % 12 + 1;

        /** @var int<1, 31> $dom */
        $dom = $marchDoy0 - intdiv($marchMonth0 * 306 + 5, 10) + 1;

        $yearEst += intdiv($marchMonth0, 10);

        // Check year now we are certain it is correct.
        Field\Year::check($yearEst);

        return new LocalDate($yearEst, $month, $dom);
    }

    /**
     * Returns the current date in the given time-zone, according to the given clock.
     *
     * If no clock is provided, the system clock is used.
     */
    public static function now(TimeZone $timeZone, ?Clock $clock = null): LocalDate
    {
        return ZonedDateTime::now($timeZone, $clock)->getDate();
    }

    /**
     * Returns the minimum supported LocalDate.
     *
     * This can be used by an application as a "far past" date.
     */
    public static function min(): LocalDate
    {
        /** @var LocalDate|null $min */
        static $min = null;

        return $min ??= LocalDate::of(self::MIN_YEAR, Month::JANUARY, 1);
    }

    /**
     * Returns the maximum supported LocalDate.
     *
     * This can be used by an application as a "far future" date.
     */
    public static function max(): LocalDate
    {
        /** @var LocalDate|null $max */
        static $max = null;

        return $max ??= LocalDate::of(self::MAX_YEAR, Month::DECEMBER, 31);
    }

    /**
     * Returns the smallest LocalDate among the given values.
     *
     * @param LocalDate ...$dates The LocalDate objects to compare.
     *
     * @return LocalDate The earliest LocalDate object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function minOf(LocalDate ...$dates): LocalDate
    {
        if ($dates === []) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $min = null;

        foreach ($dates as $date) {
            if ($min === null || $date->isBefore($min)) {
                $min = $date;
            }
        }

        return $min;
    }

    /**
     * Returns the highest LocalDate among the given values.
     *
     * @param LocalDate ...$dates The LocalDate objects to compare.
     *
     * @return LocalDate The latest LocalDate object.
     *
     * @throws DateTimeException If the array is empty.
     */
    public static function maxOf(LocalDate ...$dates): LocalDate
    {
        if ($dates === []) {
            throw new DateTimeException(__METHOD__ . ' does not accept less than 1 parameter.');
        }

        $max = null;

        foreach ($dates as $date) {
            if ($max === null || $date->isAfter($max)) {
                $max = $date;
            }
        }

        return $max;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * Returns the month-of-year as a Month enum.
     */
    public function getMonth(): Month
    {
        return Month::from($this->month);
    }

    /**
     * Returns the month-of-year value from 1 to 12.
     *
     * @return int<1, 12>
     */
    public function getMonthValue(): int
    {
        return $this->month;
    }

    /**
     * @return int<1, 31>
     */
    public function getDayOfMonth(): int
    {
        return $this->day;
    }

    public function getYearMonth(): YearMonth
    {
        return YearMonth::of($this->year, $this->month);
    }

    public function getDayOfWeek(): DayOfWeek
    {
        return DayOfWeek::from(Math::floorMod($this->toEpochDay() + 3, 7) + 1);
    }

    /**
     * Returns the day-of-year, from 1 to 365, or 366 in a leap year.
     *
     * @return int<1, 366>
     */
    public function getDayOfYear(): int
    {
        return Month::from($this->month)->getFirstDayOfYear($this->isLeapYear()) + $this->day - 1;
    }

    public function getYearWeek(): YearWeek
    {
        $year = $this->year;
        $week = intdiv($this->getDayOfYear() - $this->getDayOfWeek()->value + 10, 7);

        if ($week === 0) {
            $year--;
            $week = Field\WeekOfYear::getWeeksInYear($year);
        } elseif ($week === 53 && ! Field\WeekOfYear::is53WeekYear($this->year)) {
            $year++;
            $week = 1;
        }

        return YearWeek::of($year, $week);
    }

    /**
     * Returns a copy of this LocalDate with the year altered.
     *
     * If the day-of-month is invalid for the year, it will be changed to the last valid day of the month.
     *
     * @throws DateTimeException If the year is outside the valid range.
     */
    public function withYear(int $year): LocalDate
    {
        if ($year === $this->year) {
            return $this;
        }

        Field\Year::check($year);

        return $this->resolvePreviousValid($year, $this->month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the month-of-year altered.
     *
     * If the day-of-month is invalid for the month and year, it will be changed to the last valid day of the month.
     *
     * @throws DateTimeException If the month is invalid.
     */
    public function withMonth(int|Month $month): LocalDate
    {
        if (is_int($month)) {
            Field\MonthOfYear::check($month);
        } else {
            $month = $month->value;
        }

        if ($month === $this->month) {
            return $this;
        }

        return $this->resolvePreviousValid($this->year, $month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the day-of-month altered.
     *
     * If the resulting date is invalid, an exception is thrown.
     *
     * @throws DateTimeException If the day is invalid for the current year and month.
     */
    public function withDay(int $day): LocalDate
    {
        if ($day === $this->day) {
            return $this;
        }

        Field\DayOfMonth::check($day, $this->month, $this->year);

        return new LocalDate($this->year, $this->month, $day);
    }

    /**
     * Returns a copy of this LocalDate with the specified Period added.
     */
    public function plusPeriod(Period $period): LocalDate
    {
        return $this
            ->plusYears($period->getYears())
            ->plusMonths($period->getMonths())
            ->plusDays($period->getDays());
    }

    /**
     * Returns a copy of this LocalDate with the specified period in years added.
     *
     * If the day-of-month is invalid for the resulting year and month,
     * it will be changed to the last valid day of the month.
     *
     * @throws DateTimeException If the resulting year is out of range.
     */
    public function plusYears(int $years): LocalDate
    {
        if ($years === 0) {
            return $this;
        }

        return $this->withYear($this->year + $years);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in months added.
     *
     * If the day-of-month is invalid for the resulting year and month,
     * it will be changed to the last valid day of the month.
     */
    public function plusMonths(int $months): LocalDate
    {
        $month = $this->month + $months - 1;

        $yearDiff = Math::floorDiv($month, 12);

        /** @var int<1, 12> $month */
        $month = Math::floorMod($month, 12) + 1;

        $year = $this->year + $yearDiff;

        return $this->resolvePreviousValid($year, $month, $this->day);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weeks added.
     */
    public function plusWeeks(int $weeks): LocalDate
    {
        if ($weeks === 0) {
            return $this;
        }

        return $this->plusDays($weeks * 7);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in days added.
     */
    public function plusDays(int $days): LocalDate
    {
        if ($days === 0) {
            return $this;
        }

        // Performance optimization for a common use case.
        if ($days === 1) {
            if ($this->day >= 28 && $this->day === $this->getLengthOfMonth()) {
                return new self($this->year + intdiv($this->month, 12), ($this->month % 12) + 1, 1);
            }

            /** @psalm-suppress InvalidArgument $this->day + 1 is not int<2, 32> as Psalm thinks */
            return new self($this->year, $this->month, $this->day + 1);
        }

        return LocalDate::ofEpochDay($this->toEpochDay() + $days);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weekdays (Monday-Friday) added.
     * If the current date is on a weekend and the number of days is zero, the result is the current date.
     * This is a slightly different behaviour from PHP DateTime's "+ n weekdays", that would return the next monday.
     *
     * Note: this is currently a naive implementation that could be greatly improved.
     */
    public function plusWeekdays(int $days): LocalDate
    {
        $result = $this;

        if ($days < 0) {
            $subtractedDays = 0;

            while ($subtractedDays < -$days) {
                $result = $result->minusDays(1);
                if ($result->getDayOfWeek()->isWeekday()) {
                    $subtractedDays++;
                }
            }
        } else {
            $addedDays = 0;

            while ($addedDays < $days) {
                $result = $result->plusDays(1);
                if ($result->getDayOfWeek()->isWeekday()) {
                    $addedDays++;
                }
            }
        }

        return $result;
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weekdays (Monday-Friday) subtracted.
     * If the current date is on a weekend and the number of days is zero, the result is the current date.
     * This is a slightly different behaviour from PHP DateTime's "- n weekdays", that would return the next monday.
     */
    public function minusWeekdays(int $days): LocalDate
    {
        return $this->plusWeekdays(-$days);
    }

    /**
     * Returns a copy of this LocalDate with the specified Period subtracted.
     */
    public function minusPeriod(Period $period): LocalDate
    {
        return $this->plusPeriod($period->negated());
    }

    /**
     * Returns a copy of this LocalDate with the specified period in years subtracted.
     */
    public function minusYears(int $years): LocalDate
    {
        return $this->plusYears(-$years);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in months subtracted.
     */
    public function minusMonths(int $months): LocalDate
    {
        return $this->plusMonths(-$months);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in weeks subtracted.
     */
    public function minusWeeks(int $weeks): LocalDate
    {
        return $this->plusWeeks(-$weeks);
    }

    /**
     * Returns a copy of this LocalDate with the specified period in days subtracted.
     */
    public function minusDays(int $days): LocalDate
    {
        return $this->plusDays(-$days);
    }

    /**
     * Returns -1 if this date is before the given date, 1 if after, 0 if the dates are equal.
     *
     * @return int [-1,0,1] If this date is before, on, or after the given date.
     *
     * @psalm-return -1|0|1
     */
    public function compareTo(LocalDate $that): int
    {
        if ($this->year < $that->year) {
            return -1;
        }
        if ($this->year > $that->year) {
            return 1;
        }
        if ($this->month < $that->month) {
            return -1;
        }
        if ($this->month > $that->month) {
            return 1;
        }
        if ($this->day < $that->day) {
            return -1;
        }
        if ($this->day > $that->day) {
            return 1;
        }

        return 0;
    }

    public function isEqualTo(LocalDate $that): bool
    {
        return $this->compareTo($that) === 0;
    }

    public function isBefore(LocalDate $that): bool
    {
        return $this->compareTo($that) === -1;
    }

    public function isBeforeOrEqualTo(LocalDate $that): bool
    {
        return $this->compareTo($that) <= 0;
    }

    public function isAfter(LocalDate $that): bool
    {
        return $this->compareTo($that) === 1;
    }

    public function isAfterOrEqualTo(LocalDate $that): bool
    {
        return $this->compareTo($that) >= 0;
    }

    /**
     * Calculates the period between this date and another date.
     *
     * This calculates the period between the two dates in terms of years, months and days.
     * The result will be negative if the end is before the start.
     * The negative sign will be the same in each of year, month and day.
     *
     * The start date is included, but the end date is not.
     * The period is calculated by removing complete months, then calculating
     * the remaining number of days, adjusting to ensure that both have the same sign.
     * The number of months is then normalized into years and months based on a 12 month year.
     * A month is considered to be complete if the end day-of-month is greater
     * than or equal to the start day-of-month.
     *
     * For example, from `2010-01-15` to `2011-03-18` is 1 year, 2 months and 3 days.
     */
    public function until(LocalDate $endDateExclusive): Period
    {
        $totalMonths = $endDateExclusive->getProlepticMonth() - $this->getProlepticMonth();
        $days = $endDateExclusive->day - $this->day;

        if ($totalMonths > 0 && $days < 0) {
            $totalMonths--;
            $calcDate = $this->plusMonths($totalMonths);
            $days = $endDateExclusive->toEpochDay() - $calcDate->toEpochDay();
        } elseif ($totalMonths < 0 && $days > 0) {
            $totalMonths++;
            $days -= $endDateExclusive->getLengthOfMonth();
        }

        $years = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;

        return Period::of($years, $months, $days);
    }

    /**
     * Calculates the number of days between this date and another date.
     *
     * The start date is included, but the end date is not.
     * For example, `2018-02-15` to `2018-04-01` is 45 days.
     */
    public function daysUntil(LocalDate $endDateExclusive): int
    {
        return $endDateExclusive->toEpochDay() - $this->toEpochDay();
    }

    /**
     * Returns a local date-time formed from this date at the specified time.
     */
    public function atTime(LocalTime $time): LocalDateTime
    {
        return new LocalDateTime($this, $time);
    }

    /**
     * Checks if the year is a leap year, according to the ISO proleptic calendar system rules.
     */
    public function isLeapYear(): bool
    {
        return Field\Year::isLeap($this->year);
    }

    /**
     * Returns the length of the year represented by this date, in days.
     */
    public function getLengthOfYear(): int
    {
        return $this->isLeapYear() ? 366 : 365;
    }

    /**
     * Returns the length of the month represented by this date, in days.
     *
     * @return int<28, 31>
     */
    public function getLengthOfMonth(): int
    {
        return Field\MonthOfYear::getLength($this->month, $this->year);
    }

    /**
     * Returns the number of days since the UNIX epoch of 1st January 1970.
     */
    public function toEpochDay(): int
    {
        $y = $this->year;
        $m = $this->month;

        $total = 365 * $y;

        if ($y >= 0) {
            $total += intdiv($y + 3, 4) - intdiv($y + 99, 100) + intdiv($y + 399, 400);
        } else {
            $total -= intdiv($y, -4) - intdiv($y, -100) + intdiv($y, -400);
        }

        $total += intdiv(367 * $m - 362, 12);
        $total += $this->day - 1;

        if ($m > 2) {
            $total--;
            if (! $this->isLeapYear()) {
                $total--;
            }
        }

        return $total - self::DAYS_0000_TO_1970;
    }

    /**
     * Returns the date of the first occurrence of the specified day-of-week before the current date.
     */
    public function previousDayOfWeek(DayOfWeek $dayOfWeek): LocalDate
    {
        $daysToSubtract = $this->getDayOfWeek()->value - $dayOfWeek->value;
        if ($daysToSubtract <= 0) {
            $daysToSubtract += 7;
        }

        return $this->minusDays($daysToSubtract);
    }

    /**
     * Returns the date of the first occurrence of the specified day-of-week before the current date,
     * unless it is already on that day in which case the current date is returned.
     */
    public function previousOrSameDayOfWeek(DayOfWeek $dayOfWeek): LocalDate
    {
        $daysToSubtract = $this->getDayOfWeek()->value - $dayOfWeek->value;
        if ($daysToSubtract < 0) {
            $daysToSubtract += 7;
        }

        return $this->minusDays($daysToSubtract);
    }

    /**
     * Returns the date of the first occurrence of the specified day-of-week after the current date.
     */
    public function nextDayOfWeek(DayOfWeek $dayOfWeek): LocalDate
    {
        $daysToAdd = $dayOfWeek->value - $this->getDayOfWeek()->value;
        if ($daysToAdd <= 0) {
            $daysToAdd += 7;
        }

        return $this->plusDays($daysToAdd);
    }

    /**
     * Returns the date of the first occurrence of the specified day-of-week after the current date,
     * unless it is already on that day in which case the current date is returned.
     */
    public function nextOrSameDayOfWeek(DayOfWeek $dayOfWeek): LocalDate
    {
        $daysToAdd = $dayOfWeek->value - $this->getDayOfWeek()->value;
        if ($daysToAdd < 0) {
            $daysToAdd += 7;
        }

        return $this->plusDays($daysToAdd);
    }

    /**
     * Converts this LocalDate to a native DateTime object.
     *
     * The result is a DateTime with time 00:00 in the UTC time-zone.
     */
    public function toNativeDateTime(): DateTime
    {
        return $this->atTime(LocalTime::midnight())->toNativeDateTime();
    }

    /**
     * Converts this LocalDate to a native DateTimeImmutable object.
     *
     * The result is a DateTimeImmutable with time 00:00 in the UTC time-zone.
     */
    public function toNativeDateTimeImmutable(): DateTimeImmutable
    {
        return DateTimeImmutable::createFromMutable($this->toNativeDateTime());
    }

    /**
     * Serializes as a string using {@see LocalDate::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this date.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        // This code is optimized for high performance
        return ($this->year < 1000 && $this->year > -1000
            ? (
                $this->year < 0
                    ? '-' . str_pad((string) -$this->year, 4, '0', STR_PAD_LEFT)
                    : str_pad((string) $this->year, 4, '0', STR_PAD_LEFT)
            )
            : $this->year
        )
            . '-'
            . ($this->month < 10 ? '0' . $this->month : $this->month)
            . '-'
            . ($this->day < 10 ? '0' . $this->day : $this->day);
    }

    /**
     * {@see LocalDate::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }

    /**
     * Resolves the date, resolving days past the end of month.
     *
     * @param int        $year  The year to represent, validated from MIN_YEAR to MAX_YEAR.
     * @param int<1, 12> $month The month-of-year to represent.
     * @param int<1, 31> $day   The day-of-month to represent, validated from 1 to 31.
     */
    private function resolvePreviousValid(int $year, int $month, int $day): LocalDate
    {
        if ($day > 28) {
            $day = min($day, YearMonth::of($year, $month)->getLengthOfMonth());
        }

        return new LocalDate($year, $month, $day);
    }

    private function getProlepticMonth(): int
    {
        return $this->year * 12 + $this->month - 1;
    }
}
