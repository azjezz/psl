<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
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
     *
     * @psalm-mutation-free
     */
    public function isEmpty(): bool;

    /**
     * Get the number of elements in the collection.
     *
     * @psalm-mutation-free
     *
     * @return int<0, max>
     */
    public function count(): int;

    /**
     * Get an array copy of the current collection.
     *
     * @return array<Tk, Tv>
     *
     * @psalm-mutation-free
     */
    public function toArray(): array;

    /**
     * Get an array copy of the current collection.
     *
     * @return array<Tk, Tv>
     *
     * @psalm-mutation-free
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
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `CollectionInterface` values.
     *
     * @return CollectionInterface<Tk, Tv> A CollectionInterface containing the values after a user-specified
     *                                     condition is applied.
     */
    public function filter(Closure $fn): CollectionInterface;

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
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `CollectionInterface` keys and values.
     *
     * @return CollectionInterface<Tk, Tv> A `CollectionInterface` containing the values after a user-specified
     *                                     condition is applied to the keys and values of the
     *                                     current `CollectionInterface`.
     */
    public function filterWithKey(Closure $fn): CollectionInterface;

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
     * @template Tu
     *
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `CollectionInterface` values.
     *
     * @return CollectionInterface<Tk, Tu> A `CollectionInterface` containing key/value pairs after
     *                                     a user-specified operation is applied.
     */
    public function map(Closure $fn): CollectionInterface;

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
     * @template Tu
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `CollectionInterface` keys and values.
     *
     * @return CollectionInterface<Tk, Tu> A `CollectionInterface` containing the values after a user-specified
     *                                     operation on the current `CollectionInterface`'s keys and values is applied.
     */
    public function mapWithKey(Closure $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `CollectionInterface` and the provided elements array.
     *
     * If the number of elements of the `CollectionInterface` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `CollectionInterface`.
     *
     * @return CollectionInterface<Tk, array{0: Tv, 1: Tu}> The `CollectionInterface` that combines the values of
     *                                                      the current `CollectionInterface` with the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the first `n` values of the current
     * `CollectionInterface`.
     *
     * The returned `CollectionInterface` will always be a proper subset of the current
     * `CollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `CollectionInterface`.
     *
     * @return CollectionInterface<Tk, Tv> A `CollectionInterface` that is a proper subset of the current
     *                                     `CollectionInterface` up to `n` elements.
     *
     * @psalm-mutation-free
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
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return CollectionInterface<Tk, Tv> A `CollectionInterface` that is a proper subset of the current
     *                                     `CollectionInterface` up until the callback returns `false`.
     */
    public function takeWhile(Closure $fn): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the values after the `n`-th element of
     * the current `CollectionInterface`.
     *
     * The returned `CollectionInterface` will always be a proper subset of the current
     * `CollectionInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `CollectionInterface`.
     *
     * @return CollectionInterface<Tk, Tv> A `CollectionInterface` that is a proper subset of the current
     *                                     `CollectionInterface` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
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
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `CollectionInterface`.
     *
     * @return CollectionInterface<Tk, Tv> A `CollectionInterface` that is a proper subset of the current
     *                                     `CollectionInterface` starting after the callback returns `true`.
     */
    public function dropWhile(Closure $fn): CollectionInterface;

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
     * @param int<0, max> $start The starting position of this `CollectionInterface` to begin the returned
     *                           `CollectionInterface`.
     * @param int<0, max> $length The length of the returned `CollectionInterface`.
     *
     * @return CollectionInterface<Tk, Tv> A `CollectionInterface` that is a proper subset of the current
     *                                     `CollectionInterface` starting at `$start` up to but not including
     *                                     the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): CollectionInterface;

    /**
     * Returns a `CollectionInterface` containing the original `CollectionInterface` split into
     * chunks of the given size.
     *
     * If the original `CollectionInterface` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return CollectionInterface<int<0, max>, static<Tk, Tv>> A `CollectionInterface` containing the original
     *                                                          `CollectionInterface` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): CollectionInterface;
}
