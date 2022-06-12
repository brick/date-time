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

        $this->assertSame('8', $context->getField(Field\DayOfMonth::NAME));
        $this->assertSame('3', $context->getField(Field\DayOfWeek::NAME));
        $this->assertSame('159', $context->getField(Field\DayOfYear::NAME));
        $this->assertSame('23', $context->getField(Field\WeekOfYear::NAME));
        $this->assertSame('6', $context->getField(Field\MonthOfYear::NAME));
        $this->assertSame('2022', $context->getField(Field\Year::NAME));

        $this->assertFalse($context->hasField(Field\HourOfDay::NAME));
        $this->assertFalse($context->hasField(Field\TimeZoneRegion::NAME));
    }

    public function testOfLocalTime(): void
    {
        $localTime = LocalTime::of(13, 37, 42, 999999999);
        $context = DateTimeFormatContext::ofLocalTime($localTime);

        $this->assertSame('13', $context->getField(Field\HourOfDay::NAME));
        $this->assertSame('37', $context->getField(Field\MinuteOfHour::NAME));
        $this->assertSame('42', $context->getField(Field\SecondOfMinute::NAME));
        $this->assertSame('999999999', $context->getField(Field\NanoOfSecond::NAME));
        $this->assertSame('999999999', $context->getField(Field\FractionOfSecond::NAME));

        $this->assertFalse($context->hasField(Field\DayOfMonth::NAME));
        $this->assertFalse($context->hasField(Field\TimeZoneRegion::NAME));
    }

    public function testOfLocalDateTime(): void
    {
        $localDateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        $context = DateTimeFormatContext::ofLocalDateTime($localDateTime);

        $this->assertSame('8', $context->getField(Field\DayOfMonth::NAME));
        $this->assertSame('3', $context->getField(Field\DayOfWeek::NAME));
        $this->assertSame('159', $context->getField(Field\DayOfYear::NAME));
        $this->assertSame('23', $context->getField(Field\WeekOfYear::NAME));
        $this->assertSame('6', $context->getField(Field\MonthOfYear::NAME));
        $this->assertSame('2022', $context->getField(Field\Year::NAME));

        $this->assertSame('13', $context->getField(Field\HourOfDay::NAME));
        $this->assertSame('37', $context->getField(Field\MinuteOfHour::NAME));
        $this->assertSame('42', $context->getField(Field\SecondOfMinute::NAME));
        $this->assertSame('999999999', $context->getField(Field\NanoOfSecond::NAME));
        $this->assertSame('999999999', $context->getField(Field\FractionOfSecond::NAME));

        $this->assertFalse($context->hasField(Field\TimeZoneRegion::NAME));
    }

    public function testOfZonedDateTime(): void
    {
        $localDateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        $zonedDateTime = ZonedDateTime::of($localDateTime, TimeZoneRegion::of('Europe/Prague'));
        $context = DateTimeFormatContext::ofZonedDateTime($zonedDateTime);

        $this->assertSame('8', $context->getField(Field\DayOfMonth::NAME));
        $this->assertSame('3', $context->getField(Field\DayOfWeek::NAME));
        $this->assertSame('159', $context->getField(Field\DayOfYear::NAME));
        $this->assertSame('23', $context->getField(Field\WeekOfYear::NAME));
        $this->assertSame('6', $context->getField(Field\MonthOfYear::NAME));
        $this->assertSame('2022', $context->getField(Field\Year::NAME));

        $this->assertSame('13', $context->getField(Field\HourOfDay::NAME));
        $this->assertSame('37', $context->getField(Field\MinuteOfHour::NAME));
        $this->assertSame('42', $context->getField(Field\SecondOfMinute::NAME));
        $this->assertSame('999999999', $context->getField(Field\NanoOfSecond::NAME));
        $this->assertSame('999999999', $context->getField(Field\FractionOfSecond::NAME));

        $this->assertSame('2', $context->getField(Field\TimeZoneOffsetHour::NAME));
        $this->assertSame('0', $context->getField(Field\TimeZoneOffsetMinute::NAME));
        $this->assertSame('+', $context->getField(Field\TimeZoneOffsetSign::NAME));
        $this->assertSame('7200', $context->getField(Field\TimeZoneOffsetTotalSeconds::NAME));
        $this->assertSame('Europe/Prague', $context->getField(Field\TimeZoneRegion::NAME));
    }
}
