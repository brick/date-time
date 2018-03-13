<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Parser;

use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class DateTimeParseResult.
 */
class DateTimeParseResultTest extends AbstractTestCase
{
    /**
     * @expectedException        \Brick\DateTime\Parser\DateTimeParseException
     * @expectedExceptionMessage Field invalid_field_name is not present in the parsed result.
     */
    public function testGetFieldWithInvalidFieldStringName()
    {
        $dateTimeParseResult = new DateTimeParseResult();
        $dateTimeParseResult->getField('invalid_field_name');
    }
}
