<?php

declare(strict_types=1);

namespace Brick\DateTime\Tests\Formatter;

use Brick\DateTime\Formatter\DateTimeFormatException;
use Brick\DateTime\Formatter\IntlFormatter;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\Tests\AbstractTestCase;
use Brick\DateTime\TimeZoneOffset;
use Brick\DateTime\TimeZoneRegion;
use Brick\DateTime\ZonedDateTime;
use Throwable;

use function get_class;

class IntlFormatterTest extends AbstractTestCase
{
    /**
     * @dataProvider provideTestOfDateData
     *
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     * @param string|Throwable                                $expectedResult
     */
    public function testOfDate($value, int $format, $expectedResult): void
    {
        $formatter = IntlFormatter::ofDate('en_US', $format);

        if ($expectedResult instanceof Throwable) {
            $this->expectException(get_class($expectedResult));
            $this->expectExceptionMessage($expectedResult->getMessage());
        }

        $formatted = $value->format($formatter);
        self::assertSame($expectedResult, $formatted);
    }

    public function provideTestOfDateData(): iterable
    {
        $date = LocalDate::of(2022, 6, 8);
        yield [$date, IntlFormatter::FULL, 'Wednesday, June 8, 2022'];
        yield [$date, IntlFormatter::LONG, 'June 8, 2022'];
        yield [$date, IntlFormatter::MEDIUM, 'Jun 8, 2022'];
        yield [$date, IntlFormatter::SHORT, '6/8/22'];

        $dateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        yield [$dateTime, IntlFormatter::FULL, 'Wednesday, June 8, 2022'];

        $time = LocalTime::of(13, 37, 42, 999999999);
        yield [$time, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a date part cannot be used to format Brick\DateTime\LocalTime.')];

        $zoned = ZonedDateTime::of($dateTime, TimeZoneRegion::of('Europe/Prague'));
        yield [$zoned, IntlFormatter::FULL, 'Wednesday, June 8, 2022'];
    }

    /**
     * @dataProvider provideTestOfDateTimeData
     *
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     * @param string|Throwable                                $expectedResult
     */
    public function testOfDateTime($value, int $dateFormat, int $timeFormat, $expectedResult): void
    {
        $formatter = IntlFormatter::ofDateTime('en_US', $dateFormat, $timeFormat);

        if ($expectedResult instanceof Throwable) {
            $this->expectException(get_class($expectedResult));
            $this->expectExceptionMessage($expectedResult->getMessage());
        }

        $formatted = $value->format($formatter);
        self::assertSame($expectedResult, $formatted);
    }

    public function provideTestOfDateTimeData(): iterable
    {
        $date = LocalDate::of(2022, 6, 8);
        yield [$date, IntlFormatter::FULL, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a time part cannot be used to format Brick\DateTime\LocalDate.')];

        $dateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        yield [$dateTime, IntlFormatter::FULL, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a long or full time part cannot be used to format Brick\DateTime\LocalDateTime.')];
        yield [$dateTime, IntlFormatter::FULL, IntlFormatter::LONG, new DateTimeFormatException('IntlFormatter with a long or full time part cannot be used to format Brick\DateTime\LocalDateTime.')];
        yield [$dateTime, IntlFormatter::FULL, IntlFormatter::MEDIUM, 'Wednesday, June 8, 2022 at 1:37:42 PM'];
        yield [$dateTime, IntlFormatter::FULL, IntlFormatter::SHORT, 'Wednesday, June 8, 2022 at 1:37 PM'];
        yield [$dateTime, IntlFormatter::LONG, IntlFormatter::SHORT, 'June 8, 2022 at 1:37 PM'];
        yield [$dateTime, IntlFormatter::MEDIUM, IntlFormatter::MEDIUM, 'Jun 8, 2022, 1:37:42 PM'];
        yield [$dateTime, IntlFormatter::SHORT, IntlFormatter::LONG, new DateTimeFormatException('IntlFormatter with a long or full time part cannot be used to format Brick\DateTime\LocalDateTime.')];

        $time = LocalTime::of(13, 37, 42, 999999999);
        yield [$time, IntlFormatter::FULL, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a date part cannot be used to format Brick\DateTime\LocalTime.')];

        $zoned = ZonedDateTime::of($dateTime, TimeZoneRegion::of('Europe/Prague'));
        yield [$zoned, IntlFormatter::FULL, IntlFormatter::FULL, 'Wednesday, June 8, 2022 at 1:37:42 PM Central European Summer Time'];
        yield [$zoned, IntlFormatter::FULL, IntlFormatter::LONG, 'Wednesday, June 8, 2022 at 1:37:42 PM GMT+2'];
        yield [$zoned, IntlFormatter::FULL, IntlFormatter::MEDIUM, 'Wednesday, June 8, 2022 at 1:37:42 PM'];
        yield [$zoned, IntlFormatter::FULL, IntlFormatter::SHORT, 'Wednesday, June 8, 2022 at 1:37 PM'];
        yield [$zoned, IntlFormatter::LONG, IntlFormatter::SHORT, 'June 8, 2022 at 1:37 PM'];
        yield [$zoned, IntlFormatter::MEDIUM, IntlFormatter::MEDIUM, 'Jun 8, 2022, 1:37:42 PM'];
        yield [$zoned, IntlFormatter::SHORT, IntlFormatter::LONG, '6/8/22, 1:37:42 PM GMT+2'];
    }

    /**
     * @dataProvider provideTestOfTimeData
     *
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     * @param string|Throwable                                $expectedResult
     */
    public function testOfTime($value, int $format, $expectedResult): void
    {
        $formatter = IntlFormatter::ofTime('en_US', $format);

        if ($expectedResult instanceof Throwable) {
            $this->expectException(get_class($expectedResult));
            $this->expectExceptionMessage($expectedResult->getMessage());
        }

        $formatted = $value->format($formatter);
        self::assertSame($expectedResult, $formatted);
    }

    public function provideTestOfTimeData(): iterable
    {
        $date = LocalDate::of(2022, 6, 8);
        yield [$date, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a time part cannot be used to format Brick\DateTime\LocalDate.')];

        $dateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        yield [$dateTime, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a long or full time part cannot be used to format Brick\DateTime\LocalDateTime.')];

        $time = LocalTime::of(13, 37, 42, 999999999);
        yield [$time, IntlFormatter::FULL, new DateTimeFormatException('IntlFormatter with a long or full time part cannot be used to format Brick\DateTime\LocalTime.')];
        yield [$time, IntlFormatter::LONG, new DateTimeFormatException('IntlFormatter with a long or full time part cannot be used to format Brick\DateTime\LocalTime.')];
        yield [$time, IntlFormatter::MEDIUM, '1:37:42 PM'];
        yield [$time, IntlFormatter::SHORT, '1:37 PM'];

        $zoned = ZonedDateTime::of($dateTime, TimeZoneRegion::of('Europe/Prague'));
        yield [$zoned, IntlFormatter::FULL, '1:37:42 PM Central European Summer Time'];
    }

    /**
     * @dataProvider provideTestOfPatternData
     *
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     * @param string|Throwable                                $expectedResult
     */
    public function testOfPattern($value, string $pattern, $expectedResult): void
    {
        $formatter = IntlFormatter::ofPattern('en_US', $pattern);

        if ($expectedResult instanceof Throwable) {
            $this->expectException(get_class($expectedResult));
            $this->expectExceptionMessage($expectedResult->getMessage());
        }

        $formatted = $value->format($formatter);
        self::assertSame($expectedResult, $formatted);
    }

    public function provideTestOfPatternData(): iterable
    {
        $date = LocalDate::of(2022, 6, 8);
        yield [$date, 'eee, dd.MMM.yyyy', 'Wed, 08.Jun.2022'];
        yield [$date, 'H:mm:ss', new DateTimeFormatException("IntlFormatter with pattern 'H:mm:ss' is incompatible with type Brick\DateTime\LocalDate.")];
        yield [$date, 'XXX', new DateTimeFormatException("IntlFormatter with pattern 'XXX' is incompatible with type Brick\DateTime\LocalDate.")];

        $dateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        yield [$dateTime, 'eee, dd.MMM.yyyy', 'Wed, 08.Jun.2022'];
        yield [$dateTime, 'H:mm:ss', '13:37:42'];
        yield [$dateTime, 'XXX', new DateTimeFormatException("IntlFormatter with pattern 'XXX' is incompatible with type Brick\DateTime\LocalDateTime.")];

        $time = LocalTime::of(13, 37, 42, 999999999);
        yield [$time, 'eee, dd.MMM.yyyy', new DateTimeFormatException("IntlFormatter with pattern 'eee, dd.MMM.yyyy' is incompatible with type Brick\DateTime\LocalTime.")];
        yield [$time, 'H:mm:ss', '13:37:42'];
        yield [$time, 'XXX', new DateTimeFormatException("IntlFormatter with pattern 'XXX' is incompatible with type Brick\DateTime\LocalTime.")];

        $zoned = ZonedDateTime::of($dateTime, TimeZoneRegion::of('Europe/Prague'));
        yield [$zoned, 'eee, dd.MMM.yyyy', 'Wed, 08.Jun.2022'];
        yield [$zoned, 'H:mm:ss', '13:37:42'];
        yield [$zoned, 'VV', 'Europe/Prague'];
        yield [$zoned, 'XXX', '+02:00'];

        $zonedWithOffset = ZonedDateTime::of($dateTime, TimeZoneOffset::of(2));
        yield [$zonedWithOffset, 'eee, dd.MMM.yyyy', 'Wed, 08.Jun.2022'];
        yield [$zonedWithOffset, 'H:mm:ss', '13:37:42'];
        yield [$zonedWithOffset, 'VV', 'GMT+02:00'];
        yield [$zonedWithOffset, 'XXX', '+02:00'];
    }

    /**
     * @dataProvider provideTestOfSkeletonData
     *
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     * @param string|Throwable                                $expectedResult
     */
    public function testOfSkeleton($value, string $skeleton, $expectedResult): void
    {
        $formatter = IntlFormatter::ofSkeleton('en_US', $skeleton);

        if ($expectedResult instanceof Throwable) {
            $this->expectException(get_class($expectedResult));
            $this->expectExceptionMessage($expectedResult->getMessage());
        }

        $formatted = $value->format($formatter);
        self::assertSame($expectedResult, $formatted);
    }

    public function provideTestOfSkeletonData(): iterable
    {
        $date = LocalDate::of(2022, 6, 8);
        yield [$date, 'dMMMMyyyyeee', 'Wed, June 8, 2022'];
        yield [$date, 'Hms', new DateTimeFormatException("IntlFormatter with pattern 'HH:mm:ss' is incompatible with type Brick\DateTime\LocalDate.")];
        yield [$date, 'XXX', new DateTimeFormatException("IntlFormatter with pattern 'XXX' is incompatible with type Brick\DateTime\LocalDate.")];

        $dateTime = LocalDateTime::of(2022, 6, 8, 13, 37, 42, 999999999);
        yield [$dateTime, 'dMMMMyyyyeee', 'Wed, June 8, 2022'];
        yield [$dateTime, 'Hms', '13:37:42'];
        yield [$dateTime, 'XXX', new DateTimeFormatException("IntlFormatter with pattern 'XXX' is incompatible with type Brick\DateTime\LocalDateTime.")];

        $time = LocalTime::of(13, 37, 42, 999999999);
        yield [$time, 'dMMMMyyyyeee', new DateTimeFormatException("IntlFormatter with pattern 'EEE, MMMM d, yyyy' is incompatible with type Brick\DateTime\LocalTime.")];
        yield [$time, 'Hms', '13:37:42'];
        yield [$time, 'XXX', new DateTimeFormatException("IntlFormatter with pattern 'XXX' is incompatible with type Brick\DateTime\LocalTime.")];

        $zoned = ZonedDateTime::of($dateTime, TimeZoneRegion::of('Europe/Prague'));
        yield [$zoned, 'dMMMMyyyyeee', 'Wed, June 8, 2022'];
        yield [$zoned, 'Hms', '13:37:42'];
        yield [$zoned, 'VV', 'Europe/Prague'];
        yield [$zoned, 'XXX', '+02:00'];

        $zonedWithOffset = ZonedDateTime::of($dateTime, TimeZoneOffset::of(2));
        yield [$zonedWithOffset, 'dMMMMyyyyeee', 'Wed, June 8, 2022'];
        yield [$zonedWithOffset, 'Hms', '13:37:42'];
        yield [$zonedWithOffset, 'VV', 'GMT+02:00'];
        yield [$zonedWithOffset, 'XXX', '+02:00'];
    }
}
