<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The base interface implemented for a collection type that you are able set and remove its values.
 * keys.
 *
 * Every concrete mutable class indirectly implements this interface.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends IAccessibleCollection<Tk, Tv>
 * @extends IMutableCollection<Tk, Tv>
 * @extends IMutableIndexAccess<Tk, Tv>
 */
interface IMutableAccessibleCollection extends IAccessibleCollection, IMutableCollection, IMutableIndexAccess
{
    /**
     * Returns a `IMutableAccessibleCollection` containing the values of the current
     * `IMutableAccessibleCollection`.
     *
     * @psalm-return IMutableAccessibleCollection<int, Tv>
     */
    public function values(): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the keys of the current `IMutableAccessibleCollection`.
     *
     * @psalm-return IMutableAccessibleCollection<int, Tk>
     */
    public function keys(): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the values of the current `IMutableAccessibleCollection`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `IMutableAccessibleCollection` remain unchanged in the
     * returned `IMutableAccessibleCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `IMutableAccessibleCollection` values
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - a Collection containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the values of the current `IMutableAccessibleCollection`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `IMutableAccessibleCollection` remain unchanged in the
     * returned `IMutableAccessibleCollection`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `IMutableAccessibleCollection` keys and values
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - a `IMutableAccessibleCollection` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `IMutableAccessibleCollection`
     */
    public function filterWithKey(callable $fn): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` after an operation has been applied to each value
     * in the current `IMutableAccessibleCollection`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `IMutableAccessibleCollection` to the
     * returned `IMutableAccessibleCollection`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `IMutableAccessibleCollection` values
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tu> - a `IMutableAccessibleCollection` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` after an operation has been applied to each key and
     * value in the current `IMutableAccessibleCollection`.
     *
     * Every key and value in the current `IMutableAccessibleCollection` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `IMutableAccessibleCollection` to the returned
     * `IMutableAccessibleCollection`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `IMutableAccessibleCollection` keys and values
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tu> - a `IMutableAccessibleCollection` containing the values after a user-specified
     *                        operation on the current `IMutableAccessibleCollection`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): IMutableAccessibleCollection;

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
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - Returns itself
     */
    public function set($k, $v): IMutableAccessibleCollection;

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
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): IMutableAccessibleCollection;

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
     * @psalm-param Tk $k - The key to remove
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - Returns itself
     */
    public function remove($k): IMutableAccessibleCollection;

    /**
     * Removes all items from the collection.
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv>
     */
    public function clear(): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `IMutableAccessibleCollection` and the provided `iterable`.
     *
     * If the number of elements of the `IMutableAccessibleCollection` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `IMutableAccessibleCollection`.
     *
     * @psalm-return IMutableAccessibleCollection<Tk, array{0: Tv, 1: Tu}> - The `IMutableAccessibleCollection` that combines the values of the current
     *           `IMutableAccessibleCollection` with the provided `iterable`.
     */
    public function zip(iterable $iterable): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the first `n` values of the current
     * `IMutableAccessibleCollection`.
     *
     * The returned `IMutableAccessibleCollection` will always be a proper subset of the current
     * `IMutableAccessibleCollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `IMutableAccessibleCollection`
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - A `IMutableAccessibleCollection` that is a proper subset of the current
     *           `IMutableAccessibleCollection` up to `n` elements.
     */
    public function take(int $n): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the values of the current `IMutableAccessibleCollection`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `IMutableAccessibleCollection` will always be a proper subset of the current
     * `IMutableAccessibleCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - A `IMutableAccessibleCollection` that is a proper subset of the current
     *           `IMutableAccessibleCollection` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the values after the `n`-th element of
     * the current `IMutableAccessibleCollection`.
     *
     * The returned `IMutableAccessibleCollection` will always be a proper subset of the current
     * `IMutableAccessibleCollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `IMutableAccessibleCollection`.
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - A `IMutableAccessibleCollection` that is a proper subset of the current
     *           `IMutableAccessibleCollection` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): IMutableAccessibleCollection;

    /**
     * Returns a `IMutableAccessibleCollection` containing the values of the current `IMutableAccessibleCollection`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `IMutableAccessibleCollection` will always be a proper subset of the current
     * `IMutableAccessibleCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `IMutableAccessibleCollection`.
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - A `IMutableAccessibleCollection` that is a proper subset of the current
     *           `IMutableAccessibleCollection` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): IMutableAccessibleCollection;

    /**
     * Returns a subset of the current `IMutableAccessibleCollection` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `IMutableAccessibleCollection` will always be a proper subset of this
     * `IMutableAccessibleCollection`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `IMutableAccessibleCollection`
     * @psalm-param int $len   - The length of the returned `IMutableAccessibleCollection`
     *
     * @psalm-return IMutableAccessibleCollection<Tk, Tv> - A `IMutableAccessibleCollection` that is a proper subset of the current
     *           `IMutableAccessibleCollection` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): IMutableAccessibleCollection;
}
