<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * IMutableCollection is the primary collection interface for mutable collections.
 *
 * Assuming you want the ability to clear out your collection, you would implement this (or a child of this)
 * interface.
 *
 * If your collection to be immutable, implement Collection only instead.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends ICollection<Tk, Tv>
 */
interface IMutableCollection extends ICollection
{
    /**
     * Returns a `IMutableCollection` containing the values of the current `IMutableCollection`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `IMutableCollection` remain unchanged in the
     * returned `IMutableCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `IMutableCollection` values
     *
     * @psalm-return IMutableCollection<Tk, Tv> - a Collection containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): IMutableCollection;

    /**
     * Returns a `IMutableCollection` containing the values of the current `IMutableCollection`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `IMutableCollection` remain unchanged in the
     * returned `IMutableCollection`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `IMutableCollection` keys and values
     *
     * @psalm-return IMutableCollection<Tk, Tv> - a `IMutableCollection` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `IMutableCollection`
     */
    public function filterWithKey(callable $fn): IMutableCollection;

    /**
     * Returns a `IMutableCollection` after an operation has been applied to each value
     * in the current `IMutableCollection`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `IMutableCollection` to the
     * returned `IMutableCollection`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `IMutableCollection` values
     *
     * @psalm-return IMutableCollection<Tk, Tu> - a `IMutableCollection` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): IMutableCollection;

    /**
     * Returns a `IMutableCollection` after an operation has been applied to each key and
     * value in the current `IMutableCollection`.
     *
     * Every key and value in the current `IMutableCollection` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `IMutableCollection` to the returned
     * `IMutableCollection`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `IMutableCollection` keys and values
     *
     * @psalm-return IMutableCollection<Tk, Tu> - a `IMutableCollection` containing the values after a user-specified
     *                        operation on the current `IMutableCollection`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): IMutableCollection;

    /**
     * Returns a `IMutableCollection` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `IMutableCollection` and the provided `iterable`.
     *
     * If the number of elements of the `IMutableCollection` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `IMutableCollection`.
     *
     * @psalm-return IMutableCollection<Tk, array{0: Tv, 1: Tu}> - The `IMutableCollection` that combines the values of the current
     *           `IMutableCollection` with the provided `iterable`.
     */
    public function zip(iterable $iterable): IMutableCollection;

    /**
     * Returns a `IMutableCollection` containing the first `n` values of the current
     * `IMutableCollection`.
     *
     * The returned `IMutableCollection` will always be a proper subset of the current
     * `IMutableCollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `IMutableCollection`
     *
     * @psalm-return IMutableCollection<Tk, Tv> - A `IMutableCollection` that is a proper subset of the current
     *           `IMutableCollection` up to `n` elements.
     */
    public function take(int $n): IMutableCollection;

    /**
     * Returns a `IMutableCollection` containing the values of the current `IMutableCollection`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `IMutableCollection` will always be a proper subset of the current
     * `IMutableCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return IMutableCollection<Tk, Tv> - A `IMutableCollection` that is a proper subset of the current
     *           `IMutableCollection` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): IMutableCollection;

    /**
     * Returns a `IMutableCollection` containing the values after the `n`-th element of
     * the current `IMutableCollection`.
     *
     * The returned `IMutableCollection` will always be a proper subset of the current
     * `IMutableCollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `IMutableCollection`.
     *
     * @psalm-return IMutableCollection<Tk, Tv> - A `IMutableCollection` that is a proper subset of the current
     *           `IMutableCollection` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): IMutableCollection;

    /**
     * Returns a `IMutableCollection` containing the values of the current `IMutableCollection`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `IMutableCollection` will always be a proper subset of the current
     * `IMutableCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `IMutableCollection`.
     *
     * @psalm-return IMutableCollection<Tk, Tv> - A `IMutableCollection` that is a proper subset of the current
     *           `IMutableCollection` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): IMutableCollection;

    /**
     * Returns a subset of the current `IMutableCollection` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `IMutableCollection` will always be a proper subset of this
     * `IMutableCollection`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `IMutableCollection`
     * @psalm-param int $len   - The length of the returned `IMutableCollection`
     *
     * @psalm-return IMutableCollection<Tk, Tv> - A `IMutableCollection` that is a proper subset of the current
     *           `IMutableCollection` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): IMutableCollection;
    
    /**
     * Removes all items from the collection.
     *
     * @psalm-return IMutableCollection<Tk, Tv>
     */
    public function clear(): IMutableCollection;
}
