<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl\Iter;

/**
 * @template   Tk of array-key
 * @template   Tv
 *
 * @extends    AbstractAccessibleCollection<Tk, Tv>
 * @implements IMap<Tk, Tv>
 */
abstract class AbstractMap extends AbstractAccessibleCollection implements IMap
{
    /**
     * @var array<Tk, Tv> $elements
     */
    protected array $elements;

    /**
     * AbstractMap constructor.
     *
     * @psalm-param iterable<Tk, Tv> $elements
     */
    final public function __construct(iterable $elements)
    {
        $this->elements = Iter\to_array_with_keys($elements);
    }

    /**
     * Returns a `IVector` containing the values of the current
     * `AbstractMap`.
     *
     * @psalm-return IVector<Tv>
     */
    abstract public function values(): IVector;

    /**
     * Returns a `IVector` containing the keys of the current `AbstractMap`.
     *
     * @psalm-return IVector<Tk>
     */
    abstract public function keys(): IVector;

    /**
     * Returns a `AbstractMap` containing the values of the current `AbstractMap`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `AbstractMap` remain unchanged in the
     * returned `AbstractMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `AbstractMap` values
     *
     * @psalm-return AbstractMap<Tk, Tv> - a AbstractMap containing the values after a user-specified condition
     *                        is applied
     */
    abstract public function filter(callable $fn): AbstractMap;

    /**
     * Returns a `AbstractMap` containing the values of the current `AbstractMap`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `AbstractMap` remain unchanged in the
     * returned `AbstractMap`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `AbstractMap` keys and values
     *
     * @psalm-return AbstractMap<Tk, Tv> - a `AbstractMap` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `AbstractMap`
     */
    abstract public function filterWithKey(callable $fn): AbstractMap;

    /**
     * Returns a `AbstractMap` after an operation has been applied to each value
     * in the current `AbstractMap`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `AbstractMap` to the
     * returned `AbstractMap`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `AbstractMap` values
     *
     * @psalm-return   AbstractMap<Tk, Tu> - a `AbstractMap` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    abstract public function map(callable $fn): AbstractMap;

    /**
     * Returns a `AbstractMap` after an operation has been applied to each key and
     * value in the current `AbstractMap`.
     *
     * Every key and value in the current `AbstractMap` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `AbstractMap` to the returned
     * `AbstractMap`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `AbstractMap` keys and values
     *
     * @psalm-return   AbstractMap<Tk, Tu> - a `AbstractMap` containing the values after a user-specified
     *                        operation on the current `AbstractMap`'s keys and values is
     *                        applied
     */
    abstract public function mapWithKey(callable $fn): AbstractMap;

    /**
     * Returns a `AbstractMap` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `AbstractMap` and the provided `iterable`.
     *
     * If the number of elements of the `AbstractMap` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `AbstractMap`.
     *
     * @psalm-return   AbstractMap<Tk, array{0: Tv, 1: Tu}> - The `AbstractMap` that combines the values of the current
     *           `AbstractMap` with the provided `iterable`.
     */
    abstract public function zip(iterable $iterable): AbstractMap;

    /**
     * Returns a `AbstractMap` containing the first `n` values of the current
     * `AbstractMap`.
     *
     * The returned `AbstractMap` will always be a proper subset of the current
     * `AbstractMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `AbstractMap`
     *
     * @psalm-return AbstractMap<Tk, Tv> - A `AbstractMap` that is a proper subset of the current
     *           `AbstractMap` up to `n` elements.
     */
    abstract public function take(int $n): AbstractMap;

    /**
     * Returns a `AbstractMap` containing the values of the current `AbstractMap`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `AbstractMap` will always be a proper subset of the current
     * `AbstractMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return AbstractMap<Tk, Tv> - A `AbstractMap` that is a proper subset of the current
     *           `AbstractMap` up until the callback returns `false`.
     */
    abstract public function takeWhile(callable $fn): AbstractMap;

    /**
     * Returns a `AbstractMap` containing the values after the `n`-th element of
     * the current `AbstractMap`.
     *
     * The returned `AbstractMap` will always be a proper subset of the current
     * `AbstractMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `AbstractMap`.
     *
     * @psalm-return AbstractMap<Tk, Tv> - A `AbstractMap` that is a proper subset of the current
     *           `AbstractMap` containing values after the specified `n`-th
     *           element.
     */
    abstract public function drop(int $n): AbstractMap;

    /**
     * Returns a `AbstractMap` containing the values of the current `AbstractMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `AbstractMap` will always be a proper subset of the current
     * `AbstractMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `AbstractMap`.
     *
     * @psalm-return AbstractMap<Tk, Tv> - A `AbstractMap` that is a proper subset of the current
     *           `AbstractMap` starting after the callback returns `true`.
     */
    abstract public function dropWhile(callable $fn): AbstractMap;

    /**
     * Returns a subset of the current `AbstractMap` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `AbstractMap` will always be a proper subset of this
     * `AbstractMap`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `AbstractMap`
     * @psalm-param  int $len   - The length of the returned `AbstractMap`
     *
     * @psalm-return AbstractMap<Tk, Tv> - A `AbstractMap` that is a proper subset of the current
     *           `AbstractMap` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    abstract public function slice(int $start, int $len): AbstractMap;
}
