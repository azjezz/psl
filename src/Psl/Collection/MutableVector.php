<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * Represents a write-enabled (mutable) sequence of values, indexed by integers
 * (i.e., a vector).
 *
 * @template Tv
 *
 * @extends ConstVector<Tv>
 * @extends IndexAccess<int, Tv>
 * @extends Collection<Tv>
 */
interface MutableVector extends ConstVector, IndexAccess, Collection
{
    /**
     * Returns a MutableVector containing the values of the current MutableVector that meet a supplied
     * condition.
     *
     * @psalm-param (callable(Tv): bool) $fn
     *
     * @psalm-return MutableVector<Tv>
     */
    public function filter(callable $fn): MutableVector;

    /**
     * Returns a MutableVector containing the values of the current MutableVector that meet a supplied
     * condition applied to its keys and values.
     *
     * @psalm-param (callable(int, Tv): bool) $fn
     *
     * @psalm-return MutableVector<Tv>
     */
    public function filterWithKey(callable $fn): MutableVector;

    /**
     * Returns a MutableVector containing the values of the current
     * MutableVector. Essentially a copy of the current MutableVector.
     *
     * @psalm-return MutableVector<Tv>
     */
    public function values(): MutableVector;

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableVector`.
     *
     * @psalm-return MutableVector<int>
     */
    public function keys(): MutableVector;

    /**
     * Returns a `MutableVector` containing the values after an operation has been
     * applied to each value in the current `MutableVector`.
     *
     * Every value in the current `MutableVector` is affected by a call to `map()`,
     * unlike `filter()` where only values that meet a certain criteria are
     * affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn
     *
     * @psalm-return MutableVector<Tu>
     */
    public function map(callable $fn): MutableVector;

    /**
     * Returns a `MutableVector` containing the values after an operation has been
     * applied to each key and value in the current `MutableVector`.
     *
     * Every key and value in the current `MutableVector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, Tv): Tu) $fn - The callback containing the operation to apply to the
     *              `MutableVector` keys and values.
     *
     * @psalm-return MutableVector<Tu> - a `MutableVector` containing the values after a user-specified
     *           operation on the current Vector's keys and values is applied.
     */
    public function mapWithKey(callable $fn): MutableVector;

    /**
     * Returns a `MutableVector` where each element is a `Pair` that combines the
     * element of the current `MutableVector` and the provided `iterable`.
     *
     * If the number of elements of the `MutableVector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> - The `iterable` to use to combine with the
     *                       elements of this `MutableVector`.
     *
     * @psalm-return MutableVector<Pair<Tv, Tu>> - The `MutableVector` that combines the values of the current
     *           `MutableVector` with the provided `iterable`.
     */
    public function zip(iterable $iterable): MutableVector;

    /**
     * Returns a `MutableVector` containing the first `n` values of the current
     * `MutableVector`.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `MutableVector`
     *
     * @psalm-return MutableVector<Tv> - A `MutableVector` that is a proper subset of the current
     *           `MutableVector` up to `n` elements.
     */
    public function take(int $n): MutableVector;

    /**
     * Returns a `MutableVector` containing the values of the current `MutableVector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return MutableVector<Tv> - A `MutableVector` that is a proper subset of the current
     *           `MutableVector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableVector;

    /**
     * Returns a `MutableVector` containing the values after the `n`-th element of
     * the current `MutableVector`.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `MutableVector`.
     *
     * @psalm-return MutableVector<Tv> - A `MutableVector` that is a proper subset of the current
     *           `MutableVector` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): MutableVector;

    /**
     * Returns a `MutableVector` containing the values of the current `MutableVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `MutableVector`.
     *
     * @psalm-return MutableVector<Tv> - A `MutableVector` that is a proper subset of the current
     *           `MutableVector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableVector;

    /**
     * Returns a subset of the current `MutableVector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableVector` will always be a proper subset of this
     * `MutableVector`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `MutableVector`
     * @psalm-param int $len   - The length of the returned `MutableVector`
     *
     * @psalm-return MutableVector<Tv> - A `MutableVector` that is a proper subset of the current
     *           `MutableVector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): MutableVector;

    /**
     * Returns a `MutableVector` that is the concatenation of the values of the
     * current `MutableVector` and the values of the provided `iterable`.
     *
     * The values of the provided `iterable` is concatenated to the end of the
     * current `MutableVector` to produce the returned `MutableVector`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                       `MutableVector`.
     *
     * @psalm-return MutableVector<Tu> - The concatenated `MutableVector`.
     */
    public function concat(iterable $iterable): MutableVector;

    /**
     * Returns a deep, immutable copy (`ImmVector`) of this `MutableVector`.
     *
     * @psalm-return ImmVector<Tv> - an `ImmVector` that is a deep copy of this `MutableVector`
     */
    public function immutable(): ImmVector;
}
