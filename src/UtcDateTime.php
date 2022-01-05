<?php

declare(strict_types=1);

namespace Brick\DateTime;

use Brick\DateTime\Parser\DateTimeParser;
use Brick\DateTime\Parser\DateTimeParseResult;

final class UtcDateTime extends ZonedDateTime
{
    public static function of(LocalDateTime $dateTime, TimeZone $timeZone = null): UtcDateTime
    {
        if ($timeZone === null) {
            $timeZone = TimeZone::utc();
        }
        if (!$timeZone->isEqualTo(TimeZone::utc())) {
            throw new \InvalidArgumentException('Create UtcDateTime with not UTC timezone is not supported');
        }
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @var UtcDateTime
         */
        return parent::of($dateTime, $timeZone);
    }

    public static function ofInstant(Instant $instant, TimeZone $timeZone = null): UtcDateTime
    {
        if ($timeZone === null) {
            $timeZone = TimeZone::utc();
        }
        if (!$timeZone->isEqualTo(TimeZone::utc())) {
            throw new \InvalidArgumentException('Create UtcDateTime with not UTC timezone is not supported');
        }

        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @var UtcDateTime
         */
        return parent::ofInstant($instant, $timeZone);
    }

    public static function now(TimeZone $timeZone = null, ?Clock $clock = null): UtcDateTime
    {
        if ($timeZone === null) {
            $timeZone = TimeZone::utc();
        }
        if (!$timeZone->isEqualTo(TimeZone::utc())) {
            throw new \InvalidArgumentException('Create UtcDateTime with not UTC timezone is not supported');
        }
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @var UtcDateTime
         */
        return parent::now($timeZone, $clock);
    }

    public static function from(DateTimeParseResult $result): UtcDateTime
    {
        $result = parent::from($result);
        if (!$result->getTimeZone()->isEqualTo(TimeZone::utc())) {
            $result = $result->withTimeZoneSameInstant(TimeZone::utc());
        }
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @var UtcDateTime
         */
        return $result;
    }

    public static function parse(string $text, ?DateTimeParser $parser = null): UtcDateTime
    {
        $result = parent::parse($text, $parser);
        if (!$result->getTimeZone()->isEqualTo(TimeZone::utc())) {
            $result = $result->withTimeZoneSameInstant(TimeZone::utc());
        }
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @var UtcDateTime
         */
        return $result;
    }

    public static function fromDateTime(\DateTimeInterface $dateTime): UtcDateTime
    {
        $result = parent::fromDateTime($dateTime);
        if (!$result->getTimeZone()->isEqualTo(TimeZone::utc())) {
            $result = $result->withTimeZoneSameInstant(TimeZone::utc());
        }
        /**
         * @noinspection PhpIncompatibleReturnTypeInspection
         * @var UtcDateTime
         */
        return $result;
    }
}