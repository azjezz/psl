<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for all Vectors to enable access its values.
 *
 * @template Tv
 *
 * @extends ConstCollection<Tv>
 * @extends ConstIndexAccess<int, Tv>
 * @extends \IteratorAggregate<Tv>
 */
interface ConstVector extends ConstCollection, ConstIndexAccess, \IteratorAggregate
{
    /**
     * Returns a ConstVector containing the values of the current ConstVector that meet a supplied
     * condition.
     *
     * @psalm-param (callable(Tv): bool) $fn
     *
     * @psalm-return ConstVector<Tv>
     */
    public function filter(callable $fn): ConstVector;

    /**
     * Returns a ConstVector containing the values of the current ConstVector that meet a supplied
     * condition applied to its keys and values.
     *
     * @psalm-param (callable(int, Tv): bool) $fn
     *
     * @psalm-return ConstVector<Tv>
     */
    public function filterWithKey(callable $fn): ConstVector;

    /**
     * Returns a ConstVector containing the values of the current
     * ConstVector. Essentially a copy of the current ConstVector.
     *
     * @psalm-return ConstVector<Tv>
     */
    public function values(): ConstVector;

    /**
     * Returns a `ConstVector` containing the keys of the current `ConstVector`.
     *
     * @psalm-return ConstVector<int>
     */
    public function keys(): ConstVector;

    /**
     * Returns a `ConstVector` containing the values after an operation has been
     * applied to each value in the current `ConstVector`.
     *
     * Every value in the current `ConstVector` is affected by a call to `map()`,
     * unlike `filter()` where only values that meet a certain criteria are
     * affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn
     *
     * @psalm-return ConstVector<Tu>
     */
    public function map(callable $fn): ConstVector;

    /**
     * Returns a `ConstVector` containing the values after an operation has been
     * applied to each key and value in the current `ConstVector`.
     *
     * Every key and value in the current `ConstVector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, Tv): Tu) $fn - The callback containing the operation to apply to the
     *              `ConstVector` keys and values.
     *
     * @psalm-return ConstVector<Tu> - a `ConstVector` containing the values after a user-specified
     *           operation on the current Vector's keys and values is applied.
     */
    public function mapWithKey(callable $fn): ConstVector;

    /**
     * Returns a `ConstVector` where each element is a `Pair` that combines the
     * element of the current `ConstVector` and the provided `iterable`.
     *
     * If the number of elements of the `ConstVector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `ConstVector`.
     *
     * @psalm-return ConstVector<Pair<Tv, Tu>> - The `ConstVector` that combines the values of the current
     *           `ConstVector` with the provided `iterable`.
     */
    public function zip(iterable $iterable): ConstVector;

    /**
     * Returns a `ConstVector` containing the first `n` values of the current
     * `ConstVector`.
     *
     * The returned `ConstVector` will always be a proper subset of the current
     * `ConstVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `ConstVector`
     *
     * @psalm-return ConstVector<Tv> - A `ConstVector` that is a proper subset of the current
     *           `ConstVector` up to `n` elements.
     */
    public function take(int $n): ConstVector;

    /**
     * Returns a `ConstVector` containing the values of the current `ConstVector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `ConstVector` will always be a proper subset of the current
     * `ConstVector`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return ConstVector<Tv> - A `ConstVector` that is a proper subset of the current
     *           `ConstVector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): ConstVector;

    /**
     * Returns a `ConstVector` containing the values after the `n`-th element of
     * the current `ConstVector`.
     *
     * The returned `ConstVector` will always be a proper subset of the current
     * `ConstVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `ConstVector`.
     *
     * @psalm-return ConstVector<Tv> - A `ConstVector` that is a proper subset of the current
     *           `ConstVector` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): ConstVector;

    /**
     * Returns a `ConstVector` containing the values of the current `ConstVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `ConstVector` will always be a proper subset of the current
     * `ConstVector`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `ConstVector`.
     *
     * @psalm-return ConstVector<Tv> - A `ConstVector` that is a proper subset of the current
     *           `ConstVector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): ConstVector;

    /**
     * Returns a subset of the current `ConstVector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `ConstVector` will always be a proper subset of this
     * `ConstVector`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `ConstVector`
     * @psalm-param int $len   - The length of the returned `ConstVector`
     *
     * @psalm-return ConstVector<Tv> - A `ConstVector` that is a proper subset of the current
     *           `ConstVector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): ConstVector;

    /**
     * Returns a `ConstVector` that is the concatenation of the values of the
     * current `ConstVector` and the values of the provided `iterable`.
     *
     * The values of the provided `iterable` is concatenated to the end of the
     * current `ConstVector` to produce the returned `ConstVector`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                       `ConstVector`.
     *
     * @psalm-return ConstVector<Tu> - The concatenated `ConstVector`.
     */
    public function concat(iterable $iterable): ConstVector;

    /**
     * Returns the first value in the current `ConstVector`.
     *
     * @psalm-return null|Tv - The first value in the current `ConstVector`, or `null` if the
     *           current `ConstVector` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `ConstVector`.
     *
     * @psalm-return int|null - The first key in the current `ConstVector`, or `null` if the
     *                  current `ConstVector` is empty
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `ConstVector`.
     *
     * @psalm-return null|Tv - The last value in the current `ConstVector`, or `null` if the
     *           current `ConstVector` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `ConstVector`.
     *
     * @psalm-return int|null - The last key in the current `ConstVector`, or `null` if the
     *                  current `ConstVector` is empty
     */
    public function lastKey(): ?int;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `ConstVector`.
     *
     * @psalm-return int|null - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int;
}
