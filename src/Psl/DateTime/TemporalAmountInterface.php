<?php

declare(strict_types=1);

namespace Psl\DateTime;

use DateInterval;
use JsonSerializable;
use Psl\Comparison;
use Psl\Interoperability;
use Stringable;

/**
 * Represents an amount of time that can be added to or subtracted from a temporal object.
 *
 * This is the common interface for {@see Duration} and {@see Period}, providing
 * shared state-checking methods, ISO 8601 representation, and the ability to apply the
 * amount to a temporal object.
 *
 * @inheritors Duration|Period
 *
 * @extends Comparison\Equable<TemporalAmountInterface>
 * @implements Interoperability\ToStdlib<DateInterval>
 */
interface TemporalAmountInterface extends Comparison\Equable, JsonSerializable, Stringable, Interoperability\ToStdlib
{
    /**
     * Checks if this amount represents zero.
     *
     * @psalm-mutation-free
     */
    public function isZero(): bool;

    /**
     * Checks if this amount is positive.
     *
     * Returns true if any component is positive. Returns false if all parts are zero.
     *
     * @psalm-mutation-free
     */
    public function isPositive(): bool;

    /**
     * Checks if this amount is negative.
     *
     * Returns true if any component is negative. Returns false if all parts are zero.
     *
     * @psalm-mutation-free
     */
    public function isNegative(): bool;

    /**
     * Returns an ISO 8601 duration string representing this amount.
     *
     * @throws Exception\InvalidArgumentException If the amount cannot be represented in ISO 8601 format
     *                                            (e.g. mixed-sign components).
     *
     * @psalm-mutation-free
     */
    public function toIso8601(): string;

    /**
     * Adds this amount to the given temporal, returning the result.
     *
     * For {@see Duration}, this works with any {@see TemporalInterface} (including {@see Timestamp}).
     * For {@see Period}, the temporal must be a {@see DateTimeInterface} because calendar-based
     * arithmetic requires a date context.
     *
     * @throws Exception\InvalidArgumentException If this amount cannot be added to the given temporal type.
     * @throws Exception\UnderflowException If the operation results in an arithmetic underflow.
     * @throws Exception\OverflowException If the operation results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function addTo(TemporalInterface $temporal): TemporalInterface;

    /**
     * Subtracts this amount from the given temporal, returning the result.
     *
     * For {@see Duration}, this works with any {@see TemporalInterface} (including {@see Timestamp}).
     * For {@see Period}, the temporal must be a {@see DateTimeInterface} because calendar-based
     * arithmetic requires a date context.
     *
     * @throws Exception\InvalidArgumentException If this amount cannot be subtracted from the given temporal type.
     * @throws Exception\UnderflowException If the operation results in an arithmetic underflow.
     * @throws Exception\OverflowException If the operation results in an arithmetic overflow.
     *
     * @psalm-mutation-free
     */
    public function subtractFrom(TemporalInterface $temporal): TemporalInterface;
}
