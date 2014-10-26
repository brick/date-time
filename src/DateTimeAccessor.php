<?php

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
     * @return integer|null
     *
     * @throws DateTimeException If the field is not supported by this accessor.
     */
    public function getField($field);
}
