<?php

declare(strict_types=1);

namespace Psl\Collection;

use ArrayAccess;
use Closure;

/**
 * The base interface implemented for a collection type whose values you are able to set and remove.
 *
 * Every concrete mutable class indirectly implements this interface.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends AccessibleCollectionInterface<Tk, Tv>
 * @extends MutableCollectionInterface<Tk, Tv>
 * @extends MutableIndexAccessInterface<Tk, Tv>
 * @extends ArrayAccess<Tk, Tv>
 */
interface MutableAccessibleCollectionInterface extends
    AccessibleCollectionInterface,
    ArrayAccess,
    MutableCollectionInterface,
    MutableIndexAccessInterface
{
    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<int<0, max>, Tv>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function values(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the keys of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<int<0, max>, Tk>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function keys(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface` that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`.
     *
     * The keys associated with the current `MutableAccessibleCollectionInterface` remain unchanged in the
     * returned `MutableAccessibleCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `MutableAccessibleCollectionInterface` values.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` containing
     *                                                      the values after a user-specified condition is applied.
     */
    #[\Override]
    public function filter(Closure $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface` that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`.
     *
     * The keys associated with the current `MutableAccessibleCollectionInterface` remain unchanged in the
     * returned `MutableAccessibleCollectionInterface`; the keys will be used in the filtering process only.
     *
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `MutableAccessibleCollectionInterface` keys and values.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` containing
     *                                                      the values after a user-specified condition is applied
     *                                                      to the keys and values of the current
     *                                                      `MutableAccessibleCollectionInterface`.
     */
    #[\Override]
    public function filterWithKey(Closure $fn): MutableAccessibleCollectionInterface;

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
     * @param Tk $k The key to remove.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> Returns itself.
     */
    #[\Override]
    public function remove(int|string $k): MutableAccessibleCollectionInterface;

    /**
     * Removes all elements from the collection.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv>
     */
    #[\Override]
    public function clear(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableAccessibleCollectionInterface` and the provided elements.
     *
     * If the number of elements of the `MutableAccessibleCollectionInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements up to and including
     * the final element of the one with the least number of elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<Tk, array{0: Tv, 1: Tu}> The `MutableAccessibleCollectionInterface`
     *                                                                       that combines the values of the current
     *                                                                       `MutableAccessibleCollectionInterface` with
     *                                                                       the provided elements.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function zip(array $elements): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the first `n` values of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` that is a proper
     *                                                      subset of the current `MutableAccessibleCollectionInterface`
     *                                                      up to `n` elements.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function take(int $n): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface` up to but not including the first value
     * that produces `false` when passed to the specified callback.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` that is a proper
     *                                                      subset of the current `MutableAccessibleCollectionInterface`
     *                                                      up until the callback returns `false`.
     */
    #[\Override]
    public function takeWhile(Closure $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values after the `n`-th element of
     * the current `MutableAccessibleCollectionInterface`.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` that is a proper
     *                                                      subset of the current `MutableAccessibleCollectionInterface`
     *                                                      containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function drop(int $n): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface` starting after and including the first value
     * that produces `true` when passed to the specified callback.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` that is a proper
     *                                                      subset of the current `MutableAccessibleCollectionInterface`
     *                                                      starting after the callback returns `true`.
     */
    #[\Override]
    public function dropWhile(Closure $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a subset of the current `MutableAccessibleCollectionInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of this
     * `MutableAccessibleCollectionInterface`.
     *
     * @param int<0, max> $start The starting key of this Vector to begin the returned `MutableAccessibleCollectionInterface`
     * @param null|int<0, max> $length The length of the returned `MutableAccessibleCollectionInterface`
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> A `MutableAccessibleCollectionInterface` that is a proper
     *                                                      subset of the current `MutableAccessibleCollectionInterface`
     *                                                      starting at `$start` up to but not including the element
     *                                                      `$start + $length`.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function slice(int $start, null|int $length = null): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the original `MutableAccessibleCollectionInterface` split into
     * chunks of the given size.
     *
     * If the original `MutableAccessibleCollectionInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return MutableAccessibleCollectionInterface<int<0, max>, static<Tk, Tv>> A `MutableAccessibleCollectionInterface` containing the original
     *                                                                           `MutableAccessibleCollectionInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function chunk(int $size): MutableAccessibleCollectionInterface;

    /**
     * Determines if the specified offset exists in the current collection.
     *
     * @param mixed $offset An offset to check for.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     *
     * @return bool Returns true if the specified offset exists, false otherwise.
     *
     * @psalm-assert-if-true Tk $offset
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function offsetExists(mixed $offset): bool;

    /**
     * Returns the value at the specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     * @throws Exception\OutOfBoundsException If the offset is out-of-bounds.
     *
     * @return Tv The value at the specified offset.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function offsetGet(mixed $offset): mixed;

    /**
     * Sets the value at the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param Tv $value The value to set.
     *
     * @psalm-external-mutation-free
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     */
    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void;

    /**
     * Unsets the value at the specified offset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @psalm-external-mutation-free
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     */
    #[\Override]
    public function offsetUnset(mixed $offset): void;
}
