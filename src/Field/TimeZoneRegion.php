<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

/**
 * The time-zone region, such as 'Europe/London'.
 */
final class TimeZoneRegion
{
    /**
     * The field name.
     */
    public const NAME = 'time-zone-region';

    /**
     * The regular expression pattern matching the region name.
     */
    public const PATTERN = '[A-Za-z0-9\/_\-]+';
}
