<?php

namespace Brick\DateTime\Parser;

/**
 * Matches a regular expression pattern to a set of date-time fields.
 */
class PatternParser implements DateTimeParser
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param string $pattern The regular expression pattern.
     * @param array  $fields  The fields constants to match.
     */
    public function __construct($pattern, array $fields)
    {
        $this->pattern = $pattern;
        $this->fields  = $fields;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($text)
    {
        $pattern = '/^' . str_replace('/', '\/', $this->pattern) . '$/';

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
