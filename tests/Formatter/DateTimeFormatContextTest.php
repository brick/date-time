<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Formatter;

use Brick\DateTime\Field;
use Brick\DateTime\Formatter\DateTimeFormatContext;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\TimeZoneRegion;
use Brick\DateTime\ZonedDateTime;
use PHPUnit\Framework\TestCase;

class DateTimeFormatContextTest extends TestCase
{
    public function testOfLocalDate(): void
    {
        $localDate = LocalDate::of(2022, 6, 8);
        $context = DateTimeFormatContext::ofLocalDate($localDate);

        self::assertSame('8', $context->getField(Field\DayOfMonth::NAME));
        self::assertSame('3', $context->getField(Field\DayOfWeek::NAME));
        self::assertSame('159', $context->getField(Field\DayOfYear::NAME));
        self::assertSame('23', $context->getField(Field\WeekOfYear::NAME));
        self::assertSame('6', $context->getField(Field\MonthOfYear::NAME));
        self::assertSame('2022', $context->getField(Field\Year::NAME));

        self::assertFalse($context->hasField(Field\HourOfDay::NAME));
        self::assertFalse($context->hasField(Field\TimeZoneRegion::NAME));
    }

    public function testOfLocalTime(): void
    {
        $localTime = LocalTime::of(13, 37, 42, 999999999);
        $context = DateTimeFormatContext::ofLocalTime($localTime);

        self::assertSame('13', $context->getField(Field\HourOfDay::NAME));
        self::assertSame('37', $context->getField(Field\MinuteOfHour::NAME));
        self::assertSame('42', $context->getField(Field\SecondOfMinute::NAME));
        self::assertSame('999999999', $context->getField(Field\NanoOfSecond::NAME));
        self::assertSame('999999999', $context->getField(Field\FractionOfSecond::NAME));

        self::assertFalse($context->hasField(Field\DayOfMonth::NAME));
        self::assertFalse($context->hasField(Field\TimeZoneRegion::NAME));
    }

    public function testOfLocalDateTime(): void
    {
        $localDateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        $context = DateTimeFormatContext::ofLocalDateTime($localDateTime);

        self::assertSame('8', $context->getField(Field\DayOfMonth::NAME));
        self::assertSame('3', $context->getField(Field\DayOfWeek::NAME));
        self::assertSame('159', $context->getField(Field\DayOfYear::NAME));
        self::assertSame('23', $context->getField(Field\WeekOfYear::NAME));
        self::assertSame('6', $context->getField(Field\MonthOfYear::NAME));
        self::assertSame('2022', $context->getField(Field\Year::NAME));

        self::assertSame('13', $context->getField(Field\HourOfDay::NAME));
        self::assertSame('37', $context->getField(Field\MinuteOfHour::NAME));
        self::assertSame('42', $context->getField(Field\SecondOfMinute::NAME));
        self::assertSame('999999999', $context->getField(Field\NanoOfSecond::NAME));
        self::assertSame('999999999', $context->getField(Field\FractionOfSecond::NAME));

        self::assertFalse($context->hasField(Field\TimeZoneRegion::NAME));
    }

    public function testOfZonedDateTime(): void
    {
        $localDateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        $zonedDateTime = ZonedDateTime::of($localDateTime, TimeZoneRegion::of('Europe/Prague'));
        $context = DateTimeFormatContext::ofZonedDateTime($zonedDateTime);

        self::assertSame('8', $context->getField(Field\DayOfMonth::NAME));
        self::assertSame('3', $context->getField(Field\DayOfWeek::NAME));
        self::assertSame('159', $context->getField(Field\DayOfYear::NAME));
        self::assertSame('23', $context->getField(Field\WeekOfYear::NAME));
        self::assertSame('6', $context->getField(Field\MonthOfYear::NAME));
        self::assertSame('2022', $context->getField(Field\Year::NAME));

        self::assertSame('13', $context->getField(Field\HourOfDay::NAME));
        self::assertSame('37', $context->getField(Field\MinuteOfHour::NAME));
        self::assertSame('42', $context->getField(Field\SecondOfMinute::NAME));
        self::assertSame('999999999', $context->getField(Field\NanoOfSecond::NAME));
        self::assertSame('999999999', $context->getField(Field\FractionOfSecond::NAME));

        self::assertSame('2', $context->getField(Field\TimeZoneOffsetHour::NAME));
        self::assertSame('0', $context->getField(Field\TimeZoneOffsetMinute::NAME));
        self::assertSame('+', $context->getField(Field\TimeZoneOffsetSign::NAME));
        self::assertSame('7200', $context->getField(Field\TimeZoneOffsetTotalSeconds::NAME));
        self::assertSame('Europe/Prague', $context->getField(Field\TimeZoneRegion::NAME));
    }
}
