Brick\DateTime
==============

A powerful set of immutable classes to work with dates and times.

[![Build Status](https://secure.travis-ci.org/brick/date-time.png)](http://travis-ci.org/brick/date-time)
[![Coverage Status](https://coveralls.io/repos/brick/date-time/badge.png)](https://coveralls.io/r/brick/date-time)

Introduction
------------

Although PHP has a native `DateTime` class, it lacks many simple concepts like `LocalDate`, `LocalTime`, etc.

The classes follow the ISO 8601 standard for representing date and time objects.
They offer up to a nanosecond precision, where the native API has a 1 second precision.

The date-time API also offers a configurable `Clock` that you can set in your automated tests.

This component follows an important part of the JSR 310 (Date and Time API) specification from Java.
Don't expect an exact match of class and method names though, as a number of differences exist for technical or practical reasons.

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
Earlier versions of PHP 5.5 will fail due to [this bug](https://bugs.php.net/bug.php?id=45528).
