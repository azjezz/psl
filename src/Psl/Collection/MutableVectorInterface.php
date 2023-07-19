<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;

/**
 * @template T
 *
 * @extends VectorInterface<T>
 * @extends MutableAccessibleCollectionInterface<int<0, max>, T>
 */
interface MutableVectorInterface extends MutableAccessibleCollectionInterface, VectorInterface
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
     * Returns a `MutableVectorInterface` containing the values of the current
     * `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<T>
     *
     * @psalm-mutation-free
     */
    public function values(): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the keys of the current `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<int<0, max>>
     *
     * @psalm-mutation-free
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
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `MutableVectorInterface` values
     *
     * @return MutableVectorInterface<T> A MutableVectorInterface containing the values after
     *                                   a user-specified condition is applied.
     */
    public function filter(Closure $fn): MutableVectorInterface;

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
     * @param (Closure(int<0, max>, T): bool) $fn The callback containing the condition to apply to the current
     *                                            `MutableVectorInterface` keys and values.
     *
     * @return MutableVectorInterface<T> A `MutableVectorInterface` containing the values after a user-specified
     *                                   condition is applied to the keys and values of the current
     *                                   `MutableVectorInterface`.
     */
    public function filterWithKey(Closure $fn): MutableVectorInterface;

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
     * @template Tu
     *
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `MutableVectorInterface` values
     *
     * @return MutableVectorInterface<Tu> A `MutableVectorInterface` containing key/value pairs after
     *                                    a user-specified operation is applied.
     */
    public function map(Closure $fn): MutableVectorInterface;

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
     * @template Tu
     *
     * @param (Closure(int<0, max>, T): Tu) $fn The callback containing the operation to apply to the current
     *                                          `MutableVectorInterface` keys and values
     *
     * @return MutableVectorInterface<Tu> A `MutableVectorInterface` containing the values after
     *                                    a user-specified operation on the current `MutableVectorInterface`'s
     *                                    keys and values is applied.
     */
    public function mapWithKey(Closure $fn): MutableVectorInterface;

    /**
     * Returns the first value in the current `MutableVectorInterface`.
     *
     * @return T|null The first value in the current `MutableVectorInterface`, or `null` if the
     *                current `MutableVectorInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function first(): mixed;

    /**
     * Returns the first key in the current `MutableVectorInterface`.
     *
     * @return int<0, max>|null The first key in the current `MutableVectorInterface`, or `null` if the
     *                          current `MutableVectorInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `MutableVectorInterface`.
     *
     * @return T|null The last value in the current `MutableVectorInterface`, or `null` if the
     *                current `MutableVectorInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function last(): mixed;

    /**
     * Returns the last key in the current `MutableVectorInterface`.
     *
     * @return int<0, max>|null The last key in the current `MutableVectorInterface`, or `null` if the
     *                          current `MutableVectorInterface` is empty.
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
     *                        `MutableVectorInterface`.
     *
     * @return int<0, max>|null The key (index) where that value is found; null if it is not found.
     *
     * @psalm-mutation-free
     */
    public function linearSearch(mixed $search_value): ?int;

    /**
     * Returns a `MutableVectorInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableVectorInterface` and the provided elements.
     *
     * If the number of elements of the `MutableVectorInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<array{0: T, 1: Tu}> The `MutableVectorInterface` that combines the values of
     *                                                    the current `MutableVectorInterface` with the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the first `n` values of the current
     * `MutableVectorInterface`.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of the current
     * `MutableVectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<T> A `MutableVectorInterface` that is a proper subset of the current
     *                                   `MutableVectorInterface` up to `n` elements.
     *
     * @psalm-mutation-free
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
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     *
     * @return MutableVectorInterface<T> A `MutableVectorInterface` that is a proper subset of the current
     *                                   `MutableVectorInterface` up until the callback returns `false`.
     */
    public function takeWhile(Closure $fn): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the values after the `n`-th element of
     * the current `MutableVectorInterface`.
     *
     * The returned `MutableVectorInterface` will always be a proper subset of the current
     * `MutableVectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<T> A `MutableVectorInterface` that is a proper subset of the current
     *                                   `MutableVectorInterface` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
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
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<T> A `MutableVectorInterface` that is a proper subset of the current
     *                                   `MutableVectorInterface` starting after the callback returns `true`.
     */
    public function dropWhile(Closure $fn): MutableVectorInterface;

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
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `MutableVectorInterface`.
     * @param null|int<0, max> $length The length of the returned `MutableVectorInterface`.
     *
     * @return MutableVectorInterface<T> A `MutableVectorInterface` that is a proper subset of the current
     *                                   `MutableVectorInterface` starting at `$start` up to but not including
     *                                   the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the original `MutableVectorInterface` split into
     * chunks of the given size.
     *
     * If the original `MutableVectorInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return MutableVectorInterface<static<T>> A `MutableVectorInterface` containing the original
     *                                           `MutableVectorInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): MutableVectorInterface;

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
     * @param int<0, max> $k The key to which we will set the value.
     * @param T $v The value to set.
     *
     * @return MutableVectorInterface<T> Returns itself.
     */
    public function set(int|string $k, mixed $v): MutableVectorInterface;

    /**
     * For every element in the provided elements array, stores a value into the
     * current vector associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current vector, meaning changes made to the current vector
     * will be reflected in the returned vector.
     *
     * @param array<int<0, max>, T> $elements The elements with the new values to set.
     *
     * @return MutableVectorInterface<T> Returns itself.
     */
    public function setAll(array $elements): MutableVectorInterface;

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
     * @param int<0, max> $k The key to remove.
     *
     * @return MutableVectorInterface<T> Returns itself.
     */
    public function remove(int|string $k): MutableVectorInterface;

    /**
     * Removes all elements from the vector.
     *
     * @return MutableVectorInterface<T>
     */
    public function clear(): MutableVectorInterface;

    /**
     * Add a value to the vector and return the vector itself.
     *
     * @param T $v The value to add.
     *
     * @return MutableVectorInterface<T> Returns itself.
     */
    public function add(mixed $v): MutableVectorInterface;

    /**
     * For every element in the provided elements array, add the value into the current vector.
     *
     * @param array<array-key, T> $elements The elements with the new values to add.
     *
     * @return MutableVectorInterface<T> Returns itself.
     */
    public function addAll(array $elements): MutableVectorInterface;
}
