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
     * @param integer $a The first argument, validated as an integer.
     * @param integer $b The second argument, validated as a non-zero integer.
     *
     * @return integer
     */
    public static function floorDiv($a, $b)
    {
        $r = intdiv($a, $b);

        // If the signs are different and modulo not zero, round down.
        if (($a ^ $b) < 0 && ($r * $b != $a)) {
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
     * @param integer $a The first argument, validated as an integer.
     * @param integer $b The second argument, validated as a non-zero integer.
     *
     * @return integer
     */
    public static function floorMod($a, $b)
    {
        return (($a % $b) + $b) % $b;
    }
}
