<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

use Brick\DateTime\Field\DayOfMonth;
use Brick\DateTime\Field\FractionOfSecond;
use Brick\DateTime\Field\HourOfDay;
use Brick\DateTime\Field\MinuteOfHour;
use Brick\DateTime\Field\MonthOfYear;
use Brick\DateTime\Field\SecondOfMinute;
use Brick\DateTime\Field\TimeZoneOffsetHour;
use Brick\DateTime\Field\TimeZoneOffsetMinute;
use Brick\DateTime\Field\TimeZoneOffsetSecond;
use Brick\DateTime\Field\TimeZoneOffsetSign;
use Brick\DateTime\Field\TimeZoneRegion;
use Brick\DateTime\Field\WeekOfYear;
use Brick\DateTime\Field\Year;

/**
 * Provides ISO 8601 parser implementations.
 */
final class IsoParsers
{
    /**
     * Private constructor. This class cannot be instantiated.
     */
    private function __construct()
    {
    }

    /**
     * Returns a parser for an ISO local date such as `2014-12-31`.
     */
    public static function localDate(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendCapturePattern(Year::PATTERN, Year::NAME)
            ->appendLiteral('-')
            ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
            ->appendLiteral('-')
            ->appendCapturePattern(DayOfMonth::PATTERN, DayOfMonth::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for an ISO local time such as `10:15:30.123`.
     *
     * The second and fraction of second are optional.
     */
    public static function localTime(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendCapturePattern(HourOfDay::PATTERN, HourOfDay::NAME)
            ->appendLiteral(':')
            ->appendCapturePattern(MinuteOfHour::PATTERN, MinuteOfHour::NAME)
            ->startOptional()
            ->appendLiteral(':')
            ->appendCapturePattern(SecondOfMinute::PATTERN, SecondOfMinute::NAME)
            ->startOptional()
            ->appendLiteral('.')
            ->appendCapturePattern(FractionOfSecond::PATTERN, FractionOfSecond::NAME)
            ->endOptional()
            ->endOptional()
            ->toParser();
    }

    /**
     * Returns a parser for an ISO local date-time such as `2014-12-31T10:15`.
     *
     * The second and fraction of second are optional.
     */
    public static function localDateTime(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->append(self::localDate())
            ->appendLiteral('T')
            ->append(self::localTime())
            ->toParser();
    }

    /**
     * Returns a parser for a range of local dates such as `2014-01-05/2015-03-15`.
     */
    public static function localDateRange(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->append(self::localDate())
            ->appendLiteral('/')

            ->startGroup()
                ->startGroup()
                    ->append(self::localDate())
                ->endGroup()
            ->appendOr()
                ->startGroup()
                    ->startOptional()
                        ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
                        ->appendLiteral('-')
                    ->endOptional()
                    ->appendCapturePattern(DayOfMonth::PATTERN, DayOfMonth::NAME)
                ->endGroup()
            ->endGroup()

            ->toParser();
    }

    /**
     * Returns a parser for a range of year-months such as `2014-01/2015-03`.
     *
     * Note that ISO 8601 does not seem to define a format for year-month ranges, but we're using the same format as
     * date ranges here.
     */
    public static function yearMonthRange(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->append(self::yearMonth())
            ->appendLiteral('/')

            ->startGroup()
                ->startGroup()
                    ->append(self::yearMonth())
                ->endGroup()
            ->appendOr()
                ->startGroup()
                    ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
                ->endGroup()
            ->endGroup()

            ->toParser();
    }

    /**
     * Returns a parser for a year such as `2014`.
     */
    public static function year(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendCapturePattern(Year::PATTERN, Year::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for a year-month such as `2014-12`.
     */
    public static function yearMonth(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendCapturePattern(Year::PATTERN, Year::NAME)
            ->appendLiteral('-')
            ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for a year-week such as `2014-W15`.
     */
    public static function yearWeek(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendCapturePattern(Year::PATTERN, Year::NAME)
            ->appendLiteral('-W')
            ->appendCapturePattern(WeekOfYear::PATTERN, WeekOfYear::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for a month-day such as `12-31`.
     */
    public static function monthDay(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendLiteral('--')
            ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
            ->appendLiteral('-')
            ->appendCapturePattern(DayOfMonth::PATTERN, DayOfMonth::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for a time-zone offset such as `Z` or `+01:00`.
     */
    public static function timeZoneOffset(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->startGroup()
            ->appendCapturePattern('[Zz]', TimeZoneOffsetSign::NAME)
            ->appendOr()
            ->startGroup()
            ->appendCapturePattern('[\-\+]', TimeZoneOffsetSign::NAME)
            ->appendCapturePattern(TimeZoneOffsetHour::PATTERN, TimeZoneOffsetHour::NAME)
            ->appendLiteral(':')
            ->appendCapturePattern(TimeZoneOffsetMinute::PATTERN, TimeZoneOffsetMinute::NAME)
            ->startOptional()
            ->appendLiteral(':')
            ->appendCapturePattern(TimeZoneOffsetSecond::PATTERN, TimeZoneOffsetSecond::NAME)
            ->endOptional()
            ->endGroup()
            ->endGroup()
            ->toParser();
    }

    /**
     * Returns a parser for a time-zone region such as `Europe/London`.
     */
    public static function timeZoneRegion(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->appendCapturePattern(TimeZoneRegion::PATTERN, TimeZoneRegion::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for an offset date-time such as `2004-01-31T12:45:56+01:00`.
     */
    public static function offsetDateTime(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->append(self::localDateTime())
            ->append(self::timeZoneOffset())
            ->toParser();
    }

    /**
     * Returns a parser for a date-time with offset and zone such as `2011-12-03T10:15:30+01:00[Europe/Paris].
     */
    public static function zonedDateTime(): PatternParser
    {
        /** @var PatternParser|null $parser */
        static $parser = null;

        return $parser ??= (new PatternParserBuilder())
            ->append(self::offsetDateTime())
            ->startOptional()
            ->appendLiteral('[')
            ->append(self::timeZoneRegion())
            ->appendLiteral(']')
            ->endOptional()
            ->toParser();
    }
}
