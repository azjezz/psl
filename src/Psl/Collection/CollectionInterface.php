<?php

declare(strict_types=1);

namespace Psl\Collection;

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * The base interface implemented for a CollectionInterface type.
 *
 * Every concrete class indirectly implements this interface.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends IteratorAggregate<Tk, Tv>
 */
interface CollectionInterface extends Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Is the CollectionInterface empty?
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
     * Returns a `CollectionInterface` containing the values of the current `CollectionInterface`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `CollectionInterface` remain unchanged in the
     * returned `CollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `CollectionInterface` values
     *
     * @psalm-return CollectionInterface<Tk, Tv> - a CollectionInterface containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the values of the current `CollectionInterface`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `CollectionInterface` remain unchanged in the
     * returned `CollectionInterface`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `CollectionInterface` keys and values
     *
     * @psalm-return CollectionInterface<Tk, Tv> - a `CollectionInterface` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `CollectionInterface`
     */
    public function filterWithKey(callable $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` after an operation has been applied to each value
     * in the current `CollectionInterface`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `CollectionInterface` to the
     * returned `CollectionInterface`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `CollectionInterface` values
     *
     * @psalm-return CollectionInterface<Tk, Tu> - a `CollectionInterface` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` after an operation has been applied to each key and
     * value in the current `CollectionInterface`.
     *
     * Every key and value in the current `CollectionInterface` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `CollectionInterface` to the returned
     * `CollectionInterface`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `CollectionInterface` keys and values
     *
     * @psalm-return CollectionInterface<Tk, Tu> - a `CollectionInterface` containing the values after a user-specified
     *                        operation on the current `CollectionInterface`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `CollectionInterface` and the provided `iterable`.
     *
     * If the number of elements of the `CollectionInterface` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `CollectionInterface`.
     *
     * @psalm-return CollectionInterface<Tk, array{0: Tv, 1: Tu}> - The `CollectionInterface` that combines the values of the current
     *           `CollectionInterface` with the provided `iterable`.
     */
    public function zip(iterable $iterable): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the first `n` values of the current
     * `CollectionInterface`.
     *
     * The returned `CollectionInterface` will always be a proper subset of the current
     * `CollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `CollectionInterface`
     *
     * @psalm-return CollectionInterface<Tk, Tv> - A `CollectionInterface` that is a proper subset of the current
     *           `CollectionInterface` up to `n` elements.
     */
    public function take(int $n): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the values of the current `CollectionInterface`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `CollectionInterface` will always be a proper subset of the current
     * `CollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return CollectionInterface<Tk, Tv> - A `CollectionInterface` that is a proper subset of the current
     *           `CollectionInterface` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the values after the `n`-th element of
     * the current `CollectionInterface`.
     *
     * The returned `CollectionInterface` will always be a proper subset of the current
     * `CollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `CollectionInterface`.
     *
     * @psalm-return CollectionInterface<Tk, Tv> - A `CollectionInterface` that is a proper subset of the current
     *           `CollectionInterface` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the values of the current `CollectionInterface`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `CollectionInterface` will always be a proper subset of the current
     * `CollectionInterface`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `CollectionInterface`.
     *
     * @psalm-return CollectionInterface<Tk, Tv> - A `CollectionInterface` that is a proper subset of the current
     *           `CollectionInterface` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): CollectionInterface;

    /**
     * Returns a subset of the current `CollectionInterface` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `CollectionInterface` will always be a proper subset of this
     * `CollectionInterface`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `CollectionInterface`
     * @psalm-param int $len   - The length of the returned `CollectionInterface`
     *
     * @psalm-return CollectionInterface<Tk, Tv> - A `CollectionInterface` that is a proper subset of the current
     *           `CollectionInterface` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): CollectionInterface;
}
