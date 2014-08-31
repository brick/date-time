<?php

namespace Brick\DateTime\Parser;

use Brick\DateTime\DateTimeException;

/**
 * Exception thrown when a parse error occurs.
 */
class DateTimeParseException extends DateTimeException
{
    /**
     * @param string $textToParse
     *
     * @return DateTimeParseException
     */
    public static function invalidDuration($textToParse)
    {
        return new self('Text cannot be parsed to a Duration: ' . $textToParse);
    }
    /**
     * @param string $textToParse
     *
     * @return DateTimeParseException
     */
    public static function invalidPeriod($textToParse)
    {
        return new self('Text cannot be parsed to a Period: ' . $textToParse);
    }
}
