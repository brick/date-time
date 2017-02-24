<?php

declare(strict_types=1);

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
     * @return self
     */
    public function append(PatternParser $parser) : self
    {
        $this->pattern .= $parser->getPattern();
        $this->fields = array_merge($this->fields, $parser->getFields());

        return $this;
    }

    /**
     * @param string $literal
     *
     * @return self
     */
    public function appendLiteral(string $literal) : self
    {
        $this->pattern .= preg_quote($literal);

        return $this;
    }

    /**
     * @param string $pattern
     * @param string $field
     *
     * @return self
     */
    public function appendCapturePattern(string $pattern, string $field) : self
    {
        $this->pattern .= '(' . $pattern . ')';
        $this->fields[] = $field;

        return $this;
    }

    /**
     * @return self
     */
    public function startOptional() : self
    {
        $this->pattern .= '(?:';
        $this->stack[] = 'O';

        return $this;
    }

    /**
     * @return self
     */
    public function endOptional() : self
    {
        if (array_pop($this->stack) !== 'O') {
            throw new \RuntimeException('Cannot call endOptional() without a call to startOptional() first.');
        }

        $this->pattern .= ')?';

        return $this;
    }

    /**
     * @return self
     */
    public function startGroup() : self
    {
        $this->pattern .= '(?:';
        $this->stack[] = 'G';

        return $this;
    }

    /**
     * @return self
     */
    public function endGroup() : self
    {
        if (array_pop($this->stack) !== 'G') {
            throw new \RuntimeException('Cannot call endGroup() without a call to startGroup() first.');
        }

        $this->pattern .= ')';

        return $this;
    }

    /**
     * @return self
     */
    public function appendOr() : self
    {
        $this->pattern .= '|';

        return $this;
    }

    /**
     * @return PatternParser
     */
    public function toParser() : PatternParser
    {
        if ($this->stack) {
            throw new \RuntimeException('Builder misses call to endOptional() or endGroup().');
        }

        return new PatternParser($this->pattern, $this->fields);
    }
}
