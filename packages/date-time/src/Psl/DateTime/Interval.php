<?php

declare(strict_types=1);

namespace Psl\DateTime;

use DateMalformedStringException;
use DateTimeImmutable;
use JsonSerializable;
use Override;
use Psl\Comparison;
use Stringable;

use function count;
use function explode;

/**
 * Represents a time range between two points in time.
 *
 * An Interval has a start and end, both of which are {@see TemporalInterface}
 * instances. The start must be before or at the same time as the end.
 *
 * @implements Comparison\Equable<Interval>
 *
 * @immutable
 */
final readonly class Interval implements Comparison\Equable, JsonSerializable, Stringable
{
    /**
     * @pure
     */
    private function __construct(
        private TemporalInterface $start,
        private TemporalInterface $end,
    ) {}

    /**
     * Creates an interval between two points in time.
     *
     * @throws Exception\InvalidArgumentException If start is after end.
     *
     * @pure
     */
    public static function between(TemporalInterface $start, TemporalInterface $end): self
    {
        if ($start->after($end)) {
            throw new Exception\InvalidArgumentException('Interval start must be before or at the same time as end.');
        }

        return new self($start, $end);
    }

    /**
     * Creates an interval from the given start with the specified duration.
     *
     * @throws Exception\InvalidArgumentException If the duration is negative.
     *
     * @pure
     */
    public static function from(TemporalInterface $start, Duration $duration): self
    {
        if ($duration->isNegative()) {
            throw new Exception\InvalidArgumentException('Interval duration must not be negative.');
        }

        return new self($start, $start->plus($duration));
    }

    /**
     * Creates an interval from the given start to the current time.
     */
    public static function since(TemporalInterface $start): self
    {
        return new self($start, Timestamp::now());
    }

    /**
     * Returns the start of this interval.
     *
     * @psalm-mutation-free
     */
    public function getStart(): TemporalInterface
    {
        return $this->start;
    }

    /**
     * Returns the end of this interval.
     *
     * @psalm-mutation-free
     */
    public function getEnd(): TemporalInterface
    {
        return $this->end;
    }

    /**
     * Returns the exact time duration between start and end.
     *
     * @psalm-mutation-free
     */
    public function getDuration(): Duration
    {
        return $this->end->since($this->start);
    }

    /**
     * Checks whether the given point in time falls within this interval (inclusive).
     *
     * @psalm-mutation-free
     */
    public function contains(TemporalInterface $point): bool
    {
        return $point->afterOrAtTheSameTime($this->start) && $point->beforeOrAtTheSameTime($this->end);
    }

    /**
     * Checks whether this interval fully contains another interval.
     *
     * @psalm-mutation-free
     */
    public function containsInterval(self $other): bool
    {
        return $this->start->beforeOrAtTheSameTime($other->start) && $this->end->afterOrAtTheSameTime($other->end);
    }

    /**
     * Checks whether this interval overlaps with another.
     *
     * Two intervals overlap if they share at least one point in time.
     *
     * @psalm-mutation-free
     */
    public function overlaps(self $other): bool
    {
        return $this->start->beforeOrAtTheSameTime($other->end) && $this->end->afterOrAtTheSameTime($other->start);
    }

    /**
     * Returns the intersection of this interval with another, or null if they do not overlap.
     *
     * @psalm-mutation-free
     */
    public function intersection(self $other): null|self
    {
        if (!$this->overlaps($other)) {
            return null;
        }

        $start = $this->start->afterOrAtTheSameTime($other->start) ? $this->start : $other->start;
        $end = $this->end->beforeOrAtTheSameTime($other->end) ? $this->end : $other->end;

        return new self($start, $end);
    }

    /**
     * Returns the gap between this interval and another, or null if they overlap.
     *
     * The gap is the interval between the end of the earlier interval and the
     * start of the later one. If the intervals overlap, there is no gap.
     *
     * @psalm-mutation-free
     */
    public function gap(self $other): null|self
    {
        if ($this->overlaps($other)) {
            return null;
        }

        if ($this->end->beforeOrAtTheSameTime($other->start)) {
            return new self($this->end, $other->start);
        }

        return new self($other->end, $this->start);
    }

    /**
     * Merges this interval with another overlapping interval.
     *
     * Returns a new interval spanning from the earliest start to the latest end.
     *
     * @throws Exception\InvalidArgumentException If the intervals do not overlap.
     *
     * @psalm-mutation-free
     */
    public function merge(self $other): self
    {
        if (!$this->overlaps($other)) {
            throw new Exception\InvalidArgumentException('Cannot merge non-overlapping intervals.');
        }

        $start = $this->start->beforeOrAtTheSameTime($other->start) ? $this->start : $other->start;
        $end = $this->end->afterOrAtTheSameTime($other->end) ? $this->end : $other->end;

        return new self($start, $end);
    }

    /**
     * Returns the interval in ISO 8601 interval format ({@literal start/end}).
     *
     * Both start and end are formatted as RFC 3339 timestamps. UTC is represented as 'Z'.
     *
     * @psalm-mutation-free
     */
    public function toIso8601(): string
    {
        return $this->start->toRfc3339(useZ: true) . '/' . $this->end->toRfc3339(useZ: true);
    }

    /**
     * Parses an ISO 8601 interval string in {@literal start/end} format.
     *
     * Both start and end must be valid ISO 8601/RFC 3339 datetime strings.
     *
     * @throws Exception\InvalidArgumentException If the string is not a valid ISO 8601 interval.
     *
     * @pure
     */
    public static function fromIso8601(string $value): self
    {
        $parts = explode('/', $value);
        if (2 !== count($parts)) {
            throw new Exception\InvalidArgumentException('Invalid ISO 8601 interval format; expected "start/end".');
        }

        try {
            $start = Timestamp::fromStdlib(new DateTimeImmutable($parts[0]));
            $end = Timestamp::fromStdlib(new DateTimeImmutable($parts[1]));
        } catch (DateMalformedStringException|Exception\ExceptionInterface $e) {
            throw new Exception\InvalidArgumentException(
                'Invalid ISO 8601 interval format; ' . $e->getMessage(),
                previous: $e,
            );
        }

        return self::between($start, $end);
    }

    /**
     * Evaluates whether this interval is equivalent to another.
     *
     * Two intervals are equal if their start and end points represent the same moments in time.
     *
     * @param Interval $other
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function equals(mixed $other): bool
    {
        return $this->start->atTheSameTime($other->start) && $this->end->atTheSameTime($other->end);
    }

    /**
     * Returns the interval as a string in the format "start / end" using RFC 3339.
     *
     * @psalm-mutation-free
     */
    public function toString(): string
    {
        return $this->start->toRfc3339() . ' / ' . $this->end->toRfc3339();
    }

    /**
     * @psalm-mutation-free
     */
    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return array{start: mixed, end: mixed}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'start' => $this->start->jsonSerialize(),
            'end' => $this->end->jsonSerialize(),
        ];
    }
}
