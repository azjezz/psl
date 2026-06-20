<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
use Override;

/**
 * MutableCollectionInterface is the primary collection interface for mutable collections.
 *
 * Assuming you want the ability to clear out your collection, you would implement this (or a child of this)
 * interface.
 *
 * If your collection to be immutable, implement Collection only instead.
 *
 * @api
 */
interface MutableCollectionInterface<Tk: string|int, Tv> extends CollectionInterface<Tk, Tv>
{
    /**
     * Returns a `MutableCollectionInterface` containing the values of the current `MutableCollectionInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`.
     *
     * The keys associated with the current `MutableCollectionInterface` remain unchanged in the
     * returned `MutableCollectionInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `MutableCollectionInterface` values.
     */
    #[Override]
    public function filter(Closure $fn): MutableCollectionInterface<Tk, Tv>;

    /**
     * Returns a `MutableCollectionInterface` containing the values of the current `MutableCollectionInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`.
     *
     * The keys associated with the current `MutableCollectionInterface` remain unchanged in the
     * returned `MutableCollectionInterface`; the keys will be used in the filtering process only.
     *
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `MutableCollectionInterface` keys and values.
     */
    #[Override]
    public function filterWithKey(Closure $fn): MutableCollectionInterface<Tk, Tv>;

    /**
     * Returns a `MutableCollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableCollectionInterface` and the provided elements.
     *
     * If the number of elements of the `MutableCollectionInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
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
    #[Override]
    public function zip<Tu>(array $elements): MutableCollectionInterface<Tk, array>;

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
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MutableCollectionInterface<Tk, Tv>;

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
     */
    #[Override]
    public function takeWhile(Closure $fn): MutableCollectionInterface<Tk, Tv>;

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
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MutableCollectionInterface<Tk, Tv>;

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
     */
    #[Override]
    public function dropWhile(Closure $fn): MutableCollectionInterface<Tk, Tv>;

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
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MutableCollectionInterface<Tk, Tv>;

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
    #[Override]
    public function chunk(int $size): MutableCollectionInterface<int, MutableCollectionInterface<Tk, Tv>>;

    /**
     * Removes all elements from the collection.
     */
    public function clear(): MutableCollectionInterface<Tk, Tv>;
}
