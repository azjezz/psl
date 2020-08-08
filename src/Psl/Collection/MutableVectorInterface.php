<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template T
 *
 * @extends  VectorInterface<T>
 * @extends  MutableAccessibleCollectionInterface<int, T>
 */
interface MutableVectorInterface extends VectorInterface, MutableAccessibleCollectionInterface
{
    /**
     * Get an array copy of the current vector.
     *
     * @psalm-return list<T>
     */
    public function toArray(): array;

    /**
     * Returns a `MutableVectorInterface` containing the values of the current
     * `MutableVectorInterface`.
     *
     * @psalm-return MutableVectorInterface<T>
     */
    public function values(): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the keys of the current `MutableVectorInterface`.
     *
     * @psalm-return MutableVectorInterface<int>
     */
    public function keys(): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the values of the current `MutableVectorInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableVectorInterface` remain unchanged in the
     * returned `MutableVectorInterface`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback containing the condition to apply to the current
     *                                 `MutableVectorInterface` values
     *
     * @psalm-return MutableVectorInterface<T> - a MutableVectorInterface containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the values of the current `MutableVectorInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableVectorInterface` remain unchanged in the
     * returned `MutableVectorInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(int, T): bool) $fn - The callback containing the condition to apply to the current
     *                                     `MutableVectorInterface` keys and values
     *
     * @psalm-return MutableVectorInterface<T> - a `MutableVectorInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `MutableVectorInterface`
     */
    public function filterWithKey(callable $fn): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` after an operation has been applied to each value
     * in the current `MutableVectorInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableVectorInterface` to the
     * returned `MutableVectorInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(T): Tu) $fn - The callback containing the operation to apply to the current
     *                               `MutableVectorInterface` values
     *
     * @psalm-return   MutableVectorInterface<Tu> - a `MutableVectorInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` after an operation has been applied to each key and
     * value in the current `MutableVectorInterface`.
     *
     * Every key and value in the current `MutableVectorInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableVectorInterface` to the returned
     * `MutableVectorInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, T): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `MutableVectorInterface` keys and values
     *
     * @psalm-return   MutableVectorInterface<Tu> - a `MutableVectorInterface` containing the values after a user-specified
     *                        operation on the current `MutableVectorInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): MutableVectorInterface;

    /**
     * Returns the first value in the current `MutableVectorInterface`.
     *
     * @psalm-return null|T - The first value in the current `MutableVectorInterface`, or `null` if the
     *           current `MutableVectorInterface` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `MutableVectorInterface`.
     *
     * @psalm-return null|int - The first key in the current `MutableVectorInterface`, or `null` if the
     *                  current `MutableVectorInterface` is empty
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `MutableVectorInterface`.
     *
     * @psalm-return null|T - The last value in the current `MutableVectorInterface`, or `null` if the
     *           current `MutableVectorInterface` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `MutableVectorInterface`.
     *
     * @psalm-return null|int - The last key in the current `MutableVectorInterface`, or `null` if the
     *                  current `MutableVectorInterface` is empty
     */
    public function lastKey(): ?int;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param  T $search_value - The value that will be search for in the current
     *                        `MutableVectorInterface`.
     *
     * @psalm-return null|int - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int;

    /**
     * Returns a `MutableVectorInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableVectorInterface` and the provided `iterable`.
     *
     * If the number of elements of the `MutableVectorInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `MutableVectorInterface`.
     *
     * @psalm-return   MutableVectorInterface<array{0: T, 1: Tu}> - The `MutableVectorInterface` that combines the values of the current
     *           `MutableVectorInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the first `n` values of the current
     * `MutableVectorInterface`.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of the current
     * `MutableVectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `MutableVectorInterface`
     *
     * @psalm-return MutableVectorInterface<T> - A `MutableVectorInterface` that is a proper subset of the current
     *           `MutableVectorInterface` up to `n` elements.
     */
    public function take(int $n): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the values of the current `MutableVectorInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of the current
     * `MutableVectorInterface`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return MutableVectorInterface<T> - A `MutableVectorInterface` that is a proper subset of the current
     *           `MutableVectorInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the values after the `n`-th element of
     * the current `MutableVectorInterface`.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of the current
     * `MutableVectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `MutableVectorInterface`.
     *
     * @psalm-return MutableVectorInterface<T> - A `MutableVectorInterface` that is a proper subset of the current
     *           `MutableVectorInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the values of the current `MutableVectorInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of the current
     * `MutableVectorInterface`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback used to determine the starting element for the
     *              returned `MutableVectorInterface`.
     *
     * @psalm-return MutableVectorInterface<T> - A `MutableVectorInterface` that is a proper subset of the current
     *           `MutableVectorInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableVectorInterface;

    /**
     * Returns a subset of the current `MutableVectorInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of this
     * `MutableVectorInterface`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `MutableVectorInterface`
     * @psalm-param  int $len   - The length of the returned `MutableVectorInterface`
     *
     * @psalm-return MutableVectorInterface<T> - A `MutableVectorInterface` that is a proper subset of the current
     *           `MutableVectorInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): MutableVectorInterface;

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
     * @psalm-return MutableVectorInterface<T> - Returns itself
     */
    public function set($k, $v): MutableVectorInterface;

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
     * @psalm-return MutableVectorInterface<T> - Returns itself
     */
    public function setAll(iterable $iterable): MutableVectorInterface;

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
     * the length of the current MutableVectorInterface before the call to remove().
     *
     * If $k is negative, or $k is greater than the largest key in the current Vector, no changes are made.
     *
     * @psalm-param  int $k - The key to remove
     *
     * @psalm-return MutableVectorInterface<T> - Returns itself
     */
    public function remove($k): MutableVectorInterface;

    /**
     * Removes all items from the vector.
     *
     * @psalm-return MutableVectorInterface<T>
     */
    public function clear(): MutableVectorInterface;

    /**
     * Add a value to the vector and return the vector itself.
     *
     * @psalm-param  T $v - The value to add
     *
     * @psalm-return MutableVectorInterface<T> - Returns itself
     */
    public function add($v): MutableVectorInterface;

    /**
     * For every element in the provided iterable, add the value into the current vector.
     *
     * @psalm-param  iterable<T> $iterable - The `iterable` with the new values to add
     *
     * @psalm-return MutableVectorInterface<T> - Returns itself
     */
    public function addAll(iterable $iterable): MutableVectorInterface;
}
