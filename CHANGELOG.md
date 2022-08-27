# Changelog

## [101.1.0](https://github.com/solodkiy/brick-date-time/releases/tag/100.1.0)
* Replaces [brick/date-time 0.4.1](https://github.com/brick/date-time/releases/tag/0.4.1)
* Deprecate `ZonedDateTime::toPhpFormat()`. Use `ZonedDateTime::toNatvieFormat()` instead

## [101.0.0](https://github.com/solodkiy/brick-date-time/releases/tag/100.1.0)
Replaces [brick/date-time 0.4.0](https://github.com/brick/date-time/releases/tag/0.4.0)

## [100.1.0](https://github.com/solodkiy/brick-date-time/releases/tag/100.1.0)

**New Methods:**
* `Instant::toUtcDateTime()`
* `LocalDateTime::fromSqlFormat()`
* `LocalDateTime::toSqlFormat()`
* `ZonedDateTime::fromSqlFormat()`
* `ZonedDateTime::toSqlFormat()`
* `ZonedDateTime::toPhpFormat()`
* `ZonedDateTime::toUtcSqlFormat()`

## [100.0.0](https://github.com/solodkiy/brick-date-time/releases/tag/100.0.0) 

* Replaces [brick/date-time 0.3.2](https://github.com/brick/date-time/releases/tag/0.3.2)
* Provided new `UtcDateTime` class and `ZonedDateTime::toUtcDateTime()` method
