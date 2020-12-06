<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Parser;

use Brick\DateTime\Parser\DateTimeParseException;
use Brick\DateTime\Parser\DateTimeParseResult;
use Brick\DateTime\Tests\AbstractTestCase;

/**
 * Unit tests for class DateTimeParseResult.
 */
class DateTimeParseResultTest extends AbstractTestCase
{
    public function testGetFieldWithInvalidFieldStringName()
    {
        $dateTimeParseResult = new DateTimeParseResult();

        $this->expectException(DateTimeParseException::class);
        $this->expectExceptionMessage('Field invalid_field_name is not present in the parsed result.');

        $dateTimeParseResult->getField('invalid_field_name');
    }
}
