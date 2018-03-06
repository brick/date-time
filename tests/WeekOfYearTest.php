<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests;

use Brick\DateTime\Field\WeekOfYear;

/**
 * Unit tests for class WeekOfYear.
 */
class WeekOfYearTest extends AbstractTestCase
{
    /**
     * @expectedException        Brick\DateTime\DateTimeException
     * @expectedExceptionMessage Invalid week-of-year: -1 is not in the range 1 to 53.
     */
    public function testCheckShouldReturnDateTimeExceptionWithFieldNotInRange()
    {
        WeekOfYear::check(-1);
    }

    /**
     * @expectedException        Brick\DateTime\DateTimeException
     * @expectedExceptionMessage Year 2000 does not have 53 weeks
     */
    public function testCehckTimeShouldThrowDateYimeException()
    {
        WeekOfYear::check(53, 2000);
    }
}
