<?php

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
     * @param integer $a        The dividend.
     * @param integer $b        The divisor.
     * @param integer $expected The expected floor division result.
     */
    public function testFloorDiv($a, $b, $expected)
    {
        $this->assertSame($expected, Math::floorDiv($a, $b));
    }

    /**
     * @return array
     */
    public function providerFloorDiv()
    {
        return [
            [ 3,  2,  1],
            [ 3, -2, -2],
            [-3,  2, -2],
            [-3, -2,  1]
        ];
    }

    /**
     * @dataProvider providerFloorMod
     *
     * @param integer $a        The dividend.
     * @param integer $b        The divisor.
     * @param integer $expected The expected floor modulus result.
     */
    public function testFloorMod($a, $b, $expected)
    {
        $this->assertSame($expected, Math::floorMod($a, $b));
    }

    /**
     * @return array
     */
    public function providerFloorMod()
    {
        return [
            [ 3,  2,  1],
            [ 3, -2, -1],
            [-3,  2,  1],
            [-3, -2, -1]
        ];
    }
}
