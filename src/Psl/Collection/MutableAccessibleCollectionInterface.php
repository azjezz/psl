<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;

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
interface MutableAccessibleCollectionInterface extends
    AccessibleCollectionInterface,
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
    public function values(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the keys of the current
     * `MutableAccessibleCollectionInterface`.
     *
     * @return MutableAccessibleCollectionInterface<int<0, max>, Tk>
     *
     * @psalm-mutation-free
     */
    public function keys(): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface` that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
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
    public function filter(Closure $fn): MutableAccessibleCollectionInterface;

    /**
     * Returns a `MutableAccessibleCollectionInterface` containing the values of the current
     * `MutableAccessibleCollectionInterface` that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
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
    public function filterWithKey(Closure $fn): MutableAccessibleCollectionInterface;

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
     * @template Tu
     *
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `MutableAccessibleCollectionInterface` values.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tu> A `MutableAccessibleCollectionInterface` containing
     *                                                      key/value pairs after a user-specified operation is applied.
     */
    public function map(Closure $fn): MutableAccessibleCollectionInterface;

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
     * @template Tu
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `MutableAccessibleCollectionInterface` keys and values.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tu> A `MutableAccessibleCollectionInterface` containing
     *                                                      the values after a user-specified operation on the current
     *                                                      `MutableAccessibleCollectionInterface`'s keys and values is
     *                                                      applied.
     */
    public function mapWithKey(Closure $fn): MutableAccessibleCollectionInterface;

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
     * @param Tk $k The key to which we will set the value.
     * @param Tv $v The value to set.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> Returns itself.
     */
    public function set(int|string $k, mixed $v): MutableAccessibleCollectionInterface;

    /**
     * For every element in the provided elements, stores a value into the
     * current collection associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @param array<Tk, Tv> $elements The elements with the new values to set.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv> Returns itself.
     */
    public function setAll(array $elements): MutableAccessibleCollectionInterface;

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
    public function remove(int|string $k): MutableAccessibleCollectionInterface;

    /**
     * Removes all elements from the collection.
     *
     * @return MutableAccessibleCollectionInterface<Tk, Tv>
     */
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
    public function slice(int $start, ?int $length = null): MutableAccessibleCollectionInterface;

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
    public function chunk(int $size): MutableAccessibleCollectionInterface;
}
