<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;

/**
 * @template T of array-key
 *
 * @extends AccessibleCollectionInterface<T, T>
 */
interface SetInterface extends AccessibleCollectionInterface
{
    /**
     * Returns the provided value if it exists in the current `SetInterface`.
     *
     * As {@see SetInterface} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it is returned to indicate presence in the set. If the value does not exist,
     * an {@see Exception\OutOfBoundsException} is thrown to indicate the absence of the value.
     *
     * @param T $k
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function at(int|string $k): int|string;

    /**
     * Determines if the specified value is in the current set.
     *
     * As {@see SetInterface} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it returns true to indicate presence in the set. If the value does not exist,
     * it returns false to indicate the absence of the value.
     *
     * @param T $k
     *
     * @return bool True if the value is in the set, false otherwise.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function contains(int|string $k): bool;

    /**
     * Returns the provided value if it is part of the set, or null if it is not.
     *
     * As {@see SetInterface} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it is returned to indicate presence in the set. If the value does not exist,
     * null is returned to indicate the absence of the value.
     *
     * @param T $k
     *
     * @return T|null
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function get(int|string $k): null|int|string;

    /**
     * Get an array copy of the current set.
     *
     * @return array<T, T>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function toArray(): array;

    /**
     * Returns a `VectorInterface` containing the values of the current `SetInterface`.
     *
     * @return VectorInterface<T>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function values(): VectorInterface;

    /**
     * As {@see SetInterface} does not have keys, this method acts as an alias for {@see SetInterface::values()}.
     *
     * @return VectorInterface<T>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function keys(): VectorInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `SetInterface` values.
     *
     * @return SetInterface<T> A SetInterface containing the values after a user-specified condition
     *                         is applied.
     */
    #[\Override]
    public function filter(Closure $fn): SetInterface;

    /**
     * Applies a user-defined condition to each value in the `SetInterface`,
     *  considering the value as both key and value.
     *
     * This method extends {@see SetInterface::filter()} by providing the value twice to the
     *  callback function: once as the "key" and once as the "value", even though {@see SetInterface} do not have traditional key-value pairs.
     *
     * This allows for filtering based on both the value's "key" and "value" representation, which are identical.
     * It's particularly useful when the distinction between keys and values is relevant for the condition.
     *
     * @param (Closure(T, T): bool) $fn T
     *
     * @return SetInterface<T>
     */
    #[\Override]
    public function filterWithKey(Closure $fn): SetInterface;

    /**
     * Returns a `SetInterface` after an operation has been applied to each value
     * in the current `SetInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * @template Tu of array-key
     *
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `SetInterface` values.
     *
     * @return SetInterface<Tu> A `SetInterface` containing key/value pairs after a user-specified
     *                          operation is applied.
     */
    public function map(Closure $fn): SetInterface;

    /**
     * Transform the values of the current `SetInterface` by applying the provided callback,
     *  considering the value as both key and value.
     *
     * Similar to {@see SetInterface::map()}, this method extends the functionality by providing the value twice to the
     *  callback function: once as the "key" and once as the "value",
     *
     * The allows for transformations that take into account the value's dual role. It's useful for operations where the distinction
     *  between keys and values is relevant.
     *
     * @template Tu of array-key
     *
     * @param (Closure(T, T): Tu) $fn
     *
     * @return SetInterface<Tu>
     */
    public function mapWithKey(Closure $fn): SetInterface;

    /**
     * Returns the first value in the current `SetInterface`.
     *
     * @return T|null The first value in the current `SetInterface`, or `null` if the
     *                current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function first(): null|int|string;

    /**
     * Returns the first key in the current `SetInterface`.
     *
     * As {@see SetInterface} does not have keys, this method acts as an alias for {@see SetInterface::first()}.
     *
     * @return T|null The first value in the current `SetInterface`, or `null` if the
     *                current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function firstKey(): null|int|string;

    /**
     * Returns the last value in the current `SetInterface`.
     *
     * @return T|null The last value in the current `SetInterface`, or `null` if the
     *                current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function last(): null|int|string;

    /**
     * Returns the last key in the current `SetInterface`.
     *
     * As {@see SetInterface} does not have keys, this method acts as an alias for {@see SetInterface::last()}.
     *
     * @return T|null The last value in the current `SetInterface`, or `null` if the
     *                current `SetInterface` is empty.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function lastKey(): null|int|string;

    /**
     * Returns the key of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * As {@see SetInterface} does not have keys, this method returns the value itself.
     *
     * @param T $search_value The value that will be search for in the current
     *                        `SetInterface`.
     *
     * @return T|null The value if its found, null otherwise.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function linearSearch(mixed $search_value): null|int|string;

    /**
     * Always throws an exception since `Set` can only contain array-key values.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `SetInterface`.
     *
     * @psalm-mutation-free
     *
     * @throws Exception\RuntimeException Always throws an exception since `Set` can only contain array-key values.
     */
    #[\Override]
    public function zip(array $elements): never;

    /**
     * Returns a `SetInterface` containing the first `n` values of the current
     * `SetInterface`.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function take(int $n): SetInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` up until the callback returns `false`.
     */
    #[\Override]
    public function takeWhile(Closure $fn): SetInterface;

    /**
     * Returns a `SetInterface` containing the values after the `n`-th element of
     * the current `SetInterface`.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function drop(int $n): SetInterface;

    /**
     * Returns a `SetInterface` containing the values of the current `SetInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `SetInterface` will always be a proper subset of the current
     * `SetInterface`.
     *
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` starting after the callback returns `true`.
     */
    #[\Override]
    public function dropWhile(Closure $fn): SetInterface;

    /**
     * Returns a subset of the current `SetInterface` starting from a given index up
     * to, but not including, the element at the provided length from the starting
     * index.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at index 0 and 1.
     *
     * The returned `SetInterface` will always be a proper subset of this
     * `SetInterface`.
     *
     * @param int<0, max> $start The starting index of this set to begin the returned
     *                           `SetInterface`.
     * @param int<0, max> $length The length of the returned `SetInterface`.
     *
     * @return SetInterface<T> A `SetInterface` that is a proper subset of the current
     *                         `SetInterface` starting at `$start` up to but not including
     *                         the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function slice(int $start, null|int $length = null): SetInterface;

    /**
     * Returns a `VectorInterface` containing the original `SetInterface` split into
     * chunks of the given size.
     *
     * If the original `SetInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return VectorInterface<static<T>> A `VectorInterface` containing the original
     *                                    `SetInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function chunk(int $size): VectorInterface;
}
