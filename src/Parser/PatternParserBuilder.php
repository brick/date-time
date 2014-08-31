<?php

namespace Brick\DateTime\Parser;

/**
 * Builds a PatternParser with a fluent API.
 */
class PatternParserBuilder
{
    /**
     * @var string
     */
    private $pattern = '';

    /**
     * @var array
     */
    private $fields = [];

    /**
     * @var array
     */
    private $stack = [];

    /**
     * @param PatternParser $parser
     *
     * @return static
     */
    public function append(PatternParser $parser)
    {
        $this->pattern .= $parser->getPattern();
        $this->fields = array_merge($this->fields, $parser->getFields());

        return $this;
    }

    /**
     * @param string $literal
     *
     * @return static
     */
    public function appendLiteral($literal)
    {
        $this->pattern .= preg_quote($literal);

        return $this;
    }

    /**
     * @param string $pattern
     * @param string $field
     *
     * @return static
     */
    public function appendCapturePattern($pattern, $field)
    {
        $this->pattern .= '(' . $pattern . ')';
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return static
     */
    public function startOptional()
    {
        $this->pattern .= '(?:';
        $this->stack[] = 'O';

        return $this;
    }

    /**
     * @return static
     */
    public function endOptional()
    {
        if (array_pop($this->stack) !== 'O') {
            throw new \RuntimeException('Cannot call endOptional() without a call to startOptional() first.');
        }

        $this->pattern .= ')?';

        return $this;
    }

    /**
     * @return static
     */
    public function startGroup()
    {
        $this->pattern .= '(?:';
        $this->stack[] = 'G';

        return $this;
    }

    /**
     * @return static
     */
    public function endGroup()
    {
        if (array_pop($this->stack) !== 'G') {
            throw new \RuntimeException('Cannot call endGroup() without a call to startGroup() first.');
        }

        $this->pattern .= ')';

        return $this;
    }

    /**
     * @return static
     */
    public function appendOr()
    {
        $this->pattern .= '|';

        return $this;
    }

    /**
     * @return string
     */
    public function toParser()
    {
        if ($this->stack) {
            throw new \RuntimeException('Builder misses call to endOptional() or endGroup().');
        }

        return new PatternParser($this->pattern, $this->fields);
    }
}
