<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @extends MapInterface<Tk, Tv>
 * @extends MutableAccessibleCollectionInterface<Tk, Tv>
 */
interface MutableMapInterface extends MapInterface, MutableAccessibleCollectionInterface
{
    /**
     * Returns a `MutableVectorInterface` containing the values of the current
     * `MutableMapInterface`.
     *
     * @psalm-return MutableVectorInterface<Tv>
     */
    public function values(): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the keys of the current `MutableMapInterface`.
     *
     * @psalm-return MutableVectorInterface<Tk>
     */
    public function keys(): MutableVectorInterface;

    /**
     * Returns a `MutableMapInterface` containing the values of the current `MutableMapInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableMapInterface` remain unchanged in the
     * returned `MutableMapInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `MutableMapInterface` values
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - a MutableMapInterface containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the values of the current `MutableMapInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableMapInterface` remain unchanged in the
     * returned `MutableMapInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `MutableMapInterface` keys and values
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - a `MutableMapInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `MutableMapInterface`
     */
    public function filterWithKey(callable $fn): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` after an operation has been applied to each value
     * in the current `MutableMapInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableMapInterface` to the
     * returned `MutableMapInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `MutableMapInterface` values
     *
     * @psalm-return MutableMapInterface<Tk, Tu> - a `MutableMapInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` after an operation has been applied to each key and
     * value in the current `MutableMapInterface`.
     *
     * Every key and value in the current `MutableMapInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableMapInterface` to the returned
     * `MutableMapInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `MutableMapInterface` keys and values
     *
     * @psalm-return MutableMapInterface<Tk, Tu> - a `MutableMapInterface` containing the values after a user-specified
     *                        operation on the current `MutableMapInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): MutableMapInterface;

    /**
     * Returns the first value in the current `MutableMapInterface`.
     *
     * @psalm-return null|Tv - The first value in the current `MutableMapInterface`, or `null` if the
     *           current `MutableMapInterface` is empty.
     */
    public function first();

    /**
     * Returns the first key in the current `MutableMapInterface`.
     *
     * @psalm-return null|Tk - The first key in the current `MutableMapInterface`, or `null` if the
     *                  current `MutableMapInterface` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `MutableMapInterface`.
     *
     * @psalm-return null|Tv - The last value in the current `MutableMapInterface`, or `null` if the
     *           current `MutableMapInterface` is empty.
     */
    public function last();

    /**
     * Returns the last key in the current `MutableMapInterface`.
     *
     * @psalm-return null|Tk - The last key in the current `MutableMapInterface`, or `null` if the
     *                  current `MutableMapInterface` is empty
     */
    public function lastKey();

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `MutableMapInterface`.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value);

    /**
     * Returns a `MutableMapInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableMapInterface` and the provided `iterable`.
     *
     * If the number of elements of the `MutableMapInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `MutableMapInterface`.
     *
     * @psalm-return MutableMapInterface<Tk, array{0: Tv, 1: Tu}> - The `MutableMapInterface` that combines the values of the current
     *           `MutableMapInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the first `n` values of the current
     * `MutableMapInterface`.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `MutableMapInterface`
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - A `MutableMapInterface` that is a proper subset of the current
     *           `MutableMapInterface` up to `n` elements.
     */
    public function take(int $n): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the values of the current `MutableMapInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - A `MutableMapInterface` that is a proper subset of the current
     *           `MutableMapInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the values after the `n`-th element of
     * the current `MutableMapInterface`.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `MutableMapInterface`.
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - A `MutableMapInterface` that is a proper subset of the current
     *           `MutableMapInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the values of the current `MutableMapInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `MutableMapInterface`.
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - A `MutableMapInterface` that is a proper subset of the current
     *           `MutableMapInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableMapInterface;

    /**
     * Returns a subset of the current `MutableMapInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableMapInterface` will always be a proper subset of this
     * `MutableMapInterface`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `MutableMapInterface`
     * @psalm-param int $len   - The length of the returned `MutableMapInterface`
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - A `MutableMapInterface` that is a proper subset of the current
     *           `MutableMapInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): MutableMapInterface;
    
    /**
     * Stores a value into the current collection with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * It returns the current collection, meaning changes made to the current
     * collection will be reflected in the returned collection.
     *
     * @psalm-param Tk $k - The key to which we will set the value
     * @psalm-param Tv $v - The value to set
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - Returns itself
     */
    public function set($k, $v): MutableMapInterface;

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current collection associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): MutableMapInterface;

    /**
     * Add a value to the collection and return the collection itself.
     *
     * @psalm-param Tk $k - The key to which we will add the value
     * @psalm-param Tv $v - The value to set
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - Returns itself
     */
    public function add($k, $v): MutableMapInterface;

    /**
     * For every element in the provided iterable, add the value into the current collection.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` with the new values to add
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - Returns itself
     */
    public function addAll(iterable $iterable): MutableMapInterface;

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
     * @psalm-param  Tk $k - The key to remove
     *
     * @psalm-return MutableMapInterface<Tk, Tv> - Returns itself
     */
    public function remove($k): MutableMapInterface;

    /**
     * Removes all items from the collection.
     *
     * @psalm-return MutableMapInterface<Tk, Tv>
     */
    public function clear(): MutableMapInterface;
}
