<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;

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
     * @return MutableVectorInterface<Tv>
     *
     * @psalm-mutation-free
     */
    public function values(): MutableVectorInterface;

    /**
     * Returns a `MutableVectorInterface` containing the keys of the current `MutableMapInterface`.
     *
     * @return MutableVectorInterface<Tk>
     *
     * @psalm-mutation-free
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
     * @param (Closure(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                `MutableMapInterface` values.
     *
     * @return MutableMapInterface<Tk, Tv> - a MutableMapInterface containing the values after a user-specified
     *                                     condition is applied.
     */
    public function filter(Closure $fn): MutableMapInterface;

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
     * @param (Closure(Tk, Tv): bool) $fn - The callback containing the condition to apply to
     *                                    the current `MutableMapInterface` keys and values.
     *
     * @return MutableMapInterface<Tk, Tv> - a `MutableMapInterface` containing the values after a user-specified
     *                                     condition is applied to the keys and values of the
     *                                     current `MutableMapInterface`.
     */
    public function filterWithKey(Closure $fn): MutableMapInterface;

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
     * @template Tu
     *
     * @param (Closure(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                              `MutableMapInterface` values.
     *
     * @return MutableMapInterface<Tk, Tu> - a `MutableMapInterface` containing key/value pairs after
     *                                     a user-specified operation is applied.
     */
    public function map(Closure $fn): MutableMapInterface;

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
     * @template Tu
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `MutableMapInterface` keys and values.
     *
     * @return MutableMapInterface<Tk, Tu> A `MutableMapInterface` containing the values after a user-specified
     *                                     operation on the current `MutableMapInterface`'s keys and values is applied.
     */
    public function mapWithKey(Closure $fn): MutableMapInterface;

    /**
     * Returns the first value in the current `MutableMapInterface`.
     *
     * @return Tv|null The first value in the current `MutableMapInterface`, or `null` if the
     *                 current `MutableMapInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function first(): mixed;

    /**
     * Returns the first key in the current `MutableMapInterface`.
     *
     * @return Tk|null The first key in the current `MutableMapInterface`, or `null` if the
     *                 current `MutableMapInterface` is empty
     *
     * @psalm-mutation-free
     */
    public function firstKey(): int|string|null;

    /**
     * Returns the last value in the current `MutableMapInterface`.
     *
     * @return Tv|null The last value in the current `MutableMapInterface`, or `null` if the
     *                 current `MutableMapInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function last(): mixed;

    /**
     * Returns the last key in the current `MutableMapInterface`.
     *
     * @return Tk|null The last key in the current `MutableMapInterface`, or `null` if the
     *                 current `MutableMapInterface` is empty.
     *
     * @psalm-mutation-free
     */
    public function lastKey(): int|string|null;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @param Tv $search_value The value that will be search for in the current
     *                         `MutableMapInterface`.
     *
     * @return Tk|null The key (index) where that value is found; null if it is not found.
     *
     * @psalm-mutation-free
     */
    public function linearSearch(mixed $search_value): int|string|null;

    /**
     * Returns a `MutableMapInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableMapInterface` and the provided elements.
     *
     * If the number of elements of the `MutableMapInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableMapInterface`.
     *
     * @return MutableMapInterface<Tk, array{0: Tv, 1: Tu}> - The `MutableMapInterface` that combines
     *                                                      the values of the current `MutableMapInterface` with
     *                                                      the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the first `n` values of the current
     * `MutableMapInterface`.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned `MutableMapInterface`.
     *
     * @return MutableMapInterface<Tk, Tv> A `MutableMapInterface` that is a proper subset of the current
     *                                     `MutableMapInterface` up to `n` elements.
     *
     * @psalm-mutation-free
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
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping condition.
     *
     * @return MutableMapInterface<Tk, Tv> A `MutableMapInterface` that is a proper subset of the current
     *                                     `MutableMapInterface` up until the callback returns `false`.
     */
    public function takeWhile(Closure $fn): MutableMapInterface;

    /**
     * Returns a `MutableMapInterface` containing the values after the `n`-th element of
     * the current `MutableMapInterface`.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the first one in
     *                       the returned `MutableMapInterface`.
     *
     * @return MutableMapInterface<Tk, Tv> A `MutableMapInterface` that is a proper subset of the current
     *                                     `MutableMapInterface` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
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
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `MutableMapInterface`.
     *
     * @return MutableMapInterface<Tk, Tv> A `MutableMapInterface` that is a proper subset of the current
     *                                     `MutableMapInterface` starting after the callback returns `true`.
     */
    public function dropWhile(Closure $fn): MutableMapInterface;

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
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `MutableMapInterface`.
     * @param null|int<0, max> $length The length of the returned `MutableMapInterface`.
     *
     * @return MutableMapInterface<Tk, Tv> - A `MutableMapInterface` that is a proper subset of the current
     *                                     `MutableMapInterface` starting at `$start` up to but not including
     *                                     the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): MutableMapInterface;

    /**
     * Returns a `MutableVectorInterface` containing the original `MutableMapInterface` split into
     * chunks of the given size.
     *
     * If the original `MutableMapInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return MutableVectorInterface<static<Tk, Tv>> A `MutableVectorInterface` containing the original
     *                                                `MutableMapInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): MutableVectorInterface;

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
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function set(int|string $k, mixed $v): MutableMapInterface;

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
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function setAll(array $elements): MutableMapInterface;

    /**
     * Add a value to the collection and return the collection itself.
     *
     * @param Tk $k The key to which we will add the value.
     * @param Tv $v The value to set.
     *
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function add(int|string $k, mixed $v): MutableMapInterface;

    /**
     * For every element in the provided elements array, add the value into the current collection.
     *
     * @param array<Tk, Tv> $elements The elements with the new values to add.
     *
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function addAll(array $elements): MutableMapInterface;

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
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function remove(int|string $k): MutableMapInterface;

    /**
     * Removes all elements from the collection.
     *
     * @return MutableMapInterface<Tk, Tv>
     */
    public function clear(): MutableMapInterface;
}
