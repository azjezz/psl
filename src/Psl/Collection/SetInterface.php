<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template T
 *
 * @extends VectorInterface<T>
 */
interface SetInterface extends VectorInterface
{
    /**
     * Returns the value at the specified key in the current vector.
     *
     * @param int $k
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function at(string|int $k): mixed;

    /**
     * Determines if the specified key is in the current vector.
     *
     * @param int $k
     *
     * @psalm-mutation-free
     */
    public function contains(int|string $k): bool;

    /**
     * Returns the value at the specified key in the current vector.
     *
     * @param int $k
     *
     * @return T|null
     *
     * @psalm-mutation-free
     */
    public function get(string|int $k): mixed;

    /**
     * Get an array copy of the current vector.
     *
     * @return list<T>
     *
     * @psalm-mutation-free
     */
    public function toArray(): array;

    /**
     * Returns a `SetInterface` containing the values of the current
     * `SetInterface`.
     *
     * @return SetInterface<T>
     *
     * @psalm-mutation-free
     */
    public function values(): SetInterface;

    /**
     * Returns a `SetInterface` containing the keys of the current `SetInterface`.
     *
     * @return SetInterface<int>
     *
     * @psalm-mutation-free
     */
    public function keys(): SetInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `SetInterface` remain unchanged in the
     * returned `SetInterface`.
     *
     * @param (callable(T): bool) $fn The callback containing the condition to apply to the current
     *                                `SetInterface` values.
     *
     * @return SetInterface<T> A SetInterface containing the values after a user-specified condition
     *                         is applied.
     */
    public function filter(callable $fn): SetInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `SetInterface` remain unchanged in the
     * returned `SetInterface`; the keys will be used in the filtering process only.
     *
     * @param (callable(int, T): bool) $fn The callback containing the condition to apply to the current
     *                                     `SetInterface` keys and values.
     *
     * @return SetInterface<T> A `SetInterface` containing the values after a user-specified
     *                         condition is applied to the keys and values of the current `SetInterface`.
     */
    public function filterWithKey(callable $fn): SetInterface;

    /**
     * Returns a `VectorInterface` after an operation has been applied to each value
     * in the current `SetInterface`.
     *
     * Every value in the current `SetInterface` is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `SetInterface` to the
     * returned `VectorInterface`.
     *
     * @template Tu
     *
     * @param (callable(T): Tu) $fn The callback containing the operation to apply to the current
     *                              `SetInterface` values.
     *
     * @return VectorInterface<Tu> A `VectorInterface` containing key/value pairs after a user-specified
     *                             operation is applied.
     */
    public function map(callable $fn): VectorInterface;

    /**
     * Returns a `VectorInterface` after an operation has been applied to each key and
     * value in the current `SetInterface`.
     *
     * Every key and value in the current `SetInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `SetInterface` to the returned
     * `VectorInterface`. The keys are only used to help in the mapping operation.
     *
     * @template Tu
     *
     * @param (callable(int, T): Tu) $fn The callback containing the operation to apply to the current
     *                                   `SetInterface` keys and values.
     *
     * @return VectorInterface<Tu> A `VectorInterface` containing the values after a user-specified
     *                             operation on the current `SetInterface`'s keys and values is applied.
     */
    public function mapWithKey(callable $fn): VectorInterface;

    /**
     * Returns the first value in the current `SetInterface`.
     *
     * @return T|null The first value in the current `SetInterface`, or `null` if the
     *                current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function first(): mixed;

    /**
     * Returns the first key in the current `SetInterface`.
     *
     * @return int|null The first key in the current `SetInterface`, or `null` if the
     *                  current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `SetInterface`.
     *
     * @return T|null The last value in the current `SetInterface`, or `null` if the
     *                current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function last(): mixed;

    /**
     * Returns the last key in the current `SetInterface`.
     *
     * @return int|null The last key in the current `SetInterface`, or `null` if the
     *                  current `SetInterface` is empty.
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
     *                        `SetInterface`.
     *
     * @return int|null The key (index) where that value is found; null if it is not found
     *
     * @psalm-mutation-free
     */
    public function linearSearch(mixed $search_value): ?int;

    /**
     * Returns a `VectorInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `SetInterface` and the provided `iterable`.
     *
     * If the number of elements of the `SetInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param iterable<Tu> $iterable The `iterable` to use to combine with the
     *                               elements of this `SetInterface`.
     *
     * @return VectorInterface<array{0: T, 1: Tu}> The `VectorInterface` that combines the values
     *                                             of the current `SetInterface` with the provided `iterable`.
     *
     * @psalm-mutation-free
     */
    public function zip(iterable $iterable): VectorInterface;

    /**
     * Returns a `SetInterface` containing the first `n` values of the current
     * `SetInterface`.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int $n The last element that will be included in the returned
     *               `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    public function take(int $n): SetInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * @param (callable(T): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): SetInterface;

    /**
     * Returns a `SetInterface` containing the values after the `n`-th element of
     * the current `SetInterface`.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int $n The last element to be skipped; the $n+1 element will be the
     *               first one in the returned `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    public function drop(int $n): SetInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * @param (callable(T): bool) $fn The callback used to determine the starting element for the
     *                                returned `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): SetInterface;

    /**
     * Returns a subset of the current `SetInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `SetInterface` will always be a proper subset of this
     * `SetInterface`.
     *
     * @param int $start The starting key of this Vector to begin the returned
     *                   `SetInterface`.
     * @param int $length The length of the returned `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` starting at `$start` up to but not including
     *                         the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, int $length): SetInterface;
}
