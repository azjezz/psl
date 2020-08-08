<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The base interface implemented for a collection type that you are able to access its values.
 *
 * Every concrete class indirectly implements this interface.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends CollectionInterface<Tk, Tv>
 * @extends IndexAccessInterface<Tk, Tv>
 */
interface AccessibleCollectionInterface extends CollectionInterface, IndexAccessInterface
{
    /**
     * Returns a `AccessibleCollectionInterface` containing the values of the current
     * `AccessibleCollectionInterface`.
     *
     * @psalm-return AccessibleCollectionInterface<int, Tv>
     */
    public function values(): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the keys of the current `AccessibleCollectionInterface`.
     *
     * @psalm-return AccessibleCollectionInterface<int, Tk>
     */
    public function keys(): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the values of the current `AccessibleCollectionInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `AccessibleCollectionInterface` remain unchanged in the
     * returned `AccessibleCollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `AccessibleCollectionInterface` values
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - a AccessibleCollectionInterface containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the values of the current `AccessibleCollectionInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `AccessibleCollectionInterface` remain unchanged in the
     * returned `AccessibleCollectionInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `AccessibleCollectionInterface` keys and values
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - a `AccessibleCollectionInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `AccessibleCollectionInterface`
     */
    public function filterWithKey(callable $fn): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` after an operation has been applied to each value
     * in the current `AccessibleCollectionInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `AccessibleCollectionInterface` to the
     * returned `AccessibleCollectionInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `AccessibleCollectionInterface` values
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tu> - a `AccessibleCollectionInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` after an operation has been applied to each key and
     * value in the current `AccessibleCollectionInterface`.
     *
     * Every key and value in the current `AccessibleCollectionInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `AccessibleCollectionInterface` to the returned
     * `AccessibleCollectionInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `AccessibleCollectionInterface` keys and values
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tu> - a `AccessibleCollectionInterface` containing the values after a user-specified
     *                        operation on the current `AccessibleCollectionInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): AccessibleCollectionInterface;

    /**
     * Returns the first value in the current `AccessibleCollectionInterface`.
     *
     * @psalm-return null|Tv - The first value in the current `AccessibleCollectionInterface`, or `null` if the
     *           current `AccessibleCollectionInterface` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `AccessibleCollectionInterface`.
     *
     * @psalm-return null|Tk - The first key in the current `AccessibleCollectionInterface`, or `null` if the
     *                  current `AccessibleCollectionInterface` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `AccessibleCollectionInterface`.
     *
     * @psalm-return null|Tv - The last value in the current `AccessibleCollectionInterface`, or `null` if the
     *           current `AccessibleCollectionInterface` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `AccessibleCollectionInterface`.
     *
     * @psalm-return null|Tk - The last key in the current `AccessibleCollectionInterface`, or `null` if the
     *                  current `AccessibleCollectionInterface` is empty
     */
    public function lastKey();

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `AccessibleCollectionInterface`.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value);

    /**
     * Returns a `AccessibleCollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `AccessibleCollectionInterface` and the provided `iterable`.
     *
     * If the number of elements of the `AccessibleCollectionInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `AccessibleCollectionInterface`.
     *
     * @psalm-return AccessibleCollectionInterface<Tk, array{0: Tv, 1: Tu}> - The `AccessibleCollectionInterface` that combines the values of the current
     *           `AccessibleCollectionInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the first `n` values of the current
     * `AccessibleCollectionInterface`.
     *
     * The returned `AccessibleCollectionInterface` will always be a proper subset of the current
     * `AccessibleCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `AccessibleCollectionInterface`
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - A `AccessibleCollectionInterface` that is a proper subset of the current
     *           `AccessibleCollectionInterface` up to `n` elements.
     */
    public function take(int $n): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the values of the current `AccessibleCollectionInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `AccessibleCollectionInterface` will always be a proper subset of the current
     * `AccessibleCollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - A `AccessibleCollectionInterface` that is a proper subset of the current
     *           `AccessibleCollectionInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the values after the `n`-th element of
     * the current `AccessibleCollectionInterface`.
     *
     * The returned `AccessibleCollectionInterface` will always be a proper subset of the current
     * `AccessibleCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `AccessibleCollectionInterface`.
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - A `AccessibleCollectionInterface` that is a proper subset of the current
     *           `AccessibleCollectionInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): AccessibleCollectionInterface;

    /**
     * Returns a `AccessibleCollectionInterface` containing the values of the current `AccessibleCollectionInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `AccessibleCollectionInterface` will always be a proper subset of the current
     * `AccessibleCollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `AccessibleCollectionInterface`.
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - A `AccessibleCollectionInterface` that is a proper subset of the current
     *           `AccessibleCollectionInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): AccessibleCollectionInterface;

    /**
     * Returns a subset of the current `AccessibleCollectionInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `AccessibleCollectionInterface` will always be a proper subset of this
     * `AccessibleCollectionInterface`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `AccessibleCollectionInterface`
     * @psalm-param int $len   - The length of the returned `AccessibleCollectionInterface`
     *
     * @psalm-return AccessibleCollectionInterface<Tk, Tv> - A `AccessibleCollectionInterface` that is a proper subset of the current
     *           `AccessibleCollectionInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): AccessibleCollectionInterface;
}
