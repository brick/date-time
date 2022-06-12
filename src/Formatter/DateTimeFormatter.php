<?php

declare(strict_types=1);

namespace Brick\DateTime\Formatter;

/**
 * Interface that all date-time formatters must implement.
 */
interface DateTimeFormatter
{
    /**
     * @param DateTimeFormatContext $context Formatting context.
     *
     * @return string The formatted value.
     *
     * @throws DateTimeFormatException If the given context could not be formatted.
     */
    public function format(DateTimeFormatContext $context): string;
}
