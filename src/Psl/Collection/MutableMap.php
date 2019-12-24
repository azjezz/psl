<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * Represents a write-enabled (mutable) sequence of key/value pairs (ie. map).
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @template-extends ConstMap<Tk, Tv>
 * @template-extends Collection<Pair<Tk, Tv>>
 * @template-extends MapAccess<Tk, Tv>
 */
interface MutableMap extends ConstMap, Collection, MapAccess
{
    /**
     * Returns a `MutableVector` containing the values of the current
     * `MutableMap`.
     *
     * The indices of the `MutableVector will be integer-indexed starting from 0,
     * no matter the keys of the `MutableMap`.
     *
     * @psalm-return MutableVector<Tv> - a `MutableVector` containing the values of the current
     *                           `MutableMap`
     */
    public function values(): MutableVector;

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableMap`.
     *
     * @psalm-return MutableVector<Tk> - a `MutableVector` containing the keys of the current
     *                           `MutableMap`
     */
    public function keys(): MutableVector;

    /**
     * Returns a `MutableMap` after an operation has been applied to each value
     * in the current `MutableMap`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableMap` to the
     * returned `MutableMap`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `MutableMap` values
     *
     * @psalm-return MutableMap<Tk, Tu> - a `MutableMap` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): MutableMap;

    /**
     * Returns a `MutableMap` after an operation has been applied to each key and
     * value in the current `MutableMap`.
     *
     * Every key and value in the current `MutableMap` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableMap` to the returned
     * `MutableMap`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `MutableMap` keys and values
     *
     * @psalm-return MutableMap<Tk, Tu> - a `MutableMap` containing the values after a user-specified
     *                        operation on the current `MutableMap`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): MutableMap;

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableMap` remain unchanged in the
     * returned `MutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `MutableMap` values
     *
     * @psalm-return MutableMap<Tk, Tv> - a Map containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): MutableMap;

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableMap` remain unchanged in the
     * returned `MutableMap`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `MutableMap` keys and values
     *
     * @psalm-return MutableMap<Tk, Tv> - a `MutableMap` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `MutableMap`
     */
    public function filterWithKey(callable $fn): MutableMap;

    /**
     * Returns a `MutableMap` where each value is a `Pair` that combines the
     * value of the current `MutableMap` and the provided `iterable`.
     *
     * If the number of values of the current `MutableMap` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * The keys associated with the current `MutableMap` remain unchanged in the
     * returned `MutableMap`.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                               elements of the current `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Pair<Tv, Tu>> - The `MutableMap` that combines the values of the current
     *                        `MutableMap` with the provided `iterable`
     */
    public function zip(iterable $iterable): MutableMap;

    /**
     * Returns a `MutableMap` containing the first `n` key/values of the current
     * `MutableMap`.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element that will be included in the `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *                        `MutableMap` up to `n` elements
     */
    public function take(int $n): MutableMap;

    /**
     * Returns a `MutableMap` containing the keys and values of the current
     * `MutableMap` up to but not including the first value that produces `false`
     * when passed to the specified callback.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping condition
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *                        `MutableMap` up until the callback returns `false`
     */
    public function takeWhile(callable $fn): MutableMap;

    /**
     * Returns a `MutableMap` containing the values after the `n`-th element of
     * the current `MutableMap`.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the `$n+1` element will be the
     *               first one in the returned `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *                        `MutableMap` containing values after the specified `n`-th
     *                        element
     */
    public function drop(int $n): MutableMap;

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @psalm-param (callable(Tk): bool) $fn - The callback used to determine the starting element for the
     *                                 current `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *                        `MutableMap` starting after the callback returns `true`
     */
    public function dropWhile(callable $fn): MutableMap;

    /**
     * Returns a subset of the current `MutableMap` starting from a given key
     * location up to, but not including, the element at the provided length from
     * the starting key location.
     *
     * `$start` is 0-based. `$len` is 1-based. So `slice(0, 2)` would return the
     * keys and values at key location 0 and 1.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @psalm-param int $start - The starting key location of the current `MutableMap` for
     *                   the featured `MutableMap`
     * @psalm-param int $len   - The length of the returned `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *                     `MutableMap` starting at `$start` up to but not including the
     *                     element `$start + $len`
     */
    public function slice(int $start, int $len): MutableMap;

    /**
     * Returns a `MutableVector` that is the concatenation of the values of the
     * current `MutableMap` and the values of the provided `iterable`.
     *
     * The provided `iterable` is concatenated to the end of the current
     * `MutableMap` to produce the returned `MutableVector`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                               `MutableMap`
     *
     * @psalm-return MutableVector<Tu> - The integer-indexed concatenated `MutableVector`
     */
    public function concat(iterable $iterable): MutableVector;

    /**
     * Returns the first value in the current `MutableMap`.
     *
     * @psalm-return Tv|null - The first value in the current `MutableMap`,  or `null` if the
     *                 `MutableMap` is empty
     */
    public function first();

    /**
     * Returns the first key in the current `MutableMap`.
     *
     * @psalm-return Tk|null - The first key in the current `MutableMap`, or `null` if the
     *                 `MutableMap` is empty
     */
    public function firstKey();

    /**
     * Returns the last value in the current `MutableMap`.
     *
     * @psalm-return Tv|null - The last value in the current `MutableMap`, or `null` if the
     *                 `MutableMap` is empty
     */
    public function last();

    /**
     * Returns the last key in the current `MutableMap`.
     *
     * @psalm-return Tk|null - The last key in the current `MutableMap`, or null if the
     *                 `MutableMap` is empty
     */
    public function lastKey();
}
