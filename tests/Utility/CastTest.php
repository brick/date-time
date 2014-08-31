<?php

namespace Brick\Tests\DateTime\Utility;

use Brick\DateTime\Utility\Cast;

/**
 * Unit tests for the Cast utility class.
 */
class CastTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerToInteger
     *
     * @param mixed   $value  The value to type-cast.
     * @param integer $result The expected integer result.
     */
    public function testToInteger($value, $result)
    {
        $this->assertSame($result, Cast::toInteger($value));
    }

    /**
     * @return array
     */
    public function providerToInteger()
    {
        return [
            [123, 123],
            [123.0, 123],
            ["123", 123],
        ];
    }

    /**
     * @dataProvider providerToIntegerThrowsException
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $value The invalid value.
     */
    public function testToIntegerThrowsException($value)
    {
        Cast::toInteger($value);
    }

    /**
     * @return array
     */
    public function providerToIntegerThrowsException()
    {
        return [
            [1.5],
            [1e30],
            ["123abc"],
            ["abc123"],
        ];
    }
}
