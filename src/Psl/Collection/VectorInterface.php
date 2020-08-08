<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template T
 *
 * @extends  AccessibleCollectionInterface<int, T>
 */
interface VectorInterface extends AccessibleCollectionInterface
{
    /**
     * Returns the value at the specified key in the current vector.
     *
     * @psalm-param  int $k
     *
     * @psalm-return T
     */
    public function at($k);

    /**
     * Determines if the specified key is in the current vector.
     *
     * @psalm-param int $k
     */
    public function contains($k): bool;

    /**
     * Returns the value at the specified key in the current vector.
     *
     * @psalm-param  int $k
     *
     * @psalm-return T|null
     */
    public function get($k);

    /**
     * Get an array copy of the current vector.
     *
     * @psalm-return list<T>
     */
    public function toArray(): array;

    /**
     * Returns a `VectorInterface` containing the values of the current
     * `VectorInterface`.
     *
     * @psalm-return VectorInterface<T>
     */
    public function values(): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the keys of the current `VectorInterface`.
     *
     * @psalm-return VectorInterface<int>
     */
    public function keys(): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the values of the current `VectorInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `VectorInterface` remain unchanged in the
     * returned `VectorInterface`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback containing the condition to apply to the current
     *                                 `VectorInterface` values
     *
     * @psalm-return VectorInterface<T> - a VectorInterface containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the values of the current `VectorInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `VectorInterface` remain unchanged in the
     * returned `VectorInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(int, T): bool) $fn - The callback containing the condition to apply to the current
     *                                     `VectorInterface` keys and values
     *
     * @psalm-return VectorInterface<T> - a `VectorInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `VectorInterface`
     */
    public function filterWithKey(callable $fn): VectorInterface;

    /**
     * Returns a `VectorInterface` after an operation has been applied to each value
     * in the current `VectorInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `VectorInterface` to the
     * returned `VectorInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(T): Tu) $fn - The callback containing the operation to apply to the current
     *                               `VectorInterface` values
     *
     * @psalm-return   VectorInterface<Tu> - a `VectorInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): VectorInterface;

    /**
     * Returns a `VectorInterface` after an operation has been applied to each key and
     * value in the current `VectorInterface`.
     *
     * Every key and value in the current `VectorInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `VectorInterface` to the returned
     * `VectorInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, T): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `VectorInterface` keys and values
     *
     * @psalm-return   VectorInterface<Tu> - a `VectorInterface` containing the values after a user-specified
     *                        operation on the current `VectorInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): VectorInterface;

    /**
     * Returns the first value in the current `VectorInterface`.
     *
     * @psalm-return null|T - The first value in the current `VectorInterface`, or `null` if the
     *           current `VectorInterface` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `VectorInterface`.
     *
     * @psalm-return null|int - The first key in the current `VectorInterface`, or `null` if the
     *                  current `VectorInterface` is empty
     */
    public function firstKey(): ?int;

    /**
     * Returns the last value in the current `VectorInterface`.
     *
     * @psalm-return null|T - The last value in the current `VectorInterface`, or `null` if the
     *           current `VectorInterface` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `VectorInterface`.
     *
     * @psalm-return null|int - The last key in the current `VectorInterface`, or `null` if the
     *                  current `VectorInterface` is empty
     */
    public function lastKey(): ?int;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param  T $search_value - The value that will be search for in the current
     *                        `VectorInterface`.
     *
     * @psalm-return null|int - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int;

    /**
     * Returns a `VectorInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `VectorInterface` and the provided `iterable`.
     *
     * If the number of elements of the `VectorInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `VectorInterface`.
     *
     * @psalm-return   VectorInterface<array{0: T, 1: Tu}> - The `VectorInterface` that combines the values of the current
     *           `VectorInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the first `n` values of the current
     * `VectorInterface`.
     *
     * The returned `VectorInterface` will always be a proper subset of the current
     * `VectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `VectorInterface`
     *
     * @psalm-return VectorInterface<T> - A `VectorInterface` that is a proper subset of the current
     *           `VectorInterface` up to `n` elements.
     */
    public function take(int $n): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the values of the current `VectorInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `VectorInterface` will always be a proper subset of the current
     * `VectorInterface`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return VectorInterface<T> - A `VectorInterface` that is a proper subset of the current
     *           `VectorInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the values after the `n`-th element of
     * the current `VectorInterface`.
     *
     * The returned `VectorInterface` will always be a proper subset of the current
     * `VectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `VectorInterface`.
     *
     * @psalm-return VectorInterface<T> - A `VectorInterface` that is a proper subset of the current
     *           `VectorInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the values of the current `VectorInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `VectorInterface` will always be a proper subset of the current
     * `VectorInterface`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback used to determine the starting element for the
     *              returned `VectorInterface`.
     *
     * @psalm-return VectorInterface<T> - A `VectorInterface` that is a proper subset of the current
     *           `VectorInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): VectorInterface;

    /**
     * Returns a subset of the current `VectorInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `VectorInterface` will always be a proper subset of this
     * `VectorInterface`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `VectorInterface`
     * @psalm-param  int $len   - The length of the returned `VectorInterface`
     *
     * @psalm-return VectorInterface<T> - A `VectorInterface` that is a proper subset of the current
     *           `VectorInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): VectorInterface;
}
