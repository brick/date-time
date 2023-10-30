<?php

declare(strict_types=1);

namespace Brick\DateTime\Formatter;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\ZonedDateTime;
use DateTimeZone;
use IntlDateFormatter;
use IntlDatePatternGenerator;

use function array_keys;
use function extension_loaded;
use function get_class;
use function in_array;
use function sprintf;
use function str_split;

/**
 * Formats the value using the Intl extension.
 */
final class IntlFormatter implements DateTimeFormatter
{
    public const FULL = IntlDateFormatter::FULL;
    public const LONG = IntlDateFormatter::LONG;
    public const MEDIUM = IntlDateFormatter::MEDIUM;
    public const SHORT = IntlDateFormatter::SHORT;

    private string $locale;

    private int $dateFormat;

    private int $timeFormat;

    private string $pattern;

    private function __construct(string $locale, int $dateFormat, int $timeFormat, string $pattern)
    {
        $this->locale = $locale;
        $this->dateFormat = $dateFormat;
        $this->timeFormat = $timeFormat;
        $this->pattern = $pattern;

        if (! extension_loaded('intl')) {
            throw new DateTimeFormatException('IntlFormatter requires ext-intl to be installed and enabled.');
        }
    }

    /**
     * Returns a formatter of given type for a date value.
     */
    public static function ofDate(string $locale, int $format): self
    {
        return new self($locale, $format, IntlDateFormatter::NONE, '');
    }

    /**
     * Returns a formatter of given type for a time value.
     */
    public static function ofTime(string $locale, int $format): self
    {
        return new self($locale, IntlDateFormatter::NONE, $format, '');
    }

    /**
     * Returns a formatter of given type for a date-time value.
     */
    public static function ofDateTime(string $locale, int $dateFormat, int $timeFormat): self
    {
        return new self($locale, $dateFormat, $timeFormat, '');
    }

    /**
     * Returns a formatter with given ICU SimpleFormat pattern.
     */
    public static function ofPattern(string $locale, string $pattern): self
    {
        return new self($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $pattern);
    }

    /**
     * Returns a formatter with a pattern that best matches given skeleton.
     */
    public static function ofSkeleton(string $locale, string $skeleton): self
    {
        $generator = new IntlDatePatternGenerator($locale);
        $pattern = $generator->getBestPattern($skeleton);

        if ($pattern === false) {
            throw new DateTimeFormatException('Failed to resolve the best formatting pattern for given locale and skeleton.');
        }

        return self::ofPattern($locale, $pattern);
    }

    public function format(DateTimeFormatContext $context): string
    {
        $value = $context->getValue();

        if ($this->dateFormat !== IntlDateFormatter::NONE && $value instanceof LocalTime) {
            throw new DateTimeFormatException('IntlFormatter with a date part cannot be used to format Brick\DateTime\LocalTime.');
        }

        if ($this->timeFormat !== IntlDateFormatter::NONE && $value instanceof LocalDate) {
            throw new DateTimeFormatException('IntlFormatter with a time part cannot be used to format Brick\DateTime\LocalDate.');
        }

        if (($this->timeFormat === self::FULL || $this->timeFormat === self::LONG) && ! ($value instanceof ZonedDateTime)) {
            throw new DateTimeFormatException(sprintf('IntlFormatter with a long or full time part cannot be used to format %s.', get_class($value)));
        }

        if ($this->pattern !== '') {
            self::checkPattern($this->pattern, $value);
        }

        $timeZone = $value instanceof ZonedDateTime ? $value->getTimeZone()->toNativeDateTimeZone() : new DateTimeZone('UTC');
        $formatter = new IntlDateFormatter($this->locale, $this->dateFormat, $this->timeFormat, $timeZone, null, $this->pattern);

        return $formatter->format($value->toNativeDateTimeImmutable());
    }

    /**
     * @param LocalDate|LocalDateTime|LocalTime|ZonedDateTime $value
     */
    private static function checkPattern(string $pattern, $value): void
    {
        $supportedTypesMap = [
            LocalDate::class => true,
            LocalDateTime::class => true,
            LocalTime::class => true,
            ZonedDateTime::class => true,
        ];

        $inString = false;
        foreach (str_split($pattern) as $character) {
            if ($character === '\'') {
                if ($inString) {
                    $inString = false;

                    continue;
                }

                $inString = true;

                continue;
            }

            if ($inString) {
                continue;
            }

            if (in_array($character, ['G', 'y', 'Y', 'u', 'U', 'r', 'Q', 'q', 'M', 'L', 'w', 'W', 'd', 'D', 'F', 'g', 'E', 'e', 'c'], true)) {
                $supportedTypesMap[LocalTime::class] = false;
            }

            if (in_array($character, ['a', 'h', 'H', 'k', 'K', 'm', 's', 'S', 'A'], true)) {
                $supportedTypesMap[LocalDate::class] = false;
            }

            if (in_array($character, ['z', 'Z', 'O', 'v', 'V', 'X', 'x'], true)) {
                $supportedTypesMap[LocalDate::class] = false;
                $supportedTypesMap[LocalDateTime::class] = false;
                $supportedTypesMap[LocalTime::class] = false;
            }
        }

        $supportedTypes = array_keys($supportedTypesMap, true, true);
        foreach ($supportedTypes as $supportedType) {
            if ($value instanceof $supportedType) {
                return;
            }
        }

        throw new DateTimeFormatException(sprintf("IntlFormatter with pattern '%s' is incompatible with type %s.", $pattern, get_class($value)));
    }
}
