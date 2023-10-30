<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Formatter;

use Brick\DateTime\Formatter\DateTimeFormatException;
use Brick\DateTime\Formatter\NativeFormatter;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Tests\AbstractTestCase;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use Brick\DateTime\ZonedDateTime;
use Throwable;

use function get_class;

class NativeFormatterTest extends AbstractTestCase
{
    /**
     * @dataProvider provideTestFormatData
     *
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     * @param string|Throwable                                $expectedResult
     */
    public function testFormat($value, string $format, $expectedResult): void
    {
        $formatter = NativeFormatter::of($format);

        if ($expectedResult instanceof Throwable) {
            $this->expectException(get_class($expectedResult));
            $this->expectExceptionMessage($expectedResult->getMessage());
        }

        $formatted = $value->format($formatter);
        self::assertSame($expectedResult, $formatted);
    }

    public function provideTestFormatData(): iterable
    {
        $date = LocalDate::of(2022, 6, 8);
        yield [$date, 'd.m.y', '08.06.22'];
        yield [$date, 'M j, Y', 'Jun 8, 2022'];
        yield [$date, 'D', 'Wed'];
        yield [$date, 'H:i:s', new DateTimeFormatException("Formatting pattern 'H:i:s' is incompatible with type Brick\DateTime\LocalDate.")];
        yield [$date, 'U', new DateTimeFormatException("Formatting pattern 'U' is incompatible with type Brick\DateTime\LocalDate.")];

        $dateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        yield [$dateTime, 'd.m.y', '08.06.22'];
        yield [$dateTime, 'M j, Y', 'Jun 8, 2022'];
        yield [$dateTime, 'D', 'Wed'];
        yield [$dateTime, 'H:i:s', '13:37:42'];
        yield [$dateTime, 'u', '999999'];
        yield [$dateTime, 'U', new DateTimeFormatException("Formatting pattern 'U' is incompatible with type Brick\DateTime\LocalDateTime.")];

        $time = LocalTime::of(13, 37, 42, 999999999);
        yield [$time, 'd.m.y', new DateTimeFormatException("Formatting pattern 'd.m.y' is incompatible with type Brick\DateTime\LocalTime.")];
        yield [$time, 'D', new DateTimeFormatException("Formatting pattern 'D' is incompatible with type Brick\DateTime\LocalTime.")];
        yield [$time, 'H:i:s', '13:37:42'];
        yield [$time, 'u', '999999'];
        yield [$time, 'U', new DateTimeFormatException("Formatting pattern 'U' is incompatible with type Brick\DateTime\LocalTime.")];

        $zoned = ZonedDateTime::of($dateTime, TimeZoneRegion::of('Europe/Prague'));
        yield [$zoned, 'd.m.y', '08.06.22'];
        yield [$zoned, 'M j, Y', 'Jun 8, 2022'];
        yield [$zoned, 'D', 'Wed'];
        yield [$zoned, 'H:i:s', '13:37:42'];
        yield [$zoned, 'u', '999999'];
        yield [$zoned, 'U', '1654688262'];
        yield [$zoned, 'e', 'Europe/Prague'];
        yield [$zoned, 'p', '+02:00'];

        $zonedWithOffset = ZonedDateTime::of($dateTime, TimeZoneOffset::of(2));
        yield [$zonedWithOffset, 'd.m.y', '08.06.22'];
        yield [$zonedWithOffset, 'M j, Y', 'Jun 8, 2022'];
        yield [$zonedWithOffset, 'D', 'Wed'];
        yield [$zonedWithOffset, 'H:i:s', '13:37:42'];
        yield [$zonedWithOffset, 'u', '999999'];
        yield [$zonedWithOffset, 'U', '1654688262'];
        yield [$zonedWithOffset, 'e', '+02:00'];
        yield [$zonedWithOffset, 'p', '+02:00'];
    }
}
