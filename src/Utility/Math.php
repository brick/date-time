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
     * Divides two integers into a quotient and a remainder.
     *
     * @param integer $a The dividend, validated as an integer.
     * @param integer $b The divisor, validated as a non-zero integer.
     * @param integer $r An optional variable to store the remainder of the division.
     *
     * @return integer The quotient of the division.
     */
    public static function div($a, $b, & $r = null)
    {
        return ($a - ($r = $a % $b)) / $b;
    }

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
        $r = self::div($a, $b);

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
