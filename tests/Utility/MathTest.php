<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Utility;

use Brick\DateTime\Utility\Math;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Math utility class.
 */
class MathTest extends TestCase
{
    /**
     * @param int $a        The dividend.
     * @param int $b        The divisor.
     * @param int $expected The expected floor division result.
     */
    #[DataProvider('providerFloorDiv')]
    public function testFloorDiv(int $a, int $b, int $expected): void
    {
        self::assertSame($expected, Math::floorDiv($a, $b));
    }

    public static function providerFloorDiv(): array
    {
        return [
            [3,  2,  1],
            [3, -2, -2],
            [-3,  2, -2],
            [-3, -2,  1],
        ];
    }

    /**
     * @param int $a        The dividend.
     * @param int $b        The divisor.
     * @param int $expected The expected floor modulus result.
     */
    #[DataProvider('providerFloorMod')]
    public function testFloorMod(int $a, int $b, int $expected): void
    {
        self::assertSame($expected, Math::floorMod($a, $b));
    }

    public static function providerFloorMod(): array
    {
        return [
            [3,  2,  1],
            [3, -2, -1],
            [-3,  2,  1],
            [-3, -2, -1],
        ];
    }
}
