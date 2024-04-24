<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

use function preg_match;
use function sprintf;

/**
 * Matches a regular expression pattern to a set of date-time fields.
 */
final class PatternParser implements DateTimeParser
{
    /**
     * @param string   $pattern The regular expression pattern.
     * @param string[] $fields  The fields constants to match.
     */
    public function __construct(
        private readonly string $pattern,
        private readonly array $fields,
    ) {
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function parse(string $text): DateTimeParseResult
    {
        $pattern = '/^' . $this->pattern . '$/';

        if (preg_match($pattern, $text, $matches) !== 1) {
            throw new DateTimeParseException(sprintf('Failed to parse "%s".', $text));
        }

        $result = new DateTimeParseResult();

        $index = 1;

        foreach ($this->fields as $field) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $result->addField($field, $matches[$index]);
            }

            $index++;
        }

        return $result;
    }
}
