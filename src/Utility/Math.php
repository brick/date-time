<?php

namespace Brick\DateTime\Utility;

/**
 * Internal utility class for calculations on integers.
 *
 * @internal
 */
final class Math
{
    /**
     * Returns the largest integer value that is less than or equal to the algebraic quotient.
     *
     * @param int $a The first argument.
     * @param int $b The second argument, non-zero.
     *
     * @return int
     */
    public static function floorDiv(int $a, int $b) : int
    {
        $r = intdiv($a, $b);

        // If the signs are different and modulo not zero, round down.
        if (($a ^ $b) < 0 && ($r * $b !== $a)) {
            $r--;
        }

        return $r;
    }

    /**
     * Returns the floor modulus of the integer arguments.
     *
     * The relationship between floorDiv and floorMod is such that:
     * floorDiv(x, y) * y + floorMod(x, y) == x
     *
     * @param int $a The first argument.
     * @param int $b The second argument, non-zero.
     *
     * @return int
     */
    public static function floorMod(int $a, int $b) : int
    {
        return (($a % $b) + $b) % $b;
    }
}
