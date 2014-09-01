Brick\DateTime
==============

A powerful set of immutable classes to work with dates and times.

[![Build Status](https://secure.travis-ci.org/brick/date-time.png)](http://travis-ci.org/brick/date-time)
[![Coverage Status](https://coveralls.io/repos/brick/date-time/badge.png)](https://coveralls.io/r/brick/date-time)

Introduction
------------

Although PHP has a native `DateTime` class, it lacks many simple concepts such as `LocalDate`, `LocalTime`, etc.
It is also limited to a 1 second precision, whereas this API offers up to a nanosecond precision.

The classes follow the ISO 8601 standard for representing date and time objects.

This component follows an important part of the JSR 310 (Date and Time API) specification from Java.
Don't expect an exact match of class and method names though, as a number of differences exist for technical or practical reasons.

All the classes are immutable, they can be safely passed around without being affected.

Installation
------------

This library is installable via [Composer](https://getcomposer.org/).
Just define the following requirement in your `composer.json` file:

    {
        "require": {
            "brick/date-time": "dev-master"
        }
    }

Requirements
------------

This library requires PHP 5.5.10 or higher.
Earlier versions of PHP 5.5 will suffer from [this bug](https://bugs.php.net/bug.php?id=45528).

Overview
--------

### Main classes

The following classes represent the date-time concepts:

- `DayOfWeek`: a day-of-week such as Monday
- `Duration`: a duration measured in seconds and nanoseconds
- `Instant`: a point in time, with a nanosecond precision
- `Interval`: a period of time between two instants
- `LocalDate`: an isolated date such as `2014-08-31`
- `LocalDateRange`: an inclusive range of local dates, such as `2014-01-01/2014-12-31`
- `LocalDateTime`: a date-time without a time-zone, such as `2014-08-31T10:15:30`
- `LocalTime`: an isolated time such as `10:15:30`
- `Month`: a month-of-year such as January
- `MonthDay`: a combination of a month and a day, without a year, such as `--12-31`
- `Period`: a date-based amount of time, such as '2 years, 3 months and 4 days'
- `TimeZoneOffset`: an offset-based time-zone, such as `+01:00`
- `TimeZoneRegion`: a region-based time-zone, such as `Europe/London`
- `Year`: a year in the [proleptic calendar](http://en.wikipedia.org/wiki/Proleptic_Gregorian_calendar)
- `YearMonth`: a combination of a year and a month, such as `2014-08`
- `ZonedDateTime`: a date-time with a time-zone, such as `2014-08-31T10:15:30+01:00`.
   This class is conceptually equivalent to the native `DateTime` class

These classes belong to the `Brick\DateTime` namespace.

### Clocks

All objects read the current time from a `Clock` implementation. The following implementations are available:

- `SystemClock`: the default clock, returns the system time
- `FixedClock`: always returns the configured time
- `OffsetClock`: adds an offset to another clock

These classes belong to the `Brick\DateTime\Clock` namespace.

In your application, you will most likely never touch the defaults, and always use the `SystemClock`.
In your tests however, you will probably want to set the time to test your application in known conditions;
in that case, `FixedClock` comes in handy:

    use Brick\DateTime\Instant;
    use Brick\DateTime\Clock\Clock;
    use Brick\DateTime\Clock\FixedClock;

    Clock::setDefault(new FixedClock(Instant::of(1409563222)));

### Exceptions

The following exceptions can be thrown:

- `Brick\DateTime\DateTimeException` when an illegal operation is performed
- `Brick\DateTime\Parser\DateTimeParseException` when `parse()`ing an invalid string representation
