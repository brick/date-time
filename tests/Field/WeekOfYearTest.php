<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Field;

use Brick\DateTime\Field\WeekOfYear;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class WeekOfYear.
 */
class WeekOfYearTest extends AbstractTestCase
{
    /**
     * @expectedException        \Brick\DateTime\DateTimeException
     * @expectedExceptionMessage Invalid week-of-year: -1 is not in the range 1 to 53.
     */
    public function testCheckShouldThrowDateTimeExceptionWithFieldNotInRange()
    {
        WeekOfYear::check(-1);
    }

    /**
     * @expectedException        \Brick\DateTime\DateTimeException
     * @expectedExceptionMessage Year 2000 does not have 53 weeks
     */
    public function testCheckShouldThrowDateTimeExceptionWith52WeekYear()
    {
        WeekOfYear::check(53, 2000);
    }
}
