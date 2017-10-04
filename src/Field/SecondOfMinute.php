<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

use Brick\DateTime\DateTimeException;

/**
 * The second-of-minute field.
 */
class SecondOfMinute
{
    /**
     * The field name.
     */
    const NAME = 'second-of-minute';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    const PATTERN = '[0-9]{2}';

    /**
     * @param int $secondOfMinute The second-of-minute to check.
     *
     * @return void
     *
     * @throws DateTimeException If the second-of-minute is not valid.
     */
    public static function check(int $secondOfMinute) : void
    {
        if ($secondOfMinute < 0 || $secondOfMinute > 59) {
            throw DateTimeException::fieldNotInRange(self::NAME, $secondOfMinute, 0, 59);
        }
    }
}
