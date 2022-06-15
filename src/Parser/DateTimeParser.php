<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

/**
 * Interface that all date-time parsers must implement.
 */
interface DateTimeParser
{
    /**
     * @param string $text The text to parse.
     *
     * @return DateTimeParseResult The parse result.
     *
     * @throws DateTimeParseException If the given text could not be parsed.
     */
    public function parse(string $text): DateTimeParseResult;
}
