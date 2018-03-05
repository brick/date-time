<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Field\DayOfMonth;

/**
 * Unit tests for class DayOfMonth.
 */
class DayOfMonthTest extends AbstractTestCase
{
    public function testCheckWithNullMonthOfYear()
    {
        $dayOfMonth = new DayOfMonth();

        $this->assertNull($dayOfMonth->check(31));
    }
}
