<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
use Override;

/**
 * @api
 */
interface MutableMapInterface<Tk: string|int, Tv> extends MapInterface<Tk, Tv>, MutableAccessibleCollectionInterface<Tk, Tv>
{
    /**
     * Returns a `MutableVectorInterface` containing the values of the current
     * `MutableMapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): MutableVectorInterface<Tv>;

    /**
     * Returns a `MutableVectorInterface` containing the keys of the current `MutableMapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): MutableVectorInterface<Tk>;

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
     */
    #[Override]
    public function filter(Closure $fn): MutableMapInterface<Tk, Tv>;

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
     */
    #[Override]
    public function filterWithKey(Closure $fn): MutableMapInterface<Tk, Tv>;

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
     * @param (Closure(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                              `MutableMapInterface` values.
     */
    #[Override]
    public function map<Tu>(Closure $fn): MutableMapInterface<Tk, Tu>;

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
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `MutableMapInterface` keys and values.
     */
    #[Override]
    public function mapWithKey<Tu>(Closure $fn): MutableMapInterface<Tk, Tu>;

    /**
     * Returns the first value in the current `MutableMapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): Tv|null;

    /**
     * Returns the first key in the current `MutableMapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function firstKey(): Tk|null;

    /**
     * Returns the last value in the current `MutableMapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function last(): Tv|null;

    /**
     * Returns the last key in the current `MutableMapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function lastKey(): Tk|null;

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function linearSearch(Tv $searchValue): Tk|null;

    /**
     * Returns a `MutableMapInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableMapInterface` and the provided elements.
     *
     * If the number of elements of the `MutableMapInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableMapInterface`.
     *
     * @return MutableMapInterface<Tk, array{0: Tv, 1: Tu}> - The `MutableMapInterface` that combines
     *                                                      the values of the current `MutableMapInterface` with
     *                                                      the provided elements.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function zip<Tu>(array $elements): MutableMapInterface<Tk, array>;

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
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MutableMapInterface<Tk, Tv>;

    /**
     * Returns a `MutableMapInterface` containing the values of the current `MutableMapInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableMapInterface` will always be a proper subset of the current
     * `MutableMapInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): MutableMapInterface<Tk, Tv>;

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
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MutableMapInterface<Tk, Tv>;

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
     */
    #[Override]
    public function dropWhile(Closure $fn): MutableMapInterface<Tk, Tv>;

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
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MutableMapInterface<Tk, Tv>;

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
    #[Override]
    public function chunk(int $size): MutableVectorInterface<MutableMapInterface<Tk, Tv>>;

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
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function set(Tk $k, Tv $v): MutableMapInterface<Tk, Tv>;

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
    public function setAll(array $elements): MutableMapInterface<Tk, Tv>;

    /**
     * Add a value to the collection and return the collection itself.
     *
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function add(Tk $k, Tv $v): MutableMapInterface<Tk, Tv>;

    /**
     * For every element in the provided elements array, add the value into the current collection.
     *
     * @param iterable<Tk, Tv> $elements The elements with the new values to add.
     *
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    public function addAll(iterable $elements): MutableMapInterface<Tk, Tv>;

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
     * @return MutableMapInterface<Tk, Tv> Returns itself.
     */
    #[Override]
    public function remove(Tk $k): MutableMapInterface<Tk, Tv>;

    /**
     * Removes all elements from the collection.
     */
    #[Override]
    public function clear(): MutableMapInterface<Tk, Tv>;
}
