<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

/**
 * Result of a date-time string parsing.
 */
class DateTimeParseResult
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function addField(string $name, string $value)
    {
        $this->fields[$name][] = $value;
    }

    /**
     * Returns whether this result has at least one value for the given field.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name) : bool
    {
        return isset($this->fields[$name]) && $this->fields[$name];
    }

    /**
     * Returns the first value parsed for the given field.
     *
     * @param string $name One of the field constants.
     *
     * @return string The value for this field.
     *
     * @throws DateTimeParseException If the field is not present in this set.
     */
    public function getField(string $name) : string
    {
        $value = $this->getOptionalField($name);

        if ($value === '') {
            throw new DateTimeParseException(\sprintf('Field %s is not present in the parsed result.', $name));
        }

        return $value;
    }

    /**
     * Returns the first value for the given field, or an empty string if not present.
     *
     * @param string $name
     *
     * @return string
     */
    public function getOptionalField(string $name) : string
    {
        if (isset($this->fields[$name])) {
            if ($this->fields[$name]) {
                return \array_shift($this->fields[$name]);
            }
        }

        return '';
    }
}
