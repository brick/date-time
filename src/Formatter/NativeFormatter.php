<?php

declare(strict_types=1);

namespace Brick\DateTime\Formatter;

use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateTime;
use Brick\DateTime\LocalTime;
use Brick\DateTime\ZonedDateTime;

use function array_keys;
use function get_class;
use function in_array;
use function sprintf;
use function str_split;

/**
 * Formats the value using the native DateTime::format() and its format string.
 */
final class NativeFormatter implements DateTimeFormatter
{
    private string $format;

    /** @var list<string> */
    private array $supportedValueTypes;

    private function __construct(string $format)
    {
        $this->format = $format;
        $this->supportedValueTypes = self::getSupportedValueTypes($format);
    }

    public static function of(string $format): self
    {
        return new self($format);
    }

    public function format(DateTimeFormatContext $context): string
    {
        $value = $context->getValue();

        foreach ($this->supportedValueTypes as $supportedValueType) {
            if ($value instanceof $supportedValueType) {
                return $value->toNativeDateTimeImmutable()->format($this->format);
            }
        }

        throw new DateTimeFormatException(sprintf("Formatting pattern '%s' is incompatible with type %s.", $this->format, get_class($value)));
    }

    /**
     * @return list<string>
     */
    private static function getSupportedValueTypes(string $format): array
    {
        $supported = [
            LocalDate::class => true,
            LocalDateTime::class => true,
            LocalTime::class => true,
            ZonedDateTime::class => true,
        ];

        $escaped = false;
        foreach (str_split($format) as $character) {
            if ($character === '\\') {
                $escaped = true;

                continue;
            }

            if ($escaped) {
                $escaped = false;

                continue;
            }

            if (in_array($character, ['d', 'j', 'S', 'D', 'l', 'N', 'w', 'z', 'W', 'o', 'F', 'm', 'M', 'n', 't', 'L', 'Y', 'y'], true)) {
                $supported[LocalTime::class] = false;
            }

            if (in_array($character, ['a', 'A', 'g', 'G', 'h', 'H', 'B', 'i', 's', 'v', 'u'], true)) {
                $supported[LocalDate::class] = false;
            }

            if (in_array($character, ['e', 'T', 'I', 'c', 'r', 'U', 'O', 'P', 'p', 'Z'], true)) {
                $supported[LocalDate::class] = false;
                $supported[LocalDateTime::class] = false;
                $supported[LocalTime::class] = false;
            }
        }

        return array_keys($supported, true, true);
    }
}
