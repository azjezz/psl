<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template T
 *
 * @extends  IVector<T>
 * @extends  IMutableAccessibleCollection<int, T>
 */
interface IMutableVector extends IVector, IMutableAccessibleCollection
{
    /**
     * Get an array copy of the current vector.
     *
     * @psalm-return list<T>
     */
    public function toArray(): array;

    /**
     * Returns a `IMutableVector` containing the values of the current
     * `IMutableVector`.
     *
     * @psalm-return IMutableVector<T>
     */
    public function values(): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the keys of the current `IMutableVector`.
     *
     * @psalm-return IMutableVector<int>
     */
    public function keys(): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the values of the current `IMutableVector`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `IMutableVector` remain unchanged in the
     * returned `IMutableVector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback containing the condition to apply to the current
     *                                 `IMutableVector` values
     *
     * @psalm-return IMutableVector<T> - a IMutableVector containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the values of the current `IMutableVector`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `IMutableVector` remain unchanged in the
     * returned `IMutableVector`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(int, T): bool) $fn - The callback containing the condition to apply to the current
     *                                     `IMutableVector` keys and values
     *
     * @psalm-return IMutableVector<T> - a `IMutableVector` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `IMutableVector`
     */
    public function filterWithKey(callable $fn): IMutableVector;

    /**
     * Returns a `IMutableVector` after an operation has been applied to each value
     * in the current `IMutableVector`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `IMutableVector` to the
     * returned `IMutableVector`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(T): Tu) $fn - The callback containing the operation to apply to the current
     *                               `IMutableVector` values
     *
     * @psalm-return   IMutableVector<Tu> - a `IMutableVector` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): IMutableVector;

    /**
     * Returns a `IMutableVector` after an operation has been applied to each key and
     * value in the current `IMutableVector`.
     *
     * Every key and value in the current `IMutableVector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `IMutableVector` to the returned
     * `IMutableVector`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, T): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `IMutableVector` keys and values
     *
     * @psalm-return   IMutableVector<Tu> - a `IMutableVector` containing the values after a user-specified
     *                        operation on the current `IMutableVector`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): IMutableVector;

    /**
     * Returns the first value in the current `IMutableVector`.
     *
     * @psalm-return null|T - The first value in the current `IMutableVector`, or `null` if the
     *           current `IMutableVector` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `IMutableVector`.
     *
     * @psalm-return null|int - The first key in the current `IMutableVector`, or `null` if the
     *                  current `IMutableVector` is empty
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `IMutableVector`.
     *
     * @psalm-return null|T - The last value in the current `IMutableVector`, or `null` if the
     *           current `IMutableVector` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `IMutableVector`.
     *
     * @psalm-return null|int - The last key in the current `IMutableVector`, or `null` if the
     *                  current `IMutableVector` is empty
     */
    public function lastKey(): ?int;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param  T $search_value - The value that will be search for in the current
     *                        `IMutableVector`.
     *
     * @psalm-return null|int - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int;

    /**
     * Returns a `IMutableVector` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `IMutableVector` and the provided `iterable`.
     *
     * If the number of elements of the `IMutableVector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `IMutableVector`.
     *
     * @psalm-return   IMutableVector<array{0: T, 1: Tu}> - The `IMutableVector` that combines the values of the current
     *           `IMutableVector` with the provided `iterable`.
     */
    public function zip(iterable $iterable): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the first `n` values of the current
     * `IMutableVector`.
     *
     * The returned `IMutableVector` will always be a proper subset of the current
     * `IMutableVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `IMutableVector`
     *
     * @psalm-return IMutableVector<T> - A `IMutableVector` that is a proper subset of the current
     *           `IMutableVector` up to `n` elements.
     */
    public function take(int $n): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the values of the current `IMutableVector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `IMutableVector` will always be a proper subset of the current
     * `IMutableVector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return IMutableVector<T> - A `IMutableVector` that is a proper subset of the current
     *           `IMutableVector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the values after the `n`-th element of
     * the current `IMutableVector`.
     *
     * The returned `IMutableVector` will always be a proper subset of the current
     * `IMutableVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `IMutableVector`.
     *
     * @psalm-return IMutableVector<T> - A `IMutableVector` that is a proper subset of the current
     *           `IMutableVector` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): IMutableVector;

    /**
     * Returns a `IMutableVector` containing the values of the current `IMutableVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `IMutableVector` will always be a proper subset of the current
     * `IMutableVector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback used to determine the starting element for the
     *              returned `IMutableVector`.
     *
     * @psalm-return IMutableVector<T> - A `IMutableVector` that is a proper subset of the current
     *           `IMutableVector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): IMutableVector;

    /**
     * Returns a subset of the current `IMutableVector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `IMutableVector` will always be a proper subset of this
     * `IMutableVector`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `IMutableVector`
     * @psalm-param  int $len   - The length of the returned `IMutableVector`
     *
     * @psalm-return IMutableVector<T> - A `IMutableVector` that is a proper subset of the current
     *           `IMutableVector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): IMutableVector;

    /**
     * Stores a value into the current vector with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * It returns the current vector, meaning changes made to the current
     * vector will be reflected in the returned vector.
     *
     * @psalm-param  int $k - The key to which we will set the value
     * @psalm-param  T   $v - The value to set
     *
     * @psalm-return IMutableVector<T> - Returns itself
     */
    public function set($k, $v): IMutableVector;

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current vector associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current vector, meaning changes made to the current vector
     * will be reflected in the returned vector.
     *
     * @psalm-param  iterable<int, T> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return IMutableVector<T> - Returns itself
     */
    public function setAll(iterable $iterable): IMutableVector;

    /**
     * Removes the specified key (and associated value) from the current
     * vector.
     *
     * If the key is not in the current vector, the current vector is
     * unchanged.
     *
     * This will cause elements with higher keys to be assigned a new key that is one less
     * than their previous key.
     *
     * That is, values with keys $k + 1 to n - 1 will be given new keys $k to n - 2, where n is
     * the length of the current IMutableVector before the call to remove().
     *
     * If $k is negative, or $k is greater than the largest key in the current Vector, no changes are made.
     *
     * @psalm-param  int $k - The key to remove
     *
     * @psalm-return IMutableVector<T> - Returns itself
     */
    public function remove($k): IMutableVector;

    /**
     * Removes all items from the vector.
     *
     * @psalm-return IMutableVector<T>
     */
    public function clear(): IMutableVector;

    /**
     * Add a value to the vector and return the vector itself.
     *
     * @psalm-param  T $v - The value to add
     *
     * @psalm-return IMutableVector<T> - Returns itself
     */
    public function add($v): IMutableVector;

    /**
     * For every element in the provided iterable, add the value into the current vector.
     *
     * @psalm-param  iterable<T> $iterable - The `iterable` with the new values to add
     *
     * @psalm-return IMutableVector<T> - Returns itself
     */
    public function addAll(iterable $iterable): IMutableVector;
}
