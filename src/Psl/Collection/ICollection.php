<?php

declare(strict_types=1);

namespace Psl\Collection;

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * The base interface implemented for a ICollection type.
 *
 * Every concrete class indirectly implements this interface.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends IteratorAggregate<Tk, Tv>
 */
interface ICollection extends Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Is the ICollection empty?
     */
    public function isEmpty(): bool;

    /**
     * Get the number of items in the collection.
     */
    public function count(): int;

    /**
     * Get an array copy of the current collection.
     *
     * @psalm-return array<Tk, Tv>
     */
    public function toArray(): array;

    /**
     * Get an array copy of the current collection.
     *
     * @psalm-return array<Tk, Tv>
     */
    public function jsonSerialize(): array;

    /**
     * Returns a `ICollection` containing the values of the current `ICollection`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `ICollection` remain unchanged in the
     * returned `ICollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `ICollection` values
     *
     * @psalm-return ICollection<Tk, Tv> - a ICollection containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): ICollection;

    /**
     * Returns a `ICollection` containing the values of the current `ICollection`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `ICollection` remain unchanged in the
     * returned `ICollection`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `ICollection` keys and values
     *
     * @psalm-return ICollection<Tk, Tv> - a `ICollection` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `ICollection`
     */
    public function filterWithKey(callable $fn): ICollection;

    /**
     * Returns a `ICollection` after an operation has been applied to each value
     * in the current `ICollection`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `ICollection` to the
     * returned `ICollection`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `ICollection` values
     *
     * @psalm-return ICollection<Tk, Tu> - a `ICollection` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): ICollection;

    /**
     * Returns a `ICollection` after an operation has been applied to each key and
     * value in the current `ICollection`.
     *
     * Every key and value in the current `ICollection` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `ICollection` to the returned
     * `ICollection`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `ICollection` keys and values
     *
     * @psalm-return ICollection<Tk, Tu> - a `ICollection` containing the values after a user-specified
     *                        operation on the current `ICollection`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): ICollection;

    /**
     * Returns a `ICollection` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `ICollection` and the provided `iterable`.
     *
     * If the number of elements of the `ICollection` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `ICollection`.
     *
     * @psalm-return ICollection<Tk, array{0: Tv, 1: Tu}> - The `ICollection` that combines the values of the current
     *           `ICollection` with the provided `iterable`.
     */
    public function zip(iterable $iterable): ICollection;

    /**
     * Returns a `ICollection` containing the first `n` values of the current
     * `ICollection`.
     *
     * The returned `ICollection` will always be a proper subset of the current
     * `ICollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `ICollection`
     *
     * @psalm-return ICollection<Tk, Tv> - A `ICollection` that is a proper subset of the current
     *           `ICollection` up to `n` elements.
     */
    public function take(int $n): ICollection;

    /**
     * Returns a `ICollection` containing the values of the current `ICollection`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `ICollection` will always be a proper subset of the current
     * `ICollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return ICollection<Tk, Tv> - A `ICollection` that is a proper subset of the current
     *           `ICollection` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): ICollection;

    /**
     * Returns a `ICollection` containing the values after the `n`-th element of
     * the current `ICollection`.
     *
     * The returned `ICollection` will always be a proper subset of the current
     * `ICollection`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `ICollection`.
     *
     * @psalm-return ICollection<Tk, Tv> - A `ICollection` that is a proper subset of the current
     *           `ICollection` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): ICollection;

    /**
     * Returns a `ICollection` containing the values of the current `ICollection`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `ICollection` will always be a proper subset of the current
     * `ICollection`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `ICollection`.
     *
     * @psalm-return ICollection<Tk, Tv> - A `ICollection` that is a proper subset of the current
     *           `ICollection` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): ICollection;

    /**
     * Returns a subset of the current `ICollection` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `ICollection` will always be a proper subset of this
     * `ICollection`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `ICollection`
     * @psalm-param int $len   - The length of the returned `ICollection`
     *
     * @psalm-return ICollection<Tk, Tv> - A `ICollection` that is a proper subset of the current
     *           `ICollection` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): ICollection;
}
