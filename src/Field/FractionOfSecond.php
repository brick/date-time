<?php

declare(strict_types=1);

namespace Brick\DateTime\Field;

/**
 * The fraction-of-second field.
 *
 * This is used together with nano-of-second: fraction-of-second is used during parsing
 * as its length can range anywhere from 1 to 9; the number is then padded right with
 * zeros to make it 9 digits long, effectively becoming a nano-of-second.
 */
final class FractionOfSecond
{
    /**
     * The field name.
     */
    public const NAME = 'fraction-of-second';

    /**
     * The regular expression pattern of the ISO 8601 representation.
     */
    public const PATTERN = '[0-9]{1,9}';
}
