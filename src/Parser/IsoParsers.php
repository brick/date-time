<?php

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
     *
     * @return PatternParser
     */
    public static function localDate()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
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
     *
     * @return PatternParser
     */
    public static function localTime()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
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
     *
     * @return PatternParser
     */
    public static function localDateTime()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->append(self::localDate())
            ->appendLiteral('T')
            ->append(self::localTime())
            ->toParser();
    }

    /**
     * Returns a parser for a range of local dates such as `2014-01-05/2015-03-15`.
     *
     * @return PatternParser
     */
    public static function localDateRange()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->append(self::localDate())
            ->appendLiteral('/')
            ->append(self::localDate())
            ->toParser();
    }

    /**
     * Returns a parser for a year-month such as `2014-12`.
     *
     * @return PatternParser
     */
    public static function yearMonth()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->appendCapturePattern(Year::PATTERN, Year::NAME)
            ->appendLiteral('-')
            ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for a month-day such as `12-31`.
     *
     * @return PatternParser
     */
    public static function monthDay()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->appendLiteral('--')
            ->appendCapturePattern(MonthOfYear::PATTERN, MonthOfYear::NAME)
            ->appendLiteral('-')
            ->appendCapturePattern(DayOfMonth::PATTERN, DayOfMonth::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for a time-zone offset such as `Z` or `+01:00`.
     *
     * @return PatternParser
     */
    public static function timeZoneOffset()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
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
     *
     * @return PatternParser
     */
    public static function timeZoneRegion()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->appendCapturePattern(TimeZoneRegion::PATTERN, TimeZoneRegion::NAME)
            ->toParser();
    }

    /**
     * Returns a parser for an offset date-time such as `2004-01-31T12:45:56+01:00`.
     *
     * @return PatternParser
     */
    public static function offsetDateTime()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->append(self::localDateTime())
            ->append(self::timeZoneOffset())
            ->toParser();
    }

    /**
     * Returns a parser for a month-day such as `12-31`.
     *
     * @return PatternParser
     */
    public static function zonedDateTime()
    {
        static $parser;

        if ($parser) {
            return $parser;
        }

        return $parser = (new PatternParserBuilder())
            ->append(self::offsetDateTime())
            ->startOptional()
            ->appendLiteral('[')
            ->append(self::timeZoneRegion())
            ->appendLiteral(']')
            ->endOptional()
            ->toParser();
    }
}
