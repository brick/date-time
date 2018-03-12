<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Field;

use Brick\DateTime\Field\DayOfMonth;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class DayOfMonth.
 *
 * @doesNotPerformAssertions
 */
class DayOfMonthTest extends AbstractTestCase
{
    public function testCheckWithNullMonthOfYear()
    {
        $dayOfMonth = new DayOfMonth();

        $dayOfMonth->check(31);
    }
}
