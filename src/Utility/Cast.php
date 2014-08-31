<?php

namespace Brick\DateTime\Utility;

/**
 * Type-casting utility class.
 *
 * @internal
 */
final class Cast
{
    /**
     * Casts a variable to integer, ensuring that no data is silently lost.
     *
     * 123      => 123
     * 123.0    => 123
     * "123"    => 123
     * "1.5"    => exception
     * "123abc" => exception
     * 1e30     => exception
     *
     * @param string $value The value to convert to integer.
     *
     * @return integer The converted integer value.
     */
    public static function toInteger($value)
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if ($integer === false) {
            throw new \InvalidArgumentException('Expected integer, got ' . gettype($value));
        }

        return $integer;
    }
}
