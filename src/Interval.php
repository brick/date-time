<?php

declare(strict_types=1);

namespace Brick\DateTime;

use JsonSerializable;
use Stringable;

/**
 * Represents a period of time between two instants, inclusive of the start instant and exclusive of the end.
 * The end instant is always greater than or equal to the start instant.
 *
 * This class is immutable.
 */
final class Interval implements JsonSerializable, Stringable
{
    /**
     * @param Instant $start The start instant, inclusive.
     * @param Instant $end   The end instant, exclusive, validated as not before the start instant.
     */
    private function __construct(
        private readonly Instant $start,
        private readonly Instant $end,
    ) {
    }

    /**
     * @param Instant $startInclusive The start instant, inclusive.
     * @param Instant $endExclusive   The end instant, exclusive.
     *
     * @throws DateTimeException If the end instant is before the start instant.
     */
    public static function of(Instant $startInclusive, Instant $endExclusive): Interval
    {
        if ($endExclusive->isBefore($startInclusive)) {
            throw new DateTimeException('The end instant must not be before the start instant.');
        }

        return new Interval($startInclusive, $endExclusive);
    }

    /**
     * Returns the start instant, inclusive, of this Interval.
     */
    public function getStart(): Instant
    {
        return $this->start;
    }

    /**
     * Returns the end instant, exclusive, of this Interval.
     */
    public function getEnd(): Instant
    {
        return $this->end;
    }

    /**
     * Returns a copy of this Interval with the start instant altered.
     *
     * @throws DateTimeException If the given start instant is after the current end instant.
     */
    public function withStart(Instant $start): Interval
    {
        return Interval::of($start, $this->end);
    }

    /**
     * Returns a copy of this Interval with the end instant altered.
     *
     * @throws DateTimeException If the given end instant is before the current start instant.
     */
    public function withEnd(Instant $end): Interval
    {
        return Interval::of($this->start, $end);
    }

    /**
     * Returns a Duration representing the time elapsed in this Interval.
     */
    public function getDuration(): Duration
    {
        return Duration::between($this->start, $this->end);
    }

    /**
     * Returns whether this Interval contains the given Instant.
     */
    public function contains(Instant $instant): bool
    {
        return $instant->isAfterOrEqualTo($this->start)
            && $instant->isBefore($this->end);
    }

    /**
     * Returns whether this Interval intersects with the given one.
     */
    public function intersectsWith(Interval $that): bool
    {
        [$prev, $next] = $this->start->isBefore($that->start)
            ? [$this, $that]
            : [$that, $this];

        return $next->start->isBefore($prev->end);
    }

    /**
     * Returns an Interval which is an intersection of this one with the given one.
     *
     * @throws DateTimeException If the Intervals do not intersect.
     */
    public function getIntersectionWith(Interval $that): Interval
    {
        if (! $this->intersectsWith($that)) {
            throw new DateTimeException('Intervals "' . $this . '" and "' . $that . '" do not intersect.');
        }

        $latestStart = $this->start->isAfter($that->start) ? $this->start : $that->start;
        $earliestEnd = $this->end->isBefore($that->end) ? $this->end : $that->end;

        return new Interval($latestStart, $earliestEnd);
    }

    public function isEqualTo(Interval $that): bool
    {
        return $this->start->isEqualTo($that->start)
            && $this->end->isEqualTo($that->end);
    }

    /**
     * Serializes as a string using {@see Interval::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function jsonSerialize(): string
    {
        return $this->toISOString();
    }

    /**
     * Returns the ISO 8601 representation of this interval.
     *
     * @psalm-return non-empty-string
     */
    public function toISOString(): string
    {
        return $this->start . '/' . $this->end;
    }

    /**
     * {@see Interval::toISOString()}.
     *
     * @psalm-return non-empty-string
     */
    public function __toString(): string
    {
        return $this->toISOString();
    }
}
