<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;

/**
 * MutableCollectionInterface is the primary collection interface for mutable collections.
 *
 * Assuming you want the ability to clear out your collection, you would implement this (or a child of this)
 * interface.
 *
 * If your collection to be immutable, implement Collection only instead.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends CollectionInterface<Tk, Tv>
 */
interface MutableCollectionInterface extends CollectionInterface
{
    /**
     * Returns a `MutableCollectionInterface` containing the values of the current `MutableCollectionInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableCollectionInterface` remain unchanged in the
     * returned `MutableCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `MutableCollectionInterface` values.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` containing the values
     *                                            after a user-specified condition is applied.
     */
    public function filter(Closure $fn): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` containing the values of the current `MutableCollectionInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableCollectionInterface` remain unchanged in the
     * returned `MutableCollectionInterface`; the keys will be used in the filtering process only.
     *
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `MutableCollectionInterface` keys and values.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` containing the values after
     *                                            a user-specified condition is applied to the keys and values of
     *                                            the current `MutableCollectionInterface`.
     */
    public function filterWithKey(Closure $fn): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` after an operation has been applied to each value
     * in the current `MutableCollectionInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableCollectionInterface` to the
     * returned `MutableCollectionInterface`.
     *
     * @template Tu
     *
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `MutableCollectionInterface` values.
     *
     * @return MutableCollectionInterface<Tk, Tu> A `MutableCollectionInterface` containing key/value pairs
     *                                            after a user-specified operation is applied.
     */
    public function map(Closure $fn): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` after an operation has been applied to each key and
     * value in the current `MutableCollectionInterface`.
     *
     * Every key and value in the current `MutableCollectionInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableCollectionInterface` to the returned
     * `MutableCollectionInterface`. The keys are only used to help in the mapping operation.
     *
     * @template Tu
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `MutableCollectionInterface` keys and values.
     *
     * @return MutableCollectionInterface<Tk, Tu> A `MutableCollectionInterface` containing the values
     *                                            after a user-specified operation on the current
     *                                            `MutableCollectionInterface`'s keys and values is applied.
     */
    public function mapWithKey(Closure $fn): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableCollectionInterface` and the provided elements.
     *
     * If the number of elements of the `MutableCollectionInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the
     *                                       elements of this `MutableCollectionInterface`.
     *
     * @return MutableCollectionInterface<Tk, array{0: Tv, 1: Tu}> The `MutableCollectionInterface` that
     *                                                             combines the values of the
     *                                                             current `MutableCollectionInterface` with
     *                                                             the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` containing the first `n` values of the current
     * `MutableCollectionInterface`.
     *
     * The returned `MutableCollectionInterface` will always be a proper subset of the current
     * `MutableCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned `MutableCollectionInterface`.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` that is a proper
     *                                            subset of the current `MutableCollectionInterface` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    public function take(int $n): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` containing the values of the current `MutableCollectionInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableCollectionInterface` will always be a proper subset of the current
     * `MutableCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` that is a proper
     *                                            subset of the current `MutableCollectionInterface` up until
     *                                            the callback returns `false`.
     */
    public function takeWhile(Closure $fn): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` containing the values after the `n`-th element of
     * the current `MutableCollectionInterface`.
     *
     * The returned `MutableCollectionInterface` will always be a proper subset of the current
     * `MutableCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableCollectionInterface`.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` that is a proper
     *                                            subset of the current `MutableCollectionInterface` containing values
     *                                            after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    public function drop(int $n): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` containing the values of the current `MutableCollectionInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableCollectionInterface` will always be a proper subset of the current
     * `MutableCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `MutableCollectionInterface`.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` that is a proper subset of the current
     *                                            `MutableCollectionInterface` starting after the callback
     *                                            returns `true`.
     */
    public function dropWhile(Closure $fn): MutableCollectionInterface;

    /**
     * Returns a subset of the current `MutableCollectionInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableCollectionInterface` will always be a proper subset of this
     * `MutableCollectionInterface`.
     *
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `MutableCollectionInterface`.
     * @param null|int<0, max> $length The length of the returned `MutableCollectionInterface`.
     *
     * @return MutableCollectionInterface<Tk, Tv> A `MutableCollectionInterface` that is a proper
     *                                            subset of the current `MutableCollectionInterface` starting
     *                                            at `$start` up to but not including the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): MutableCollectionInterface;

    /**
     * Returns a `MutableCollectionInterface` containing the original `MutableCollectionInterface` split into
     * chunks of the given size.
     *
     * If the original `MutableCollectionInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return MutableCollectionInterface<int<0, max>, static<Tk, Tv>> A `MutableCollectionInterface` containing the original
     *                                                                 `MutableCollectionInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): MutableCollectionInterface;

    /**
     * Removes all elements from the collection.
     *
     * @return MutableCollectionInterface<Tk, Tv>
     */
    public function clear(): MutableCollectionInterface;
}
