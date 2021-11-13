<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template T
 *
 * @extends MutableVectorInterface<T>
 */
interface MutableSetInterface extends MutableVectorInterface
{
    /**
     * Get an array copy of the current vector.
     *
     * @return list<T>
     *
     * @psalm-mutation-free
     */
    public function toArray(): array;

    /**
     * Returns a `MutableSetInterface` containing the values of the current
     * `MutableSetInterface`.
     *
     * @return MutableSetInterface<T>
     *
     * @psalm-mutation-free
     */
    public function values(): MutableSetInterface;

    /**
     * Returns a `MutableSetInterface` containing the keys of the current `MutableSetInterface`.
     *
     * @return MutableSetInterface<int>
     *
     * @psalm-mutation-free
     */
    public function keys(): MutableSetInterface;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableSetInterface` remain unchanged in the
     * returned `MutableSetInterface`.
     *
     * @param (callable(T): bool) $fn The callback containing the condition to apply to the current
     *                                `MutableSetInterface` values
     *
     * @return MutableSetInterface<T> A MutableSetInterface containing the values after
     *                                a user-specified condition is applied.
     */
    public function filter(callable $fn): MutableSetInterface;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableSetInterface` remain unchanged in the
     * returned `MutableSetInterface`; the keys will be used in the filtering process only.
     *
     * @param (callable(int, T): bool) $fn The callback containing the condition to apply to the current
     *                                     `MutableSetInterface` keys and values.
     *
     * @return MutableSetInterface<T> A `MutableSetInterface` containing the values after a user-specified
     *                                condition is applied to the keys and values of the current
     *                                `MutableSetInterface`.
     */
    public function filterWithKey(callable $fn): MutableSetInterface;

    /**
     * Returns a `MutableVectorInterface` after an operation has been applied to each value
     * in the current `MutableSetInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableSetInterface` to the
     * returned `MutableVectorInterface`.
     *
     * @template Tu
     *
     * @param (callable(T): Tu) $fn The callback containing the operation to apply to the current
     *                              `MutableSetInterface` values
     *
     * @return MutableVectorInterface<Tu> A `MutableVectorInterface` containing key/value pairs after
     *                                    a user-specified operation is applied.
     */
    public function map(callable $fn): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` after an operation has been applied to each key and
     * value in the current `MutableSetInterface`.
     *
     * Every key and value in the current `MutableSetInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableSetInterface` to the returned
     * `MutableVectorInterface`. The keys are only used to help in the mapping operation.
     *
     * @template Tu
     *
     * @param (callable(int, T): Tu) $fn The callback containing the operation to apply to the current
     *                                   `MutableSetInterface` keys and values
     *
     * @return MutableVectorInterface<Tu> A `MutableVectorInterface` containing the values after
     *                                    a user-specified operation on the current `MutableSetInterface`'s
     *                                    keys and values is applied.
     */
    public function mapWithKey(callable $fn): MutableVectorInterface;

    /**
     * Returns the first value in the current `MutableSetInterface`.
     *
     * @return T|null The first value in the current `MutableSetInterface`, or `null` if the
     *                current `MutableSetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function first(): mixed;

    /**
     * Returns the first key in the current `MutableSetInterface`.
     *
     * @return int|null The first key in the current `MutableSetInterface`, or `null` if the
     *                  current `MutableSetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `MutableSetInterface`.
     *
     * @return T|null The last value in the current `MutableSetInterface`, or `null` if the
     *                current `MutableSetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function last(): mixed;

    /**
     * Returns the last key in the current `MutableSetInterface`.
     *
     * @return int|null The last key in the current `MutableSetInterface`, or `null` if the
     *                  current `MutableSetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function lastKey(): ?int;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @param T $search_value The value that will be search for in the current
     *                        `MutableSetInterface`.
     *
     * @return int|null The key (index) where that value is found; null if it is not found.
     *
     * @psalm-mutation-free
     */
    public function linearSearch(mixed $search_value): ?int;

    /**
     * Returns a `MutableVectorInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableSetInterface` and the provided `iterable`.
     *
     * If the number of elements of the `MutableSetInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param iterable<Tu> $iterable The `iterable` to use to combine with the
     *                               elements of this `MutableSetInterface`.
     *
     * @return MutableVectorInterface<array{0: T, 1: Tu}> - The `MutableVectorInterface` that combines the
     *                                                    values of the current `MutableSetInterface` with
     *                                                    the provided `iterable`.
     *
     * @psalm-mutation-free
     */
    public function zip(iterable $iterable): MutableVectorInterface;

    /**
     * Returns a `MutableSetInterface` containing the first `n` values of the current
     * `MutableSetInterface`.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int $n The last element that will be included in the returned
     *               `MutableSetInterface`.
     *
     * @return MutableSetInterface<T> A `MutableSetInterface` that is a proper subset of the current
     *                                `MutableSetInterface` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    public function take(int $n): MutableSetInterface;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * @param (callable(T): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return MutableSetInterface<T> A `MutableSetInterface` that is a proper subset of the current
     *                                `MutableSetInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableSetInterface;

    /**
     * Returns a `MutableSetInterface` containing the values after the `n`-th element of
     * the current `MutableSetInterface`.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int $n The last element to be skipped; the $n+1 element will be the
     *               first one in the returned `MutableSetInterface`.
     *
     * @return MutableSetInterface<T> A `MutableSetInterface` that is a proper subset of the current
     *                                `MutableSetInterface` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    public function drop(int $n): MutableSetInterface;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * @param (callable(T): bool) $fn The callback used to determine the starting element for the
     *                                returned `MutableSetInterface`.
     *
     * @return MutableSetInterface<T> A `MutableSetInterface` that is a proper subset of the current
     *                                `MutableSetInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableSetInterface;

    /**
     * Returns a subset of the current `MutableSetInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableSetInterface` will always be a proper subset of this
     * `MutableSetInterface`.
     *
     * @param int $start The starting key of this Vector to begin the returned
     *                   `MutableSetInterface`.
     * @param int $length The length of the returned `MutableSetInterface`.
     *
     * @return MutableSetInterface<T> A `MutableSetInterface` that is a proper subset of the current
     *                                `MutableSetInterface` starting at `$start` up to but not including
     *                                the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, int $length): MutableSetInterface;

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
     * @param int $k The key to which we will set the value.
     * @param T $v The value to set.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function set(int|string $k, mixed $v): MutableSetInterface;

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
     * @param iterable<int, T> $iterable The `iterable` with the new values to set.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function setAll(iterable $iterable): MutableSetInterface;

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
     * the length of the current MutableSetInterface before the call to remove().
     *
     * If $k is negative, or $k is greater than the largest key in the current Vector, no changes are made.
     *
     * @param int $k The key to remove.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function remove(int|string $k): MutableSetInterface;

    /**
     * Removes all items from the vector.
     *
     * @return MutableSetInterface<T>
     */
    public function clear(): MutableSetInterface;

    /**
     * Add a value to the vector and return the vector itself.
     *
     * @param T $v The value to add.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function add(mixed $v): MutableSetInterface;

    /**
     * For every element in the provided iterable, add the value into the current vector.
     *
     * @param iterable<T> $iterable The `iterable` with the new values to add.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function addAll(iterable $iterable): MutableSetInterface;
}
