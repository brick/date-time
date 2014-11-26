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
     * "0123"   => 123
     * "1.5"    => exception
     * "123abc" => exception
     * " 123"   => exception
     * 1e30     => exception
     *
     * @param string $value The value to convert to integer.
     *
     * @return integer The converted integer value.
     */
    public static function toInteger($value)
    {
        if (is_string($value)) {
            if ($value === '' || ! ctype_digit($value)) {
                goto exception;
            }

            if ($value[0] === '0') {
                $value = ltrim($value, '0');
                if ($value === '') {
                    return 0;
                }
            }
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if ($integer === false) {
            goto exception;
        }

        return $integer;

        exception:
            throw new \InvalidArgumentException('Expected integer, got ' . gettype($value));
    }
}
