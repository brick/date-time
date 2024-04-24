<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Field;

use Brick\DateTime\Field\DayOfMonth;
use Brick\DateTime\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

/**
 * Unit tests for class DayOfMonth.
 */
class DayOfMonthTest extends AbstractTestCase
{
    #[DoesNotPerformAssertions]
    public function testCheckWithNullMonthOfYear(): void
    {
        DayOfMonth::check(31);
    }
}
