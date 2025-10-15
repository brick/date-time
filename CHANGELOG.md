# Changelog

## [103.1.0](https://github.com/solodkiy/brick-date-time/releases/tag/103.1.0)
* Replaces [brick/date-time 0.6.5](https://github.com/brick/date-time/releases/tag/0.6.5)

## [103.0.0](https://github.com/solodkiy/brick-date-time/releases/tag/103.0.0)
* Replaces [brick/date-time 0.6.4](https://github.com/brick/date-time/releases/tag/0.6.4)
* Removed deprecated methods `UtcDateTime::fromDateTime()`, `ZonedDateTime::toPhpFormat()`
 
Be aware of [breaking changes](https://github.com/brick/date-time/releases/tag/0.6.0)

## [102.1.0](https://github.com/solodkiy/brick-date-time/releases/tag/102.1.0)
* Replaces [brick/date-time 0.5.5](https://github.com/brick/date-time/releases/tag/0.5.5)

## [102.0.0](https://github.com/solodkiy/brick-date-time/releases/tag/102.0.0)
* Replaces [brick/date-time 0.5.1](https://github.com/brick/date-time/releases/tag/0.5.1)

Be aware of [breaking changes](https://github.com/brick/date-time/releases/tag/0.5.0)

## [101.2.0](https://github.com/solodkiy/brick-date-time/releases/tag/101.2.0)
* Replaces [brick/date-time 0.4.3](https://github.com/brick/date-time/releases/tag/0.4.3)

## [101.1.0](https://github.com/solodkiy/brick-date-time/releases/tag/101.1.0)
* Replaces [brick/date-time 0.4.1](https://github.com/brick/date-time/releases/tag/0.4.1)
* Deprecate `ZonedDateTime::toPhpFormat()`. Use `ZonedDateTime::toNatvieFormat()` instead

## [101.0.0](https://github.com/solodkiy/brick-date-time/releases/tag/101.0.0)
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
