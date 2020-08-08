<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The base interface implemented for a collection type that you are able set and remove its values.
 * keys.
 *
 * Every concrete mutable class indirectly implements this interface.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends AccessibleCollectionInterface<Tk, Tv>
 * @extends MutableCollectionInterface<Tk, Tv>
 * @extends MutableIndexAccessInterface<Tk, Tv>
 */
interface MutableAccessibleCollectionInterface extends AccessibleCollectionInterface, MutableCollectionInterface, MutableIndexAccessInterface
{
    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @psalm-return MutableAccessibleCollectionInterface<int, Tv>
     */
    public function values(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the keys of the current `MutableAccessibleCollectionInterface`.
     *
     * @psalm-return MutableAccessibleCollectionInterface<int, Tk>
     */
    public function keys(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current `MutableAccessibleCollectionInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableAccessibleCollectionInterface` remain unchanged in the
     * returned `MutableAccessibleCollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `MutableAccessibleCollectionInterface` values
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - a Collection containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current `MutableAccessibleCollectionInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableAccessibleCollectionInterface` remain unchanged in the
     * returned `MutableAccessibleCollectionInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `MutableAccessibleCollectionInterface` keys and values
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - a `MutableAccessibleCollectionInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `MutableAccessibleCollectionInterface`
     */
    public function filterWithKey(callable $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` after an operation has been applied to each value
     * in the current `MutableAccessibleCollectionInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableAccessibleCollectionInterface` to the
     * returned `MutableAccessibleCollectionInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `MutableAccessibleCollectionInterface` values
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tu> - a `MutableAccessibleCollectionInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` after an operation has been applied to each key and
     * value in the current `MutableAccessibleCollectionInterface`.
     *
     * Every key and value in the current `MutableAccessibleCollectionInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableAccessibleCollectionInterface` to the returned
     * `MutableAccessibleCollectionInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `MutableAccessibleCollectionInterface` keys and values
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tu> - a `MutableAccessibleCollectionInterface` containing the values after a user-specified
     *                        operation on the current `MutableAccessibleCollectionInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): MutableAccessibleCollectionInterface;

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
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - Returns itself
     */
    public function set($k, $v): MutableAccessibleCollectionInterface;

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
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): MutableAccessibleCollectionInterface;

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
     * @psalm-param Tk $k - The key to remove
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - Returns itself
     */
    public function remove($k): MutableAccessibleCollectionInterface;

    /**
     * Removes all items from the collection.
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv>
     */
    public function clear(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableAccessibleCollectionInterface` and the provided `iterable`.
     *
     * If the number of elements of the `MutableAccessibleCollectionInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `MutableAccessibleCollectionInterface`.
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, array{0: Tv, 1: Tu}> - The `MutableAccessibleCollectionInterface` that combines the values of the current
     *           `MutableAccessibleCollectionInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the first `n` values of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `MutableAccessibleCollectionInterface`
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - A `MutableAccessibleCollectionInterface` that is a proper subset of the current
     *           `MutableAccessibleCollectionInterface` up to `n` elements.
     */
    public function take(int $n): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current `MutableAccessibleCollectionInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - A `MutableAccessibleCollectionInterface` that is a proper subset of the current
     *           `MutableAccessibleCollectionInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values after the `n`-th element of
     * the current `MutableAccessibleCollectionInterface`.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `MutableAccessibleCollectionInterface`.
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - A `MutableAccessibleCollectionInterface` that is a proper subset of the current
     *           `MutableAccessibleCollectionInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current `MutableAccessibleCollectionInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableAccessibleCollectionInterface` will always be a proper subset of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `MutableAccessibleCollectionInterface`.
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - A `MutableAccessibleCollectionInterface` that is a proper subset of the current
     *           `MutableAccessibleCollectionInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableAccessibleCollectionInterface;

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
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `MutableAccessibleCollectionInterface`
     * @psalm-param int $len   - The length of the returned `MutableAccessibleCollectionInterface`
     *
     * @psalm-return MutableAccessibleCollectionInterface<Tk, Tv> - A `MutableAccessibleCollectionInterface` that is a proper subset of the current
     *           `MutableAccessibleCollectionInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): MutableAccessibleCollectionInterface;
}
