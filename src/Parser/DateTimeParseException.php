<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

use Brick\DateTime\DateTimeException;

/**
 * Exception thrown when a parse error occurs.
 */
class DateTimeParseException extends DateTimeException
{
    public static function invalidDuration(string $textToParse): self
    {
        return new self('Text cannot be parsed to a Duration: ' . $textToParse);
    }

    public static function invalidPeriod(string $textToParse): self
    {
        return new self('Text cannot be parsed to a Period: ' . $textToParse);
    }
}
