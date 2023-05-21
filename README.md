Brick\DateTime
==============

<img src="https://raw.githubusercontent.com/brick/brick/master/logo.png" alt="" align="left" height="64">

A powerful set of immutable classes to work with dates and times.

[![Build Status](https://github.com/brick/date-time/workflows/CI/badge.svg)](https://github.com/brick/date-time/actions)
[![Coverage Status](https://coveralls.io/repos/github/brick/date-time/badge.svg?branch=master)](https://coveralls.io/github/brick/date-time?branch=master)
[![Latest Stable Version](https://poser.pugx.org/brick/date-time/v/stable)](https://packagist.org/packages/brick/date-time)
[![Total Downloads](https://poser.pugx.org/brick/date-time/downloads)](https://packagist.org/packages/brick/date-time)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](http://opensource.org/licenses/MIT)

Introduction
------------

This library builds an extensive API on top of the native PHP date-time classes, and adds missing concepts such as `LocalDate`, `LocalTime`, `YearMonth`, `MonthDay`, etc.

The classes follow the [ISO 8601](http://en.wikipedia.org/wiki/ISO_8601) standard for representing date and time concepts.

This component follows an important part of the JSR 310 (Date and Time API) specification from Java.
Don't expect an exact match of class and method names though, as a number of differences exist for technical or practical reasons.

All the classes are immutable, they can be safely passed around without being affected.

Installation
------------

This library is installable via [Composer](https://getcomposer.org/):

```bash
composer require brick/date-time
```

Requirements
------------

This library requires PHP 7.4 or later.

Project status & release process
--------------------------------

While this library is still under development, it is well tested and should be stable enough to use in production environments.

The current releases are numbered `0.x.y`. When a non-breaking change is introduced (adding new methods, optimizing existing code, etc.), `y` is incremented.

**When a breaking change is introduced, a new `0.x` version cycle is always started.**

It is therefore safe to lock your project to a given release cycle, such as `0.5.*`.

If you need to upgrade to a newer release cycle, check the [release history](https://github.com/brick/date-time/releases) for a list of changes introduced by each further `0.x.0` version.

Overview
--------

### Main classes

The following classes/enums represent the date-time concepts:

- `DayOfWeek`: a day-of-week such as Monday (`enum`)
- `Duration`: a duration measured in seconds and nanoseconds
- `Instant`: a point in time, with a nanosecond precision
- `Interval`: a period of time between two instants
- `LocalDate`: an isolated date such as `2014-08-31`
- `LocalDateRange`: an inclusive range of local dates, such as `2014-01-01/2014-12-31`
- `LocalDateTime`: a date-time without a time-zone, such as `2014-08-31T10:15:30`
- `LocalTime`: an isolated time such as `10:15:30`
- `Month`: a month-of-year such as January (`enum`)
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

- `SystemClock` returns the system time; it's the default clock
- `FixedClock`: returns a pre-configured time
- `OffsetClock`: adds an offset to another clock
- `ScaleClock`: makes another clock fast forward by a scale factor

These classes belong to the `Brick\DateTime\Clock` namespace.

In your application, you will most likely never touch the defaults, and always use the default clock:

```php
use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;

echo LocalDate::now(TimeZone::utc()); // 2017-10-04
```

In your tests however, you might need to set the current time to test your application in known conditions. To do this, you can either explicitly pass a `Clock` instance to  `now()` methods:

```php
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;

$clock = new FixedClock(Instant::of(1000000000));
echo LocalDate::now(TimeZone::utc(), $clock); // 2001-09-09
```

Or you can change the *default* clock for all date-time classes. All methods such as `now()`, unless provided with an explicit Clock, will use the default clock you provide:

```php
use Brick\DateTime\Clock\FixedClock;
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;
use Brick\DateTime\LocalDate;
use Brick\DateTime\TimeZone;

DefaultClock::set(new FixedClock(Instant::of(1000000000)));
echo LocalDate::now(TimeZone::utc()); // 2001-09-09

DefaultClock::reset(); // do not forget to reset the clock to the system clock!
```

There are also useful shortcut methods to use clocks in your tests, inspired by [timecop](https://github.com/travisjeffery/timecop):

- `freeze()` freezes time to a specific point in time
- `travel()` travels to a specific point in time, but allows time to continue moving forward from there
- `scale()` makes time move at a given pace

#### Freeze the time to a specific point

```php
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;

DefaultClock::freeze(Instant::of(2000000000));

$a = Instant::now(); sleep(1);
$b = Instant::now();

echo $a, PHP_EOL; // 2033-05-18T03:33:20Z
echo $b, PHP_EOL; // 2033-05-18T03:33:20Z

DefaultClock::reset();
```

#### Travel to a specific point in time

```php
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;

DefaultClock::travel(Instant::of(2000000000));
$a = Instant::now(); sleep(1);
$b = Instant::now();

echo $a, PHP_EOL; // 2033-05-18T03:33:20.000342Z
echo $b, PHP_EOL; // 2033-05-18T03:33:21.000606Z

DefaultClock::reset();
```

#### Make time move at a given pace

```php
use Brick\DateTime\DefaultClock;
use Brick\DateTime\Instant;

DefaultClock::travel(Instant::of(2000000000));
DefaultClock::scale(60); // 1 second becomes 60 seconds

$a = Instant::now(); sleep(1);
$b = Instant::now();

echo $a, PHP_EOL; // 2033-05-18T03:33:20.00188Z
echo $b, PHP_EOL; // 2033-05-18T03:34:20.06632Z

DefaultClock::reset();
```

As you can see, you can even combine `travel()` and `scale()` methods.

Be very careful to **`reset()` the DefaultClock after each of your tests!** If you're using PHPUnit, a good place to do this is in the `tearDown()` method.

### Exceptions

The following exceptions can be thrown:

- `Brick\DateTime\DateTimeException` when an illegal operation is performed
- `Brick\DateTime\Parser\DateTimeParseException` when `parse()`ing an invalid string representation

### Doctrine mappings

You can use `brick/date-time` types in your Doctrine entities using the [brick/date-time-doctrine](https://github.com/brick/date-time-doctrine) package.

Contributing
--------

### Coding Style

Install Easy Coding Standard tool in its own folder

```sh
composer install --working-dir=tools/ecs
```

Run coding style analysis checks
```sh
./tools/ecs/vendor/bin/ecs check --config tools/ecs/ecs.php
```

Or fix issues found directly
```sh
./tools/ecs/vendor/bin/ecs check --config tools/ecs/ecs.php --fix
```
