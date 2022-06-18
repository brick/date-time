# Changelog

## [0.4.1](https://github.com/brick/date-time/releases/tag/0.4.1) - 2022-06-18

✨ **New methods**

- `LocalDateRange::toPeriod()` ([#51](https://github.com/brick/date-time/issues/51))
- `Year::toLocalDateRange()` ([#46](https://github.com/brick/date-time/pull/46))
- `YearMonth::toLocalDateRange()` ([#46](https://github.com/brick/date-time/pull/46))
- `YearMonthRange::toLocalDateRange()` ([#46](https://github.com/brick/date-time/pull/46))
- `YearWeek::toLocalDateRange()` ([#46](https://github.com/brick/date-time/pull/46))

💩 **Deprecations**

The following methods have been deprecated in favour of new names ([#47](https://github.com/brick/date-time/issues/47)):

- `Period::fromDateInterval()` → `fromNativeDateInterval()`
- `Period::toDateInterval()` → `toNativeDateInterval()`
- `TimeZone::fromDateTimeZone()` → `fromNativeDateTimeZone()`
- `TimeZone::toDateTimeZone()` → `toNativeDateTimeZone()`
- `LocalTime::fromDateTime()` → `fromNativeDateTime()`
- `LocalTime::toDateTime()` → `toNativeDateTime()`
- `LocalTime::toDateTimeImmutable()` → `toNativeDateTimeImmutable()`
- `LocalDateRange::toDatePeriod()` → `toNativeDatePeriod()`
- `LocalDate::fromDateTime()` → `fromNativeDateTime()`
- `LocalDate::toDateTime()` → `toNativeDateTime()`
- `LocalDate::toDateTimeImmutable()` → `toNativeDateTimeImmutable()`
- `ZonedDateTime::fromDateTime()` → `fromNativeDateTime()`
- `ZonedDateTime::toDateTime()` → `toNativeDateTime()`
- `ZonedDateTime::toDateTimeImmutable()` → `toNativeDateTimeImmutable()`
- `LocalDateTime::fromDateTime()` → `fromNativeDateTime()`
- `LocalDateTime::toDateTime()` → `toNativeDateTime()`
- `LocalDateTime::toDateTimeImmutable()` → `toNativeDateTimeImmutable()`

## [0.4.0](https://github.com/brick/date-time/releases/tag/0.4.0) - 2021-12-23

💥 **Breaking changes**

- **Minimum PHP version is now 7.4**
- `TimeZoneOffset` does not allow seconds anymore (#35):
  - `TimeZoneOffset::of()`'s `$seconds` parameter is removed
  - `TimeZoneOffset::ofTotalSeconds()` now throws if the number of seconds is not a multiple of `60`
  - `IsoParsers::timeZoneOffset()` does not allow seconds in timezone offset anymore; this affects not only `TimeZoneOffset:parse()` but also `ZonedDateTime::parse()`
  
🔧 **Fix**

- Fixed return type of `TimeZoneRegion::parse()` (#38) Thanks to @adrianguenter

✨ **New methods**

- `Period::fromDateInterval()` converts a native `DateInterval` object to `Period`

## [0.3.2](https://github.com/brick/date-time/releases/tag/0.3.2) - 2021-06-30

✨ **New methods**

- `DayOfWeek::isWeekday()`
- `DayOfWeek::isWeekend()`
- `LocalDate::plusWeekdays()`
- `LocalDate::minusWeekDays()`

## [0.3.1](https://github.com/brick/date-time/releases/tag/0.3.1) - 2021-06-29

✨ **New methods**

- `LocalDateRange::withStart()`
- `LocalDateRange::withEnd()`

## [0.3.0](https://github.com/brick/date-time/releases/tag/0.3.0) - 2021-04-24

💥 **Breaking changes**

- The following methods now have return types:
  - `Brick\DateTime\DayOfWeek::monday()` through `sunday()`
  - `Brick\DateTime\DayOfWeek::__toString()`
  - `Brick\DateTime\Month::__toString()`

## [0.2.3](https://github.com/brick/date-time/releases/tag/0.2.3) - 2021-02-26

✨ **New method**

- `LocalDateRange::toDatePeriod()` (#31) Thanks to @morrislaptop

🔧 **Fix**

- Added missing `ext-json` requirement in `composer.json`

## [0.2.2](https://github.com/brick/date-time/releases/tag/0.2.2) - 2020-07-30

🐛 **Bug fix**

`LocalDateTime::plusDuration()` and `LocalDateTime::minusDuration()` could return the wrong day. (#25)

Thanks @JodyLognoul!

## [0.2.1](https://github.com/brick/date-time/releases/tag/0.2.1) - 2020-04-06

✨ **New methods**

- `LocalDateRange::intersectsWith()`
- `LocalDateRange::getIntersectionWith()`

Thanks @solodkiy!

## [0.2.0](https://github.com/brick/date-time/releases/tag/0.2.0) - 2020-01-08

💥 **Breaking changes**

- most of the project classes are now `final` (#8)
- the following deprecated methods have been removed:
    - `LocalDateRange::getStartDate()` - use `getStart()` instead (#13)
    - `LocalDateRange::getEndDate()` - use `getEnd()` instead (#13)
    - `Duration::ofMilliseconds()` - use `ofMillis()` instead

## [0.1.16](https://github.com/brick/date-time/releases/tag/0.1.16) - 2020-01-08

✨ **New methods**

- `LocalDateRange::getStart()` - deprecates `getStartDate()`
- `LocalDateRange::getEnd()` - deprecates `getEndDate()`

💩 **Deprecations**

- `LocalDateRange::getStartDate()` is now deprecated and will be removed in `0.2.0`.
- `LocalDateRange::getEndDate()` is now deprecated and will be removed in `0.2.0`.

This makes `LocalDateRange` consistent with `YearMonthRange`.

## [0.1.15](https://github.com/brick/date-time/releases/tag/0.1.15) - 2020-01-07

🛠 **Improvement**

The following classes now implement `JsonSerializable` (#19):

- `DayOfWeek`
- `Duration`
- `Instant`
- `Interval`
- `LocalDate`
- `LocalDateRange`
- `LocalDateTime`
- `LocalTime`
- `Month`
- `MonthDay`
- `Period`
- `Year`
- `YearMonth`
- `YearMonthRange`
- `YearWeek`
- `ZonedDateTime`

Thanks to @kagmole!

## [0.1.14](https://github.com/brick/date-time/releases/tag/0.1.14) - 2019-09-17

✨ **New methods**

- `Duration::toDays()`
- `Duration::toDaysPart()`
- `Duration::toHours()`
- `Duration::toHoursPart()`
- `Duration::toMinutes()`
- `Duration::toMinutesPart()`
- `Duration::toSeconds()`
- `Duration::toSecondsPart()`
- `Duration::toMillis()`
- `Duration::toMillisPart()`
- `Duration::toNanos()`
- `Duration::toNanosPart()`
- `Duration::ofNanos()`

See #15 for more information.

💩 **Deprecations**

- `Duration::ofMilliseconds()` is now `ofMillis()`;  `ofMilliseconds()` is now deprecated and will be removed in `0.2.0`.

## [0.1.13](https://github.com/brick/date-time/releases/tag/0.1.13) - 2019-04-17

**New methods**

- `LocalDateTime::isFuture()`
- `LocalDateTime::isPast()`

These methods return whether a local date-time is in the future or in the past, in a given time-zone.

## [0.1.12](https://github.com/brick/date-time/releases/tag/0.1.12) - 2019-04-15

**New methods**

- `Instant::withEpochSecond()` returns a copy of the `Instant` with the epoch second altered
- `Instant::withNano()` returns a copy of the `Instant` with the nano-of-second altered

## [0.1.11](https://github.com/brick/date-time/releases/tag/0.1.11) - 2019-03-08

New class: `YearMonthRange`.

## [0.1.10](https://github.com/brick/date-time/releases/tag/0.1.10) - 2019-03-08

`LocalDateRange::getIterator()` is now type-hinted as `LocalDate[]` to allow static code analysis in IDEs.

## [0.1.9](https://github.com/brick/date-time/releases/tag/0.1.9) - 2018-11-14

New method: `LocalDate::daysUntil()`

This allows to get the number of days between two dates.

## [0.1.8](https://github.com/brick/date-time/releases/tag/0.1.8) - 2018-10-29

New methods to convert objects to native `DateTimeImmutable` objects:

- `LocalDate::toDateTimeImmutable()`
- `LocalTime::toDateTimeImmutable()`
- `LocalDateTime::toDateTimeImmutable()`
- `ZonedDateTime::toDateTimeImmutable()`

## [0.1.7](https://github.com/brick/date-time/releases/tag/0.1.7) - 2018-10-18

New methods to convert to native `DateTime` objects:

- `LocalDate::toDateTime()`
- `LocalTime::toDateTime()`
- `LocalDateTime::toDateTime()`

This makes these methods in line with their `fromDateTime()` counterparts.

*Note: even though these classes represent partial date-time concepts, PHP merges all these concepts into a single `DateTime` class, so it is relevant to be able to easily export them as native PHP date-times, with sensible defaults.*

## [0.1.6](https://github.com/brick/date-time/releases/tag/0.1.6) - 2018-10-18

**New method**:

`ZonedDateTime::toDateTime()` : converts the `ZonedDateTime` to a native `DateTime` object.

## [0.1.5](https://github.com/brick/date-time/releases/tag/0.1.5) - 2018-10-13

New factory method:

- `Duration::ofMilliseconds()`

## [0.1.4](https://github.com/brick/date-time/releases/tag/0.1.4) - 2018-03-14

New methods available to create objects from native `DateTime` or `DateTimeImmutable` objects:

- `LocalDate::fromDateTime()`
- `LocalTime::fromDateTime()`
- `LocalDateTime::fromDateTime()`
- `ZonedDateTime::fromDateTime()`

## [0.1.3](https://github.com/brick/date-time/releases/tag/0.1.3) - 2018-02-06

This release adds support for `YearWeek`.

## [0.1.2](https://github.com/brick/date-time/releases/tag/0.1.2) - 2017-10-04

Bug fix:

- `Duration::multipliedBy()` could return an invalid Duration with negative nanos

New methods:

- `Instant::toDecimal()` returns a decimal timestamp such as `123456789.123456`
- `FixedClock::move()` allows to move the clock by a number of seconds and/or nanos

New clock implementation:

- `ScaleClock` makes the time move at a given pace

New feature:

- Methods such as `now()` now use the clock returned by `DefaultClock`. By default, this clock is still `SystemClock`, but it can now be overridden in tests. DefaultClock offers the `freeze()`, `travel()` and `scale()` methods inspired by [timecop](https://github.com/travisjeffery/timecop).

## [0.1.1](https://github.com/brick/date-time/releases/tag/0.1.1) - 2017-10-04

`ZonedDateTime::isPast()` and `isFuture()` now accept an optional `Clock` parameter.

## [0.1.0](https://github.com/brick/date-time/releases/tag/0.1.0) - 2017-10-04

First beta release.

