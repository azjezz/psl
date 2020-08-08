<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends AccessibleCollectionInterface<Tk, Tv>
 */
interface MapInterface extends AccessibleCollectionInterface
{
    /**
     * Returns a `VectorInterface` containing the values of the current
     * `MapInterface`.
     *
     * @psalm-return VectorInterface<Tv>
     */
    public function values(): VectorInterface;

    /**
     * Returns a `VectorInterface` containing the keys of the current `MapInterface`.
     *
     * @psalm-return VectorInterface<Tk>
     */
    public function keys(): VectorInterface;

    /**
     * Returns a `MapInterface` containing the values of the current `MapInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MapInterface` remain unchanged in the
     * returned `MapInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `MapInterface` values
     *
     * @psalm-return MapInterface<Tk, Tv> - a MapInterface containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): MapInterface;

    /**
     * Returns a `MapInterface` containing the values of the current `MapInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MapInterface` remain unchanged in the
     * returned `MapInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `MapInterface` keys and values
     *
     * @psalm-return MapInterface<Tk, Tv> - a `MapInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `MapInterface`
     */
    public function filterWithKey(callable $fn): MapInterface;

    /**
     * Returns a `MapInterface` after an operation has been applied to each value
     * in the current `MapInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MapInterface` to the
     * returned `MapInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `MapInterface` values
     *
     * @psalm-return MapInterface<Tk, Tu> - a `MapInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): MapInterface;

    /**
     * Returns a `MapInterface` after an operation has been applied to each key and
     * value in the current `MapInterface`.
     *
     * Every key and value in the current `MapInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MapInterface` to the returned
     * `MapInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `MapInterface` keys and values
     *
     * @psalm-return MapInterface<Tk, Tu> - a `MapInterface` containing the values after a user-specified
     *                        operation on the current `MapInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): MapInterface;

    /**
     * Returns the first value in the current `MapInterface`.
     *
     * @psalm-return null|Tv - The first value in the current `MapInterface`, or `null` if the
     *           current `MapInterface` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `MapInterface`.
     *
     * @psalm-return null|Tk - The first key in the current `MapInterface`, or `null` if the
     *                  current `MapInterface` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `MapInterface`.
     *
     * @psalm-return null|Tv - The last value in the current `MapInterface`, or `null` if the
     *           current `MapInterface` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `MapInterface`.
     *
     * @psalm-return null|Tk - The last key in the current `MapInterface`, or `null` if the
     *                  current `MapInterface` is empty
     */
    public function lastKey();

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `MapInterface`.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value);

    /**
     * Returns a `MapInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MapInterface` and the provided `iterable`.
     *
     * If the number of elements of the `MapInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `MapInterface`.
     *
     * @psalm-return MapInterface<Tk, array{0: Tv, 1: Tu}> - The `MapInterface` that combines the values of the current
     *           `MapInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): MapInterface;

    /**
     * Returns a `MapInterface` containing the first `n` values of the current
     * `MapInterface`.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `MapInterface`
     *
     * @psalm-return MapInterface<Tk, Tv> - A `MapInterface` that is a proper subset of the current
     *           `MapInterface` up to `n` elements.
     */
    public function take(int $n): MapInterface;

    /**
     * Returns a `MapInterface` containing the values of the current `MapInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return MapInterface<Tk, Tv> - A `MapInterface` that is a proper subset of the current
     *           `MapInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MapInterface;

    /**
     * Returns a `MapInterface` containing the values after the `n`-th element of
     * the current `MapInterface`.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `MapInterface`.
     *
     * @psalm-return MapInterface<Tk, Tv> - A `MapInterface` that is a proper subset of the current
     *           `MapInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): MapInterface;

    /**
     * Returns a `MapInterface` containing the values of the current `MapInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `MapInterface`.
     *
     * @psalm-return MapInterface<Tk, Tv> - A `MapInterface` that is a proper subset of the current
     *           `MapInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MapInterface;

    /**
     * Returns a subset of the current `MapInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MapInterface` will always be a proper subset of this
     * `MapInterface`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `MapInterface`
     * @psalm-param int $len   - The length of the returned `MapInterface`
     *
     * @psalm-return MapInterface<Tk, Tv> - A `MapInterface` that is a proper subset of the current
     *           `MapInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): MapInterface;
}
