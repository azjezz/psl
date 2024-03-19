<?php

declare(strict_types=1);

namespace Psl\DateTime;

use JsonSerializable;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;

/**
 * Represents a temporal object that can be manipulated and compared.
 *
 * @extends Comparable<TemporalInterface>
 * @extends Equable<TemporalInterface>
 */
interface TemporalInterface extends Comparable, Equable, JsonSerializable
{
    /**
     * Returns the timestamp representation of this temporal object.
     *
     * @mutation-free
     */
    public function getTimestamp(): Timestamp;

    /**
     * Checks if this temporal object represents the same time as the given one.
     *
     * Note: this method is an alias for {@see TemporalInterface::isAtTheSameTime()}.
     *
     * @param TemporalInterface $other
     *
     * @mutation-free
     */
    public function equals(mixed $other): bool;

    /**
     * Checks if this temporal object represents the same time as the given one.
     *
     * @mutation-free
     */
    public function isAtTheSameTime(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is before the given one.
     *
     * @mutation-free
     */
    public function isBefore(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is before or at the same time as the given one.
     *
     * @mutation-free
     */
    public function isBeforeOrAtTheSameTime(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is after the given one.
     *
     * @mutation-free
     */
    public function isAfter(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is after or at the same time as the given one.
     *
     * @mutation-free
     */
    public function isAfterOrAtTheSameTime(TemporalInterface $other): bool;

    /**
     * Checks if this temporal object is between the given times (inclusive).
     *
     * @mutation-free
     */
    public function isBetweenTimeInclusive(TemporalInterface $a, TemporalInterface $b): bool;

    /**
     * Checks if this temporal object is between the given times (exclusive).
     *
     * @mutation-free
     */
    public function isBetweenTimeExclusive(TemporalInterface $a, TemporalInterface $b): bool;

    /**
     * Adds the specified duration to this temporal object, returning a new instance with the added duration.
     *
     * @throws Exception\UnderflowException If adding the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the duration results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plus(Duration $duration): static;

    /**
     * Subtracts the specified duration from this temporal object, returning a new instance with the subtracted duration.
     *
     * @throws Exception\UnderflowException If subtracting the duration results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the duration results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minus(Duration $duration): static;

    /**
     * Adds the specified hours to this temporal object, returning a new instance with the added hours.
     *
     * @throws Exception\UnderflowException If adding the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the hours results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusHours(int $hours): static;

    /**
     * Adds the specified minutes to this temporal object, returning a new instance with the added minutes.
     *
     * @throws Exception\UnderflowException If adding the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the minutes results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusMinutes(int $minutes): static;

    /**
     * Adds the specified seconds to this temporal object, returning a new instance with the added seconds.
     *
     * @throws Exception\UnderflowException If adding the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the seconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusSeconds(int $seconds): static;

    /**
     * Adds the specified nanoseconds to this temporal object, returning a new instance with the added nanoseconds.
     *
     * @throws Exception\UnderflowException If adding the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If adding the nanoseconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function plusNanoseconds(int $nanoseconds): static;

    /**
     * Subtracts the specified hours from this temporal object, returning a new instance with the subtracted hours.
     *
     * @throws Exception\UnderflowException If subtracting the hours results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the hours results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusHours(int $hours): static;

    /**
     * Subtracts the specified minutes from this temporal object, returning a new instance with the subtracted minutes.
     *
     * @throws Exception\UnderflowException If subtracting the minutes results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the minutes results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusMinutes(int $minutes): static;

    /**
     * Subtracts the specified seconds from this temporal object, returning a new instance with the subtracted seconds.
     *
     * @throws Exception\UnderflowException If subtracting the seconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the seconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusSeconds(int $seconds): static;

    /**
     * Subtracts the specified nanoseconds from this temporal object, returning a new instance with the subtracted nanoseconds.
     *
     * @throws Exception\UnderflowException If subtracting the nanoseconds results in an arithmetic underflow.
     * @throws Exception\OverflowException If subtracting the nanoseconds results in an arithmetic overflow.
     *
     * @mutation-free
     */
    public function minusNanoseconds(int $nanoseconds): static;

    /**
     * Calculates the duration between this temporal object and the given one.
     *
     * @param TemporalInterface $other The temporal object to calculate the duration to.
     *
     * @return Duration The duration between the two temporal objects.
     *
     * @mutation-free
     */
    public function since(TemporalInterface $other): Duration;

    /**
     * Converts the current temporal object to a new {@see DateTimeInterface} instance in a different timezone.
     *
     * @param Timezone $timezone The target timezone for the conversion.
     *
     * @mutation-free
     */
    public function convertToTimezone(Timezone $timezone): DateTimeInterface;
}
