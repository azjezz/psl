<?php

declare(strict_types=1);

namespace Psl\DateTime;

use Psl\Comparison;

/**
 * @require-implements TemporalInterface
 */
trait TemporalConvenienceMethodsTrait
{
    /**
     * Compare this temporal object to the given one.
     *
     * @param TemporalInterface $other
     *
     * @mutation-free
     */
    public function compare(mixed $other): Comparison\Order
    {
        $a = $this->getTimestamp()->toRaw();
        $b = $other->getTimestamp()->toRaw();

        return Comparison\Order::from($a[0] !== $b[0] ? $a[0] <=> $b[0] : $a[1] <=> $b[1]);
    }

    /**
     * Checks if this temporal object represents the same time as the given one.
     *
     * @param TemporalInterface $other
     *
     * @mutation-free
     */
    public function equals(mixed $other): bool
    {
        return $this->isAtTheSameTime($other);
    }

    /**
     * Checks if this temporal object represents the same time as the given one.
     *
     * Note: this method is an alias for {@see TemporalInterface::equals()}.
     *
     * @mutation-free
     */
    public function isAtTheSameTime(TemporalInterface $other): bool
    {
        return $this->compare($other) === Comparison\Order::Equal;
    }

    /**
     * Checks if this temporal object is before the given one.
     *
     * @mutation-free
     */
    public function isBefore(TemporalInterface $other): bool
    {
        return $this->compare($other) < Comparison\Order::Less;
    }

    /**
     * Checks if this temporal object is before or at the same time as the given one.
     *
     * @mutation-free
     */
    public function isBeforeOrAtTheSameTime(TemporalInterface $other): bool
    {
        return $this->compare($other) !== Comparison\Order::Greater;
    }

    /**
     * Checks if this temporal object is after the given one.
     *
     * @mutation-free
     */
    public function isAfter(TemporalInterface $other): bool
    {
        return $this->compare($other) === Comparison\Order::Greater;
    }

    /**
     * Checks if this temporal object is between the given times (inclusive).
     *
     * @mutation-free
     */
    public function isAfterOrAtTheSameTime(TemporalInterface $other): bool
    {
        return $this->compare($other) !== Comparison\Order::Less;
    }

    /**
     * Checks if this temporal object is between the given times (exclusive).
     *
     * @mutation-free
     */
    public function isBetweenTimeInclusive(TemporalInterface $a, TemporalInterface $b): bool
    {
        $ca = $this->compare($a);
        $cb = $this->compare($b);

        return $ca === Comparison\Order::Equal || $ca !== $cb;
    }

    /**
     * {@inheritDoc}
     *
     * @mutation-free
     */
    public function isBetweenTimeExclusive(TemporalInterface $a, TemporalInterface $b): bool
    {
        $ca = $this->compare($a);
        $cb = $this->compare($b);

        return $ca !== Comparison\Order::Equal && $cb !== Comparison\Order::Equal && $ca !== $cb;
    }

    /**
     * Adds the specified hours to this temporal object, returning a new instance with the added hours.
     *
     * @throws Exception\UnderflowException If adding the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the hours results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusHours(int $hours): static
    {
        return $this->plus(Duration::hours($hours));
    }

    /**
     * Adds the specified minutes to this temporal object, returning a new instance with the added minutes.
     *
     * @throws Exception\UnderflowException If adding the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the minutes results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusMinutes(int $minutes): static
    {
        return $this->plus(Duration::minutes($minutes));
    }

    /**
     * Adds the specified seconds to this temporal object, returning a new instance with the added seconds.
     *
     * @throws Exception\UnderflowException If adding the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the seconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusSeconds(int $seconds): static
    {
        return $this->plus(Duration::seconds($seconds));
    }

    /**
     * Adds the specified nanoseconds to this temporal object, returning a new instance with the added nanoseconds.
     *
     * @throws Exception\UnderflowException If adding the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the nanoseconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusNanoseconds(int $nanoseconds): static
    {
        return $this->plus(Duration::nanoseconds($nanoseconds));
    }

    /**
     * Subtracts the specified hours from this temporal object, returning a new instance with the subtracted hours.
     *
     * @throws Exception\UnderflowException If subtracting the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the hours results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusHours(int $hours): static
    {
        return $this->minus(Duration::hours($hours));
    }

    /**
     * Subtracts the specified minutes from this temporal object, returning a new instance with the subtracted minutes.
     *
     * @throws Exception\UnderflowException If subtracting the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the minutes results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusMinutes(int $minutes): static
    {
        return $this->minus(Duration::minutes($minutes));
    }

    /**
     * Subtracts the specified seconds from this temporal object, returning a new instance with the subtracted seconds.
     *
     * @throws Exception\UnderflowException If subtracting the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the seconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusSeconds(int $seconds): static
    {
        return $this->minus(Duration::seconds($seconds));
    }

    /**
     * Subtracts the specified nanoseconds from this temporal object, returning a new instance with the subtracted nanoseconds.
     *
     * @throws Exception\UnderflowException If subtracting the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the nanoseconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusNanoseconds(int $nanoseconds): static
    {
        return $this->minus(Duration::nanoseconds($nanoseconds));
    }

    /**
     * Calculates the duration between this temporal object and the given one.
     *
     * @param TemporalInterface $other The temporal object to calculate the duration to.
     *
     * @return Duration The duration between the two temporal objects.
     *
     * @mutation-free
     */
    public function since(TemporalInterface $other): Duration
    {
        $a = $this->getTimestamp()->toRaw();
        $b = $other->getTimestamp()->toRaw();

        return Duration::fromParts(0, 0, $a[0] - $b[0], $a[1] - $b[1]);
    }

    /**
     * Converts the current temporal object to a new {@see DateTimeInterface} instance in a different timezone.
     *
     * @param Timezone $timezone The target timezone for the conversion.
     *
     * @mutation-free
     */
    public function convertToTimezone(Timezone $timezone): DateTimeInterface
    {
        return DateTime::fromTimestamp($this->getTimestamp(), $timezone);
    }
}
