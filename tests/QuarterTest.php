<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Quarter;
use PHPUnit\Framework\Attributes\DataProvider;

use function json_encode;

use const JSON_THROW_ON_ERROR;

/**
 * Unit tests for class Quarter.
 */
class QuarterTest extends AbstractTestCase
{
    /**
     * @param int     $expectedValue The expected value of the constant.
     * @param Quarter $quarter       The quarter instance.
     */
    #[DataProvider('providerValues')]
    public function testValues(int $expectedValue, Quarter $quarter): void
    {
        self::assertSame($expectedValue, $quarter->value);
    }

    public static function providerValues(): array
    {
        return [
            [1, Quarter::Q1],
            [2, Quarter::Q2],
            [3, Quarter::Q3],
            [4, Quarter::Q4],
        ];
    }

    /**
     * @param Quarter $quarter      The quarter.
     * @param string  $expectedJson The representation in JSON of the quarter.
     */
    #[DataProvider('provideJsonSerialize')]
    public function testJsonSerialize(Quarter $quarter, string $expectedJson): void
    {
        self::assertSame($expectedJson, json_encode($quarter, JSON_THROW_ON_ERROR));
    }

    public static function provideJsonSerialize(): array
    {
        return [
            [Quarter::Q1, '1'],
            [Quarter::Q2, '2'],
            [Quarter::Q3, '3'],
            [Quarter::Q4, '4'],
        ];
    }
}
