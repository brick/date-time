<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The second part of the time-zone offset.
 */
final class TimeZoneOffsetSecond
{
    /**
     * The field name.
     */
    public const NAME = 'time-zone-offset-second';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = SecondOfMinute::PATTERN;

    /**
     * @param int $offsetSecond The offset-second to check.
     *
     * @throws DateTimeException If the offset-second is not valid.
     */
    public static function check(int $offsetSecond): void
    {
        if ($offsetSecond < -59 || $offsetSecond > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $offsetSecond, -59, 59);
        }
    }
}
