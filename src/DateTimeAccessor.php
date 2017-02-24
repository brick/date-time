<?php

declare(strict_types=1);

namespace Brick\DateTime;

/**
 * General low-level access to a date/time object.
 */
interface DateTimeAccessor
{
    /**
     * Returns the value of the given date-time field.
     *
     * @param string $field The date-time field.
     *
     * @return int|null
     */
    public function getField(string $field);
}
