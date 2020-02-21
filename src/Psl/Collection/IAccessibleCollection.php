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
 * @extends ICollection<Tk, Tv>
 * @extends IIndexAccess<Tk, Tv>
 */
interface IAccessibleCollection extends ICollection, IIndexAccess
{
    /**
     * Returns a `IAccessibleCollection` containing the values of the current
     * `IAccessibleCollection`.
     *
     * @psalm-return IAccessibleCollection<int, Tv>
     */
    public function values(): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the keys of the current `IAccessibleCollection`.
     *
     * @psalm-return IAccessibleCollection<int, Tk>
     */
    public function keys(): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the values of the current `IAccessibleCollection`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `IAccessibleCollection` remain unchanged in the
     * returned `IAccessibleCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `IAccessibleCollection` values
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - a IAccessibleCollection containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the values of the current `IAccessibleCollection`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `IAccessibleCollection` remain unchanged in the
     * returned `IAccessibleCollection`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `IAccessibleCollection` keys and values
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - a `IAccessibleCollection` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `IAccessibleCollection`
     */
    public function filterWithKey(callable $fn): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` after an operation has been applied to each value
     * in the current `IAccessibleCollection`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `IAccessibleCollection` to the
     * returned `IAccessibleCollection`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `IAccessibleCollection` values
     *
     * @psalm-return IAccessibleCollection<Tk, Tu> - a `IAccessibleCollection` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` after an operation has been applied to each key and
     * value in the current `IAccessibleCollection`.
     *
     * Every key and value in the current `IAccessibleCollection` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `IAccessibleCollection` to the returned
     * `IAccessibleCollection`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `IAccessibleCollection` keys and values
     *
     * @psalm-return IAccessibleCollection<Tk, Tu> - a `IAccessibleCollection` containing the values after a user-specified
     *                        operation on the current `IAccessibleCollection`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): IAccessibleCollection;

    /**
     * Returns the first value in the current `IAccessibleCollection`.
     *
     * @psalm-return null|Tv - The first value in the current `IAccessibleCollection`, or `null` if the
     *           current `IAccessibleCollection` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `IAccessibleCollection`.
     *
     * @psalm-return null|Tk - The first key in the current `IAccessibleCollection`, or `null` if the
     *                  current `IAccessibleCollection` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `IAccessibleCollection`.
     *
     * @psalm-return null|Tv - The last value in the current `IAccessibleCollection`, or `null` if the
     *           current `IAccessibleCollection` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `IAccessibleCollection`.
     *
     * @psalm-return null|Tk - The last key in the current `IAccessibleCollection`, or `null` if the
     *                  current `IAccessibleCollection` is empty
     */
    public function lastKey();

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `IAccessibleCollection`.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value);

    /**
     * Returns a `IAccessibleCollection` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `IAccessibleCollection` and the provided `iterable`.
     *
     * If the number of elements of the `IAccessibleCollection` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `IAccessibleCollection`.
     *
     * @psalm-return IAccessibleCollection<Tk, array{0: Tv, 1: Tu}> - The `IAccessibleCollection` that combines the values of the current
     *           `IAccessibleCollection` with the provided `iterable`.
     */
    public function zip(iterable $iterable): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the first `n` values of the current
     * `IAccessibleCollection`.
     *
     * The returned `IAccessibleCollection` will always be a proper subset of the current
     * `IAccessibleCollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `IAccessibleCollection`
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - A `IAccessibleCollection` that is a proper subset of the current
     *           `IAccessibleCollection` up to `n` elements.
     */
    public function take(int $n): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the values of the current `IAccessibleCollection`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `IAccessibleCollection` will always be a proper subset of the current
     * `IAccessibleCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - A `IAccessibleCollection` that is a proper subset of the current
     *           `IAccessibleCollection` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the values after the `n`-th element of
     * the current `IAccessibleCollection`.
     *
     * The returned `IAccessibleCollection` will always be a proper subset of the current
     * `IAccessibleCollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `IAccessibleCollection`.
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - A `IAccessibleCollection` that is a proper subset of the current
     *           `IAccessibleCollection` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): IAccessibleCollection;

    /**
     * Returns a `IAccessibleCollection` containing the values of the current `IAccessibleCollection`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `IAccessibleCollection` will always be a proper subset of the current
     * `IAccessibleCollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `IAccessibleCollection`.
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - A `IAccessibleCollection` that is a proper subset of the current
     *           `IAccessibleCollection` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): IAccessibleCollection;

    /**
     * Returns a subset of the current `IAccessibleCollection` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `IAccessibleCollection` will always be a proper subset of this
     * `IAccessibleCollection`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `IAccessibleCollection`
     * @psalm-param int $len   - The length of the returned `IAccessibleCollection`
     *
     * @psalm-return IAccessibleCollection<Tk, Tv> - A `IAccessibleCollection` that is a proper subset of the current
     *           `IAccessibleCollection` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): IAccessibleCollection;
}
