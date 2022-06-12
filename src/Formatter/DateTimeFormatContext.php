<?php

declare(strict_types=1);

namespace Brick\DateTime\Formatter;

use Brick\DateTime\Field;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\ZonedDateTime;

use function abs;
use function array_shift;
use function floor;
use function sprintf;

/**
 * An intermediate representation of a formatted date-time value.
 */
final class DateTimeFormatContext
{
    /** @var LocalDate|LocalDateTime|LocalTime|ZonedDateTime */
    private $value;

    /** @var array<string, list<string>> */
    private array $fields = [];

    /**
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    public static function ofLocalDate(LocalDate $localDate): self
    {
        $self = new self($localDate);
        $self->addField(Field\DayOfMonth::NAME, (string) $localDate->getDay());
        $self->addField(Field\DayOfWeek::NAME, (string) $localDate->getDayOfWeek()->getValue());
        $self->addField(Field\DayOfYear::NAME, (string) $localDate->getDayOfYear());
        $self->addField(Field\WeekOfYear::NAME, (string) $localDate->getYearWeek()->getWeek());
        $self->addField(Field\MonthOfYear::NAME, (string) $localDate->getMonth());
        $self->addField(Field\Year::NAME, (string) $localDate->getYear());

        return $self;
    }

    public static function ofLocalTime(LocalTime $localTime): self
    {
        $self = new self($localTime);
        $self->addField(Field\HourOfDay::NAME, (string) $localTime->getHour());
        $self->addField(Field\MinuteOfHour::NAME, (string) $localTime->getMinute());
        $self->addField(Field\SecondOfMinute::NAME, (string) $localTime->getSecond());
        $self->addField(Field\NanoOfSecond::NAME, (string) $localTime->getNano());
        $self->addField(Field\FractionOfSecond::NAME, (string) $localTime->getNano());

        return $self;
    }

    public static function ofLocalDateTime(LocalDateTime $localDateTime): self
    {
        $self = new self($localDateTime);
        $self->addField(Field\DayOfMonth::NAME, (string) $localDateTime->getDate()->getDay());
        $self->addField(Field\DayOfWeek::NAME, (string) $localDateTime->getDate()->getDayOfWeek()->getValue());
        $self->addField(Field\DayOfYear::NAME, (string) $localDateTime->getDate()->getDayOfYear());
        $self->addField(Field\WeekOfYear::NAME, (string) $localDateTime->getDate()->getYearWeek()->getWeek());
        $self->addField(Field\MonthOfYear::NAME, (string) $localDateTime->getDate()->getMonth());
        $self->addField(Field\Year::NAME, (string) $localDateTime->getDate()->getYear());
        $self->addField(Field\HourOfDay::NAME, (string) $localDateTime->getTime()->getHour());
        $self->addField(Field\MinuteOfHour::NAME, (string) $localDateTime->getTime()->getMinute());
        $self->addField(Field\SecondOfMinute::NAME, (string) $localDateTime->getTime()->getSecond());
        $self->addField(Field\NanoOfSecond::NAME, (string) $localDateTime->getTime()->getNano());
        $self->addField(Field\FractionOfSecond::NAME, (string) $localDateTime->getTime()->getNano());

        return $self;
    }

    public static function ofZonedDateTime(ZonedDateTime $zonedDateTime): self
    {
        $self = new self($zonedDateTime);
        $self->addField(Field\DayOfMonth::NAME, (string) $zonedDateTime->getDate()->getDay());
        $self->addField(Field\DayOfWeek::NAME, (string) $zonedDateTime->getDate()->getDayOfWeek()->getValue());
        $self->addField(Field\DayOfYear::NAME, (string) $zonedDateTime->getDate()->getDayOfYear());
        $self->addField(Field\WeekOfYear::NAME, (string) $zonedDateTime->getDate()->getYearWeek()->getWeek());
        $self->addField(Field\MonthOfYear::NAME, (string) $zonedDateTime->getDate()->getMonth());
        $self->addField(Field\Year::NAME, (string) $zonedDateTime->getDate()->getYear());
        $self->addField(Field\HourOfDay::NAME, (string) $zonedDateTime->getTime()->getHour());
        $self->addField(Field\MinuteOfHour::NAME, (string) $zonedDateTime->getTime()->getMinute());
        $self->addField(Field\SecondOfMinute::NAME, (string) $zonedDateTime->getTime()->getSecond());
        $self->addField(Field\NanoOfSecond::NAME, (string) $zonedDateTime->getTime()->getNano());
        $self->addField(Field\FractionOfSecond::NAME, (string) $zonedDateTime->getTime()->getNano());
        $self->addField(Field\TimeZoneOffsetHour::NAME, sprintf('%d', floor(abs($zonedDateTime->getTimeZoneOffset()->getTotalSeconds()) / LocalTime::SECONDS_PER_HOUR)));
        $self->addField(Field\TimeZoneOffsetMinute::NAME, (string) ((abs($zonedDateTime->getTimeZoneOffset()->getTotalSeconds()) % LocalTime::SECONDS_PER_HOUR) / LocalTime::SECONDS_PER_MINUTE));
        $self->addField(Field\TimeZoneOffsetSign::NAME, $zonedDateTime->getTimeZoneOffset()->getTotalSeconds() === 0 ? 'Z' : ($zonedDateTime->getTimeZoneOffset()->getTotalSeconds() > 0 ? '+' : '-'));
        $self->addField(Field\TimeZoneOffsetTotalSeconds::NAME, (string) $zonedDateTime->getTimeZoneOffset()->getTotalSeconds());
        $self->addField(Field\TimeZoneRegion::NAME, $zonedDateTime->getTimeZone()->getId());

        return $self;
    }

    public function addField(string $name, string $value): void
    {
        $this->fields[$name][] = $value;
    }

    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]) && $this->fields[$name];
    }

    public function getField(string $name): string
    {
        $value = $this->getOptionalField($name);

        if ($value === '') {
            throw new DateTimeFormatException(sprintf('Field %s is not present in the formatting context.', $name));
        }

        return $value;
    }

    public function getOptionalField(string $name): string
    {
        if (isset($this->fields[$name])) {
            if ($this->fields[$name]) {
                return array_shift($this->fields[$name]);
            }
        }

        return '';
    }

    /**
     * @return LocalDate|LocalDateTime|LocalTime|ZonedDateTime
     */
    public function getValue()
    {
        return $this->value;
    }
}
