<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Field;

use Brick\DateTime\DateTimeException;
use Brick\DateTime\Field\WeekOfYear;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class WeekOfYear.
 */
class WeekOfYearTest extends AbstractTestCase
{
    public function testCheckShouldThrowDateTimeExceptionWithFieldNotInRange()
    {
        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage('Invalid week-of-year: -1 is not in the range 1 to 53.');

        WeekOfYear::check(-1);
    }

    public function testCheckShouldThrowDateTimeExceptionWith52WeekYear()
    {
        $this->expectException(DateTimeException::class);
        $this->expectExceptionMessage('Year 2000 does not have 53 weeks');

        WeekOfYear::check(53, 2000);
    }
}
