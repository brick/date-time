# Changelog

## [0.7.0](https://github.com/brick/date-time/releases/tag/0.7.0) - 2024-06-23

💥 **Breaking changes**

- `DayOfWeek`:
  - deprecated method `of()` has been removed, use `DayOfWeek::from()` instead
  - the following deprecated methods have been removed, use enum values instead:
    - `DayOfWeek::monday()` → `DayOfWeek::MONDAY`
    - `DayOfWeek::tuesday()` → `DayOfWeek::TUESDAY`
    - `DayOfWeek::wednesday()` → `DayOfWeek::WEDNESDAY`
    - `DayOfWeek::thursday()` → `DayOfWeek::THURSDAY`
    - `DayOfWeek::friday()` → `DayOfWeek::FRIDAY`
    - `DayOfWeek::saturday()` → `DayOfWeek::SATURDAY`
    - `DayOfWeek::sunday()` → `DayOfWeek::SUNDAY`
  - deprecated method `getValue()` has been removed, use `$dayOfWeek->value` instead
  - deprecated method `is()` has been removed, compare values with `$dayOfWeek` or `$dayOfWeek->value` instead
  - deprecated method `isEqualTo()` has been removed, use strict equality `===` between `DayOfWeek` instances instead.
- `DefaultClock`:
  - deprecated method `travel()` has been removed, use `travelTo()` instead
- `LocalDate`:
  - deprecated method `getDay()` has been removed, use `getDayOfMonth()` instead
  - `getMonth()` now returns a `Month` enum; use `getMonthValue()` if you want the month number
- `LocalDateTime`:
  - deprecated method `getDay()` has been removed, use `getDayOfMonth()` instead
  - `getMonth()` now returns a `Month` enum; use `getMonthValue()` if you want the month number
- `Month`:
  - deprecated method `of()` has been removed, use `Month::from()` instead
  - deprecated method `getAll()` has been removed, use `Month::cases()` instead
  - deprecated method `getValue()` has been removed, use `$month->value` instead
  - deprecated method `is()` has been removed, compare values with `$month` or `$month->value` instead
  - deprecated method `isEqualTo()` has been removed, use strict equality `===` between `Month` instances instead
- `MonthDay`:
  - deprecated method `getDay()` has been removed, use `getDayOfMonth()` instead
  - `getMonth()` now returns a `Month` enum; use `getMonthValue()` if you want the month number
- `YearMonth`:
  - `getMonth()` now returns a `Month` enum; use `getMonthValue()` if you want the month number
- `ZonedDateTime`:
  - deprecated method `getDay()` has been removed, use `getDayOfMonth()` instead
  - `getMonth()` now returns a `Month` enum; use `getMonthValue()` if you want the month number

## [0.6.5](https://github.com/brick/date-time/releases/tag/0.6.5) - 2024-06-19

✨ **New methods**

- `LocalDate::previousDayOfWeek()`
- `LocalDate::previousOrSameDayOfWeek()`
- `LocalDate::nextDayOfWeek()`
- `LocalDate::nextOrSameDayOfWeek()`

## [0.6.4](https://github.com/brick/date-time/releases/tag/0.6.4) - 2024-04-25

✨ **New features**

- `DefaultClock::travelBy()` travels in time by a duration ([#92](https://github.com/brick/date-time/pull/92))

💩 **Deprecations**

- `DefaultClock::travel()` is now deprecated in favour of `travelTo()` ([#92](https://github.com/brick/date-time/pull/92))

Thanks to [@francislavoie](https://github.com/francislavoie)!

## [0.6.3](https://github.com/brick/date-time/releases/tag/0.6.3) - 2024-04-02

✨ **New features**

- `Stopwatch::stop()` now returns the lap duration ([#98](https://github.com/brick/date-time/pull/98))

Thanks to [@rodnaph](https://github.com/rodnaph)!

## [0.6.2](https://github.com/brick/date-time/releases/tag/0.6.2) - 2024-04-01

✨ **New features**

- `MonthDay::of()` and `MonthDay::withMonth()` now accept a `Month` enum as parameter ([#106](https://github.com/brick/date-time/pull/106))
- `LocalDate::of()` and `LocalDate::withMonth()` now accept a `Month` enum as parameter ([#106](https://github.com/brick/date-time/pull/106))
- `LocalDateTime::of()` and `LocalDateTime::withMonth()` now accept a `Month` enum as parameter ([#106](https://github.com/brick/date-time/pull/106))
- `ZonedDateTime::withMonth()` now accepts a `Month` enum as parameter ([#106](https://github.com/brick/date-time/pull/106))

✨ **Undeprecations**

- Passing an `int` to `Year::atMonth()` is un-deprecated, and now valid again ([#103](https://github.com/brick/date-time/pull/103))
- Passing an `int` to `YearMonth::of()` and `YearMonth::withMonth()` is un-deprecated, and now valid again ([#103](https://github.com/brick/date-time/pull/103))
- Passing an `int` to `YearWeek::atDay()` is un-deprecated, and now valid again ([#103](https://github.com/brick/date-time/pull/103))

Thanks to [@gnutix](https://github.com/gnutix)!

## [0.6.1](https://github.com/brick/date-time/releases/tag/0.6.1) - 2024-03-26

✨ **New features**

- `Year::atMonth()` now accepts a `Month` enum as parameter ([#95](https://github.com/brick/date-time/pull/95))
- `YearMonth::of()` and `YearMonth::withMonth()` now accept a `Month` enum as parameter ([#96](https://github.com/brick/date-time/pull/96))

🔧 **Improvements**

- Narrower Psalm types for `compareTo()`, `__toString()`, `toISOString()`, `jsonSerialize()` methods ([#97](https://github.com/brick/date-time/pull/97))

💩 **Deprecations**

- Passing an `int` to `Year::atMonth()` is now deprecated, pass a `Month` enum instead ([#95](https://github.com/brick/date-time/pull/95))
- Passing an `int` to `YearMonth::of()` and `YearMonth::withMonth()` is now deprecated, pass a `Month` enum instead ([#96](https://github.com/brick/date-time/pull/96))

Thanks to [@gnutix](https://github.com/gnutix)!

## [0.6.0](https://github.com/brick/date-time/releases/tag/0.6.0) - 2023-12-05

💥 **Breaking changes**

- Minimum PHP version is now 8.1
- `DayOfWeek`:
  - `DayOfWeek` is now an `enum`: constants `MONDAY`, `TUESDAY`, etc. are now `DayOfWeek` instances, not integers
  - the `__toString()` method is removed, use `toString()` instead (enums disallow magic methods)
  - the `all()` method does not accept `null` anymore, and defaults to `DayOfWeek::MONDAY`
- `Month`:
  - `Month`is now an `enum`: constants `JANUARY`, `FEBRUARY`, etc. are now `Month` instances, not integers
  - the `__toString()` method is removed, use `toString()` instead (enums disallow magic methods)

💩 **Deprecations**

- `DayOfWeek`:
  - the `of()` method is deprecated, use `DayOfWeek::from()` instead
  - the following methods are deprecated, use enum values instead:
    - `DayOfWeek::monday()` → `DayOfWeek::MONDAY`
    - `DayOfWeek::tuesday()` → `DayOfWeek::TUESDAY`
    - `DayOfWeek::wednesday()` → `DayOfWeek::WEDNESDAY`
    - `DayOfWeek::thursday()` → `DayOfWeek::THURSDAY`
    - `DayOfWeek::friday()` → `DayOfWeek::FRIDAY`
    - `DayOfWeek::saturday()` → `DayOfWeek::SATURDAY`
    - `DayOfWeek::sunday()` → `DayOfWeek::SUNDAY`
  - the `getValue()` method is deprecated, use `$dayOfWeek->value` instead
  - the `is()` method is deprecated, compare values with `$dayOfWeek->value` instead
  - the `isEqualTo()` method is deprecated, use strict equality `===` between `DayOfWeek` instances instead.
- `LocalDate`:
  - `getDay()` is deprecated, use `getDayOfMonth()` instead
  - `getMonth()` is deprecated, use `getMonthValue()` instead (`getMonth()` will be repurposed to return a `Month` instance in a future release)
- `LocalDateTime`:
  - `getDay()` is deprecated, use `getDayOfMonth()` instead
  - `getMonth()` is deprecated, use `getMonthValue()` instead (`getMonth()` will be repurposed to return a `Month` instance in a future release)
- `Month`:
  - the `of()` method is deprecated, use `Month::from()` instead
  - the `getAll()` method is deprecated, use `Month::cases()` instead
  - the `getValue()` method is deprecated, use `$month->value` instead
  - the `is()` method is deprecated, compare values with `$month->value` instead
  - the `isEqualTo()` method is deprecated, use strict equality `===` between `Month` instances instead
- `MonthDay`:
  - `getDay()` is deprecated, use `getDayOfMonth()` instead
  - `getMonth()` is deprecated, use `getMonthValue()` instead (`getMonth()` will be repurposed to return a `Month` instance in a future release)
- `YearMonth`:
  - `getMonth()` is deprecated, use `getMonthValue()` instead (`getMonth()` will be repurposed to return a `Month` instance in a future release)
- `YearWeek`:
  - the `atDay()` method now accepts a `DayOfWeek` instance, passing an integer is deprecated
- `ZonedDateTime`:
  - `getDay()` is deprecated, use `getDayOfMonth()` instead
  - `getMonth()` is deprecated, use `getMonthValue()` instead (`getMonth()` will be repurposed to return a `Month` instance in a future release)

## [0.5.5](https://github.com/brick/date-time/releases/tag/0.5.5) - 2023-10-20

🐛 **Bug fixes**

- `Year::toISOString()` / `__toString()` did not respect ISO 8601; years with less than 4 digits are now left-padded with zeros ([#90](https://github.com/brick/date-time/pull/90))

Thanks to [@andreaswolf](https://github.com/andreaswolf)!

## [0.5.4](https://github.com/brick/date-time/releases/tag/0.5.4) - 2023-10-16

🐛 **Bug fixes**

- `YearMonth::__toString()` would return an invalid string for years `< 1000` ([#87](https://github.com/brick/date-time/pull/87))

✨ **New methods**

- `Year::parse()` and `Year::from()` ([#86](https://github.com/brick/date-time/pull/86))
- `YearWeek::parse()` and `YearWeek::from()` ([#86](https://github.com/brick/date-time/pull/86))
- the following classes now have a `toISOString()` method: ([#87](https://github.com/brick/date-time/pull/87))
  - `Duration`
  - `Instant`
  - `Interval`
  - `LocalDate`
  - `LocalDateRange`
  - `LocalDateTime`
  - `LocalTime`
  - `MonthDay`
  - `Period`
  - `Year`
  - `YearMonth`
  - `YearMonthRange`
  - `YearWeek`
  - `ZonedDateTime`

The `toISOString()` methods return the same result as `__toString()`, but are better suited for the nullsafe operator:

```php
$date?->toISOString();
```

⚡️ **Performance optimizations**

- Most `__toString()` methods got a small performance boost ([#85](https://github.com/brick/date-time/pull/85))

Thanks to [@gnutix](https://github.com/gnutix)!

## [0.5.3](https://github.com/brick/date-time/releases/tag/0.5.3) - 2023-09-27

✨ **New methods**

- `Instant::getIntervalTo()` ([#81](https://github.com/brick/date-time/pull/81))
- `ZonedDateTime::getIntervalTo()` ([#81](https://github.com/brick/date-time/pull/81))

Thanks to [@solodkiy](https://github.com/solodkiy)!

## [0.5.2](https://github.com/brick/date-time/releases/tag/0.5.2) - 2023-09-17

⚡️ **Performance optimizations**

- These methods got a small performance boost: `Local(Date|Time|DateTime)` `minOf()`/`maxOf()` ([#76](https://github.com/brick/date-time/pull/76))
- Static objects returned by factory methods are now cached ([#77](https://github.com/brick/date-time/pull/77))
- The special case `LocalDate::plusDays(1)` is now much faster ([#79](https://github.com/brick/date-time/pull/79))

Thanks to [@gnutix](https://github.com/gnutix) and [@BastienClement](https://github.com/BastienClement)!

## [0.5.1](https://github.com/brick/date-time/releases/tag/0.5.1) - 2023-08-01

✨ **New methods**

- `ZonedDateTime::getDurationTo()` ([#71](https://github.com/brick/date-time/pull/71)) ([@solodkiy](https://github.com/solodkiy))

## [0.5.0](https://github.com/brick/date-time/releases/tag/0.5.0) - 2023-06-25

💥 **Breaking changes**

- Deprecated `Interval` constructor is now `private`; please use `Interval::of()` instead
- The following deprecated methods have been removed, please use the new names:
  
  | Class Name       | Old Method Name         | New Method Name               |
  |------------------|-------------------------|-------------------------------|
  | `LocalDate`      | `fromDateTime()`        | `fromNativeDateTime()`        |
  | `LocalDate`      | `toDateTime()`          | `toNativeDateTime()`          |
  | `LocalDate`      | `toDateTimeImmutable()` | `toNativeDateTimeImmutable()` |
  | `LocalDateRange` | `toDatePeriod()`        | `toNativeDatePeriod()`        |
  | `LocalDateTime`  | `fromDateTime()`        | `fromNativeDateTime()`        |
  | `LocalDateTime`  | `toDateTime()`          | `toNativeDateTime()`          |
  | `LocalDateTime`  | `toDateTimeImmutable()` | `toNativeDateTimeImmutable()` |
  | `LocalTime`      | `fromDateTime()`        | `fromNativeDateTime()`        |
  | `LocalTime`      | `toDateTime()`          | `toNativeDateTime()`          |
  | `LocalTime`      | `toDateTimeImmutable()` | `toNativeDateTimeImmutable()` |
  | `Period`         | `fromDateInterval()`    | `fromNativeDateInterval()`    |
  | `Period`         | `toDateInterval()`      | `toNativeDateInterval()`      |
  | `TimeZone`       | `fromDateTimeZone()`    | `fromNativeDateTimeZone()`    |
  | `TimeZone`       | `toDateTimeZone()`      | `toNativeDateTimeZone()`      |
  | `ZonedDateTime`  | `fromDateTime()`        | `fromNativeDateTime()`        |
  | `ZonedDateTime`  | `toDateTime()`          | `toNativeDateTime()`          |
  | `ZonedDateTime`  | `toDateTimeImmutable()` | `toNativeDateTimeImmutable()` |

## [0.4.3](https://github.com/brick/date-time/releases/tag/0.4.3) - 2023-06-20

🔧 **Improvements**

- `TimeZoneOffset::parse()` return type has been narrowed to `TimeZoneOffset`
- Support for seconds in `TimeZoneOffset` has been added back ([#60](https://github.com/brick/date-time/pull/60)) (@jiripudil)

🐛 **Bug fixes**

- Old date/times could fail to be parsed by `ZonedDateTime` due to sub-minute timezone offsets ([#44](https://github.com/brick/date-time/pull/44))

## [0.4.2](https://github.com/brick/date-time/releases/tag/0.4.2) - 2023-05-19

✨ **New methods**

- `Duration::isGreaterThanOrEqualTo()` ([#50](https://github.com/brick/date-time/pull/50))
- `Duration::isLessThanOrEqualTo()` ([#50](https://github.com/brick/date-time/pull/50))
- `Interval::of()` ([#64](https://github.com/brick/date-time/pull/64))
- `Interval::contains()` ([#64](https://github.com/brick/date-time/pull/64))
- `Interval::intersectsWith()` ([#64](https://github.com/brick/date-time/pull/64))
- `Interval::getIntersectionWith()` ([#64](https://github.com/brick/date-time/pull/64))
- `Interval::isEqualTo()` ([#64](https://github.com/brick/date-time/pull/64))

💩 **Deprecations**

- `Interval` constructor is deprecated in favour of `Interval::of()` ([#64](https://github.com/brick/date-time/pull/64))

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

