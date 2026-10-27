<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;
use Override;

/**
 * Represents a quarter-of-year.
 */
enum Quarter: int implements JsonSerializable
{
    /**
     * January 1 to March 31.
     */
    case Q1 = 1;

    /**
     * April 1 to June 30.
     */
    case Q2 = 2;

    /**
     * July 1 to September 30.
     */
    case Q3 = 3;

    /**
     * October 1 to December 31.
     */
    case Q4 = 4;

    /**
     * Serializes as an integer.
     */
    #[Override]
    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
