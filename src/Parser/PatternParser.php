<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

/**
 * Matches a regular expression pattern to a set of date-time fields.
 *
 * @psalm-immutable
 */
final class PatternParser implements DateTimeParser
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string[]
     */
    private $fields;

    /**
     * @param string   $pattern The regular expression pattern.
     * @param string[] $fields  The fields constants to match.
     */
    public function __construct(string $pattern, array $fields)
    {
        $this->pattern = $pattern;
        $this->fields  = $fields;
    }

    public function getPattern() : string
    {
        return $this->pattern;
    }

    /**
     * @return string[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    public function parse(string $text) : DateTimeParseResult
    {
        $pattern = '/^' . $this->pattern . '$/';

        if (\preg_match($pattern, $text, $matches) !== 1) {
            throw new DateTimeParseException(\sprintf('Failed to parse "%s".', $text));
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
