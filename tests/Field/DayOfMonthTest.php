<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Field;

use Brick\DateTime\Field\DayOfMonth;
use Brick\DateTime\Tests\AbstractTestCase;

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
