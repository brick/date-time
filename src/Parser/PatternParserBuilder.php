<?php

declare(strict_types=1);

namespace Brick\DateTime\Parser;

use RuntimeException;

use function array_merge;
use function array_pop;
use function preg_quote;

/**
 * Builds a PatternParser with a fluent API.
 */
final class PatternParserBuilder
{
    private string $pattern = '';

    /**
     * @var string[]
     */
    private array $fields = [];

    /**
     * @var string[]
     */
    private array $stack = [];

    public function append(PatternParser $parser): self
    {
        $this->pattern .= $parser->getPattern();
        $this->fields = array_merge($this->fields, $parser->getFields());

        return $this;
    }

    public function appendLiteral(string $literal): self
    {
        $this->pattern .= preg_quote($literal, '/');

        return $this;
    }

    public function appendCapturePattern(string $pattern, string $field): self
    {
        $this->pattern .= '(' . $pattern . ')';
        $this->fields[] = $field;

        return $this;
    }

    public function startOptional(): self
    {
        $this->pattern .= '(?:';
        $this->stack[] = 'O';

        return $this;
    }

    public function endOptional(): self
    {
        if (array_pop($this->stack) !== 'O') {
            throw new RuntimeException('Cannot call endOptional() without a call to startOptional() first.');
        }

        $this->pattern .= ')?';

        return $this;
    }

    public function startGroup(): self
    {
        $this->pattern .= '(?:';
        $this->stack[] = 'G';

        return $this;
    }

    public function endGroup(): self
    {
        if (array_pop($this->stack) !== 'G') {
            throw new RuntimeException('Cannot call endGroup() without a call to startGroup() first.');
        }

        $this->pattern .= ')';

        return $this;
    }

    public function appendOr(): self
    {
        $this->pattern .= '|';

        return $this;
    }

    public function toParser(): PatternParser
    {
        if ($this->stack !== []) {
            throw new RuntimeException('Builder misses call to endOptional() or endGroup().');
        }

        return new PatternParser($this->pattern, $this->fields);
    }
}
