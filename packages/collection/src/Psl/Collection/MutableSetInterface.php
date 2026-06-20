<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
use Override;

/**
 * @api
 */
interface MutableSetInterface<T: string|int> extends MutableAccessibleCollectionInterface<T, T>, SetInterface<T>
{
    /**
     * Returns the provided value if it exists in the current `MutableSetInterface`.
     *
     * As {@see MutableSetInterface} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it is returned to indicate presence in the set. If the value does not exist,
     * an {@see Exception\OutOfBoundsException} is thrown to indicate the absence of the value.
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function at(T $k): T;

    /**
     * Determines if the specified value is in the current set.
     *
     * As {@see MutableSetInterface} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it returns true to indicate presence in the set. If the value does not exist,
     * it returns false to indicate the absence of the value.
     *
     * @return bool True if the value is in the set, false otherwise.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function contains(T $k): bool;

    /**
     * Returns the provided value if it is part of the set, or null if it is not.
     *
     * As {@see MutableSetInterface} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it is returned to indicate presence in the set. If the value does not exist,
     * null is returned to indicate the absence of the value.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function get(T $k): T|null;

    /**
     * Get an array copy of the current set.
     *
     * @return array<T, T>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function toArray(): array;

    /**
     * Returns a `MutableVectorInterface` containing the values of the current `MutableSetInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): MutableVectorInterface<T>;

    /**
     * As {@see MutableSetInterface} does not have keys, this method acts as an alias for {@see MutableSetInterface::values()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): MutableVectorInterface<T>;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `MutableSetInterface` values
     */
    #[Override]
    public function filter(Closure $fn): MutableSetInterface<T>;

    /**
     * Applies a user-defined condition to each value in the `MutableSetInterface`,
     *  considering the value as both key and value.
     *
     * This method extends {@see MutableSetInterface::filter()} by providing the value twice to the
     *  callback function: once as the "key" and once as the "value", even though {@see MutableSetInterface} do not have traditional key-value pairs.
     *
     * This allows for filtering based on both the value's "key" and "value" representation, which are identical.
     * It's particularly useful when the distinction between keys and values is relevant for the condition.
     *
     * @param (Closure(T, T): bool) $fn T
     */
    #[Override]
    public function filterWithKey(Closure $fn): MutableSetInterface<T>;

    /**
     * Returns a `MutableSetInterface` after an operation has been applied to each value
     * in the current `MutableSetInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `MutableSetInterface` values
     */
    #[Override]
    public function map<Tu: string|int>(Closure $fn): MutableSetInterface<Tu>;

    /**
     * Transform the values of the current `MutableSetInterface` by applying the provided callback,
     *  considering the value as both key and value.
     *
     * Similar to {@see MutableSetInterface::map()}, this method extends the functionality by providing the value twice to the
     *  callback function: once as the "key" and once as the "value",
     *
     * The allows for transformations that take into account the value's dual role. It's useful for operations where the distinction
     *  between keys and values is relevant.
     *
     * @param (Closure(T, T): Tu) $fn
     */
    #[Override]
    public function mapWithKey<Tu: string|int>(Closure $fn): MutableSetInterface<Tu>;

    /**
     * Returns the first value in the current `MutableSetInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): T|null;

    /**
     * Returns the first key in the current `MutableSetInterface`.
     *
     * As {@see MutableSetInterface} does not have keys, this method acts as an alias for {@see MutableSetInterface::first()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function firstKey(): T|null;

    /**
     * Returns the last value in the current `MutableSetInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function last(): T|null;

    /**
     * Returns the last key in the current `MutableSetInterface`.
     *
     * As {@see MutableSetInterface} does not have keys, this method acts as an alias for {@see MutableSetInterface::last()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function lastKey(): T|null;

    /**
     * Returns the key of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * As {@see MutableSetInterface} does not have keys, this method returns the value itself.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function linearSearch(T $searchValue): T|null;

    /**
     * Always throws an exception since `Set` can only contain array-key values.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `SetInterface`.
     *
     * @psalm-mutation-free
     *
     * @throws Exception\RuntimeException Always throws an exception since `Set` can only contain array-key values.
     */
    #[Override]
    public function zip<Tu>(array $elements): never;

    /**
     * Returns a `MutableSetInterface` containing the first `n` values of the current
     * `MutableSetInterface`.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `MutableSetInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MutableSetInterface<T>;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): MutableSetInterface<T>;

    /**
     * Returns a `MutableSetInterface` containing the values after the `n`-th element of
     * the current `MutableSetInterface`.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableSetInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MutableSetInterface<T>;

    /**
     * Returns a `MutableSetInterface` containing the values of the current `MutableSetInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableSetInterface` will always be a proper subset of the current
     * `MutableSetInterface`.
     *
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `MutableSetInterface`.
     */
    #[Override]
    public function dropWhile(Closure $fn): MutableSetInterface<T>;

    /**
     * Returns a subset of the current `MutableSetInterface` starting from a given index up
     * to, but not including, the element at the provided length from the starting
     * index.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at index 0 and 1.
     *
     * The returned `MutableSetInterface` will always be a proper subset of this
     * `MutableSetInterface`.
     *
     * @param int<0, max> $start The starting index of this set to begin the returned
     *                           `MutableSetInterface`.
     * @param int<0, max> $length The length of the returned `MutableSetInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MutableSetInterface<T>;

    /**
     * Returns a `MutableVectorInterface` containing the original `MutableSetInterface` split into
     * chunks of the given size.
     *
     * If the original `MutableSetInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return MutableVectorInterface<static<T>> A `MutableVectorInterface` containing the original
     *                                           `MutableSetInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): MutableVectorInterface<MutableSetInterface<T>>;

    /**
     * Removes the specified value from the current set.
     *
     * If the value is not in the current set, the current set is unchanged.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    #[Override]
    public function remove(T $k): MutableSetInterface<T>;

    /**
     * Removes all elements from the set.
     */
    #[Override]
    public function clear(): MutableSetInterface<T>;

    /**
     * Add a value to the set and return the set itself.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function add(T $v): MutableSetInterface<T>;

    /**
     * For every element in the provided elements iterable, add the value into the current set.
     *
     * @param iterable<T> $elements The elements with the new values to add.
     *
     * @return MutableSetInterface<T> Returns itself.
     */
    public function addAll(iterable $elements): MutableSetInterface<T>;
}
