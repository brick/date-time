<?php

declare(strict_types=1);

namespace Brick\DateTime\Utility;

use ArithmeticError;

use function intdiv;
use function is_float;

/**
 * Internal utility class for calculations on integers.
 *
 * @internal
 */
final class Math
{
    /**
     * @throws ArithmeticError
     */
    public static function addExact(int $a, int $b): int
    {
        $result = $a + $b;

        if (is_float($result)) {
            throw new ArithmeticError('The result of the operation overflows an integer on this platform.');
        }

        return $result;
    }

    /**
     * @throws ArithmeticError
     */
    public static function multiplyExact(int $a, int $b): int
    {
        $result = $a * $b;

        if (is_float($result)) {
            throw new ArithmeticError('The result of the operation overflows an integer on this platform.');
        }

        return $result;
    }

    /**
     * Returns the largest integer value that is less than or equal to the algebraic quotient.
     *
     * @param int $a The first argument.
     * @param int $b The second argument, non-zero.
     */
    public static function floorDiv(int $a, int $b): int
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
     */
    public static function floorMod(int $a, int $b): int
    {
        return (($a % $b) + $b) % $b;
    }
}
