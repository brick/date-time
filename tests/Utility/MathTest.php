<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Utility;

use Brick\DateTime\Utility\Math;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Math utility class.
 */
class MathTest extends TestCase
{
    /**
     * @dataProvider providerFloorDiv
     *
     * @param int $a        The dividend.
     * @param int $b        The divisor.
     * @param int $expected The expected floor division result.
     */
    public function testFloorDiv(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, Math::floorDiv($a, $b));
    }

    public function providerFloorDiv(): array
    {
        return [
            [3,  2,  1],
            [3, -2, -2],
            [-3,  2, -2],
            [-3, -2,  1],
        ];
    }

    /**
     * @dataProvider providerFloorMod
     *
     * @param int $a        The dividend.
     * @param int $b        The divisor.
     * @param int $expected The expected floor modulus result.
     */
    public function testFloorMod(int $a, int $b, int $expected): void
    {
        $this->assertSame($expected, Math::floorMod($a, $b));
    }

    public function providerFloorMod(): array
    {
        return [
            [3,  2,  1],
            [3, -2, -1],
            [-3,  2,  1],
            [-3, -2, -1],
        ];
    }
}
