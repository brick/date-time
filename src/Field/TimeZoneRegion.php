<?php

namespace Brick\DateTime\Field;

/**
 * The time-zone region, such as 'Europe/London'.
 */
class TimeZoneRegion
{
    /**
     * The field name.
     */
    const NAME = 'time-zone-region';

    /**
     * The regular expression pattern matching the region name.
     */
    const PATTERN = '[A-Za-z0-9/_\-]+';
}
