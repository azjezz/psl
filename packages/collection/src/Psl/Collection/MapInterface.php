<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
use Override;

/**
 * @api
 */
interface MapInterface<Tk: string|int, Tv> extends AccessibleCollectionInterface<Tk, Tv>
{
    /**
     * Returns a `VectorInterface` containing the values of the current
     * `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): VectorInterface<Tv>;

    /**
     * Returns a `VectorInterface` containing the keys of the current `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): VectorInterface<Tk>;

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
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `MapInterface` values.
     */
    #[Override]
    public function filter(Closure $fn): MapInterface<Tk, Tv>;

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
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `MapInterface` keys and values.
     */
    #[Override]
    public function filterWithKey(Closure $fn): MapInterface<Tk, Tv>;

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
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `MapInterface` values.
     */
    public function map<Tu>(Closure $fn): MapInterface<Tk, Tu>;

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
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `MapInterface` keys and values.
     */
    public function mapWithKey<Tu>(Closure $fn): MapInterface<Tk, Tu>;

    /**
     * Returns the first value in the current `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): Tv|null;

    /**
     * Returns the first key in the current `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function firstKey(): Tk|null;

    /**
     * Returns the last value in the current `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function last(): Tv|null;

    /**
     * Returns the last key in the current `MapInterface`.
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
     * Returns a `MapInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MapInterface` and the provided elements.
     *
     * If the number of elements of the `MapInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MapInterface`.
     *
     * @return MapInterface<Tk, array{0: Tv, 1: Tu}> The `MapInterface` that combines the values of the current
     *                                               `MapInterface` with the provided elements.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function zip<Tu>(array $elements): MapInterface<Tk, array>;

    /**
     * Returns a `MapInterface` containing the first `n` values of the current
     * `MapInterface`.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MapInterface<Tk, Tv>;

    /**
     * Returns a `MapInterface` containing the values of the current `MapInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): MapInterface<Tk, Tv>;

    /**
     * Returns a `MapInterface` containing the values after the `n`-th element of
     * the current `MapInterface`.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MapInterface`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MapInterface<Tk, Tv>;

    /**
     * Returns a `MapInterface` containing the values of the current `MapInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MapInterface` will always be a proper subset of the current
     * `MapInterface`.
     *
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `MapInterface`.
     */
    #[Override]
    public function dropWhile(Closure $fn): MapInterface<Tk, Tv>;

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
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `MapInterface`
     * @param null|int<0, max> $length The length of the returned `MapInterface`
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MapInterface<Tk, Tv>;

    /**
     * Returns a `VectorInterface` containing the original `MapInterface` split into
     * chunks of the given size.
     *
     * If the original `MapInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return VectorInterface<static<Tk, Tv>> A `VectorInterface` containing the original
     *                                         `MapInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): VectorInterface<MapInterface<Tk, Tv>>;
}
