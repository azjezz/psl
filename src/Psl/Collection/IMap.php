<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends IAccessibleCollection<Tk, Tv>
 */
interface IMap extends IAccessibleCollection
{
    /**
     * Returns a `IVector` containing the values of the current
     * `IMap`.
     *
     * @psalm-return IVector<Tv>
     */
    public function values(): IVector;

    /**
     * Returns a `IVector` containing the keys of the current `IMap`.
     *
     * @psalm-return IVector<Tk>
     */
    public function keys(): IVector;

    /**
     * Returns a `IMap` containing the values of the current `IMap`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `IMap` remain unchanged in the
     * returned `IMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `IMap` values
     *
     * @psalm-return IMap<Tk, Tv> - a IMap containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): IMap;

    /**
     * Returns a `IMap` containing the values of the current `IMap`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `IMap` remain unchanged in the
     * returned `IMap`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `IMap` keys and values
     *
     * @psalm-return IMap<Tk, Tv> - a `IMap` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `IMap`
     */
    public function filterWithKey(callable $fn): IMap;

    /**
     * Returns a `IMap` after an operation has been applied to each value
     * in the current `IMap`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `IMap` to the
     * returned `IMap`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `IMap` values
     *
     * @psalm-return IMap<Tk, Tu> - a `IMap` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): IMap;

    /**
     * Returns a `IMap` after an operation has been applied to each key and
     * value in the current `IMap`.
     *
     * Every key and value in the current `IMap` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `IMap` to the returned
     * `IMap`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `IMap` keys and values
     *
     * @psalm-return IMap<Tk, Tu> - a `IMap` containing the values after a user-specified
     *                        operation on the current `IMap`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): IMap;

    /**
     * Returns the first value in the current `IMap`.
     *
     * @psalm-return null|Tv - The first value in the current `IMap`, or `null` if the
     *           current `IMap` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `IMap`.
     *
     * @psalm-return null|Tk - The first key in the current `IMap`, or `null` if the
     *                  current `IMap` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `IMap`.
     *
     * @psalm-return null|Tv - The last value in the current `IMap`, or `null` if the
     *           current `IMap` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `IMap`.
     *
     * @psalm-return null|Tk - The last key in the current `IMap`, or `null` if the
     *                  current `IMap` is empty
     */
    public function lastKey();

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `IMap`.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value);

    /**
     * Returns a `IMap` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `IMap` and the provided `iterable`.
     *
     * If the number of elements of the `IMap` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `IMap`.
     *
     * @psalm-return IMap<Tk, array{0: Tv, 1: Tu}> - The `IMap` that combines the values of the current
     *           `IMap` with the provided `iterable`.
     */
    public function zip(iterable $iterable): IMap;

    /**
     * Returns a `IMap` containing the first `n` values of the current
     * `IMap`.
     *
     * The returned `IMap` will always be a proper subset of the current
     * `IMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `IMap`
     *
     * @psalm-return IMap<Tk, Tv> - A `IMap` that is a proper subset of the current
     *           `IMap` up to `n` elements.
     */
    public function take(int $n): IMap;

    /**
     * Returns a `IMap` containing the values of the current `IMap`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `IMap` will always be a proper subset of the current
     * `IMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return IMap<Tk, Tv> - A `IMap` that is a proper subset of the current
     *           `IMap` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): IMap;

    /**
     * Returns a `IMap` containing the values after the `n`-th element of
     * the current `IMap`.
     *
     * The returned `IMap` will always be a proper subset of the current
     * `IMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `IMap`.
     *
     * @psalm-return IMap<Tk, Tv> - A `IMap` that is a proper subset of the current
     *           `IMap` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): IMap;

    /**
     * Returns a `IMap` containing the values of the current `IMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `IMap` will always be a proper subset of the current
     * `IMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `IMap`.
     *
     * @psalm-return IMap<Tk, Tv> - A `IMap` that is a proper subset of the current
     *           `IMap` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): IMap;

    /**
     * Returns a subset of the current `IMap` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `IMap` will always be a proper subset of this
     * `IMap`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `IMap`
     * @psalm-param int $len   - The length of the returned `IMap`
     *
     * @psalm-return IMap<Tk, Tv> - A `IMap` that is a proper subset of the current
     *           `IMap` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): IMap;
}
