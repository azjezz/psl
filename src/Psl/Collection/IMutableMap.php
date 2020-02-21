<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends IMap<Tk, Tv>
 * @extends IMutableAccessibleCollection<Tk, Tv>
 */
interface IMutableMap extends IMap, IMutableAccessibleCollection {
    /**
     * Returns a `IMutableVector` containing the values of the current
     * `IMutableMap`.
     *
     * @psalm-return IMutableVector<Tv>
     */
    public function values(): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the keys of the current `IMutableMap`.
     *
     * @psalm-return IMutableVector<Tk>
     */
    public function keys(): IMutableVector;

    /**
     * Returns a `IMutableMap` containing the values of the current `IMutableMap`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `IMutableMap` remain unchanged in the
     * returned `IMutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `IMutableMap` values
     *
     * @psalm-return IMutableMap<Tk, Tv> - a IMutableMap containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): IMutableMap;

    /**
     * Returns a `IMutableMap` containing the values of the current `IMutableMap`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `IMutableMap` remain unchanged in the
     * returned `IMutableMap`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `IMutableMap` keys and values
     *
     * @psalm-return IMutableMap<Tk, Tv> - a `IMutableMap` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `IMutableMap`
     */
    public function filterWithKey(callable $fn): IMutableMap;

    /**
     * Returns a `IMutableMap` after an operation has been applied to each value
     * in the current `IMutableMap`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `IMutableMap` to the
     * returned `IMutableMap`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `IMutableMap` values
     *
     * @psalm-return IMutableMap<Tk, Tu> - a `IMutableMap` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): IMutableMap;

    /**
     * Returns a `IMutableMap` after an operation has been applied to each key and
     * value in the current `IMutableMap`.
     *
     * Every key and value in the current `IMutableMap` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `IMutableMap` to the returned
     * `IMutableMap`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `IMutableMap` keys and values
     *
     * @psalm-return IMutableMap<Tk, Tu> - a `IMutableMap` containing the values after a user-specified
     *                        operation on the current `IMutableMap`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): IMutableMap;

    /**
     * Returns the first value in the current `IMutableMap`.
     *
     * @psalm-return null|Tv - The first value in the current `IMutableMap`, or `null` if the
     *           current `IMutableMap` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `IMutableMap`.
     *
     * @psalm-return null|Tk - The first key in the current `IMutableMap`, or `null` if the
     *                  current `IMutableMap` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `IMutableMap`.
     *
     * @psalm-return null|Tv - The last value in the current `IMutableMap`, or `null` if the
     *           current `IMutableMap` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `IMutableMap`.
     *
     * @psalm-return null|Tk - The last key in the current `IMutableMap`, or `null` if the
     *                  current `IMutableMap` is empty
     */
    public function lastKey();

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `IMutableMap`.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value);

    /**
     * Returns a `IMutableMap` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `IMutableMap` and the provided `iterable`.
     *
     * If the number of elements of the `IMutableMap` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `IMutableMap`.
     *
     * @psalm-return IMutableMap<Tk, array{0: Tv, 1: Tu}> - The `IMutableMap` that combines the values of the current
     *           `IMutableMap` with the provided `iterable`.
     */
    public function zip(iterable $iterable): IMutableMap;

    /**
     * Returns a `IMutableMap` containing the first `n` values of the current
     * `IMutableMap`.
     *
     * The returned `IMutableMap` will always be a proper subset of the current
     * `IMutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `IMutableMap`
     *
     * @psalm-return IMutableMap<Tk, Tv> - A `IMutableMap` that is a proper subset of the current
     *           `IMutableMap` up to `n` elements.
     */
    public function take(int $n): IMutableMap;

    /**
     * Returns a `IMutableMap` containing the values of the current `IMutableMap`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `IMutableMap` will always be a proper subset of the current
     * `IMutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return IMutableMap<Tk, Tv> - A `IMutableMap` that is a proper subset of the current
     *           `IMutableMap` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): IMutableMap;

    /**
     * Returns a `IMutableMap` containing the values after the `n`-th element of
     * the current `IMutableMap`.
     *
     * The returned `IMutableMap` will always be a proper subset of the current
     * `IMutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `IMutableMap`.
     *
     * @psalm-return IMutableMap<Tk, Tv> - A `IMutableMap` that is a proper subset of the current
     *           `IMutableMap` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): IMutableMap;

    /**
     * Returns a `IMutableMap` containing the values of the current `IMutableMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `IMutableMap` will always be a proper subset of the current
     * `IMutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `IMutableMap`.
     *
     * @psalm-return IMutableMap<Tk, Tv> - A `IMutableMap` that is a proper subset of the current
     *           `IMutableMap` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): IMutableMap;

    /**
     * Returns a subset of the current `IMutableMap` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `IMutableMap` will always be a proper subset of this
     * `IMutableMap`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `IMutableMap`
     * @psalm-param int $len   - The length of the returned `IMutableMap`
     *
     * @psalm-return IMutableMap<Tk, Tv> - A `IMutableMap` that is a proper subset of the current
     *           `IMutableMap` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): IMutableMap;
    
    /**
     * Stores a value into the current collection with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * It returns the current collection, meaning changes made to the current
     * collection will be reflected in the returned collection.
     *
     * @psalm-param Tk $k - The key to which we will set the value
     * @psalm-param Tv $v - The value to set
     *
     * @psalm-return IMutableMap<Tk, Tv> - Returns itself
     */
    public function set($k, $v): IMutableMap;

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current collection associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return IMutableMap<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): IMutableMap;

    /**
     * Add a value to the collection and return the collection itself.
     *
     * @psalm-param Tk $k - The key to which we will add the value
     * @psalm-param Tv $v - The value to set
     *
     * @psalm-return IMutableMap<Tk, Tv> - Returns itself
     */
    public function add($k, $v): IMutableMap;

    /**
     * For every element in the provided iterable, add the value into the current collection.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` with the new values to add
     *
     * @psalm-return IMutableMap<Tk, Tv> - Returns itself
     */
    public function addAll(iterable $iterable): IMutableMap;

    /**
     * Removes the specified key (and associated value) from the current
     * collection.
     *
     * If the key is not in the current collection, the current collection is
     * unchanged.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @psalm-param  Tk $k - The key to remove
     *
     * @psalm-return IMutableMap<Tk, Tv> - Returns itself
     */
    public function remove($k): IMutableMap;

    /**
     * Removes all items from the collection.
     *
     * @psalm-return IMutableMap<Tk, Tv>
     */
    public function clear(): IMutableMap;
}
