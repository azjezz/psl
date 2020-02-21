<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Arr;

/**
 * @template   T
 *
 * @extends    AbstractAccessibleCollection<int, T>
 * @implements IVector<T>
 */
abstract class AbstractVector extends AbstractAccessibleCollection implements IVector
{
    /**
     * @var array<int, T> $elements
     */
    protected array $elements = [];

    /**
     * Vector constructor.
     *
     * @psalm-param iterable<T> $elements
     */
    public function __construct(iterable $elements)
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }
    }

    /**
     * Returns the first key in the current `AbstractVector`.
     *
     * @psalm-return null|int - The first key in the current `AbstractVector`, or `null` if the
     *                  current `AbstractVector` is empty
     */
    public function firstKey(): ?int
    {
        /** @var int|null $key */
        $key = Arr\first_key($this->elements);

        return $key;
    }

    /**
     * Returns the last key in the current `AbstractVector`.
     *
     * @psalm-return null|int - The last key in the current `AbstractVector`, or `null` if the
     *                  current `AbstractVector` is empty
     */
    public function lastKey(): ?int
    {
        return Arr\last_key($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param  Tv $search_value - The value that will be search for in the current
     *                        collection.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    final public function linearSearch($search_value): ?int
    {
        foreach ($this->elements as $key => $element) {
            if ($search_value === $element) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Returns a `IVector` containing the values of the current
     * `AbstractVector`.
     *
     * @psalm-return IVector<T>
     */
    abstract public function values(): IVector;

    /**
     * Returns a `IVector` containing the keys of the current `AbstractVector`.
     *
     * @psalm-return IVector<int>
     */
    abstract public function keys(): IVector;

    /**
     * Returns a `AbstractVector` containing the values of the current `AbstractVector`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `AbstractVector` remain unchanged in the
     * returned `AbstractVector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback containing the condition to apply to the current
     *                                 `AbstractVector` values
     *
     * @psalm-return AbstractVector<T> - a AbstractVector containing the values after a user-specified condition
     *                        is applied
     */
    abstract public function filter(callable $fn): AbstractVector;

    /**
     * Returns a `AbstractVector` containing the values of the current `AbstractVector`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `AbstractVector` remain unchanged in the
     * returned `AbstractVector`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(int, T): bool) $fn - The callback containing the condition to apply to the current
     *                                     `AbstractVector` keys and values
     *
     * @psalm-return AbstractVector<T> - a `AbstractVector` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `AbstractVector`
     */
    abstract public function filterWithKey(callable $fn): AbstractVector;

    /**
     * Returns a `AbstractVector` after an operation has been applied to each value
     * in the current `AbstractVector`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `AbstractVector` to the
     * returned `AbstractVector`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(T): Tu) $fn - The callback containing the operation to apply to the current
     *                               `AbstractVector` values
     *
     * @psalm-return   AbstractVector<Tu> - a `AbstractVector` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    abstract public function map(callable $fn): AbstractVector;

    /**
     * Returns a `AbstractVector` after an operation has been applied to each key and
     * value in the current `AbstractVector`.
     *
     * Every key and value in the current `AbstractVector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `AbstractVector` to the returned
     * `AbstractVector`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, T): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `AbstractVector` keys and values
     *
     * @psalm-return   AbstractVector<Tu> - a `AbstractVector` containing the values after a user-specified
     *                        operation on the current `AbstractVector`'s keys and values is
     *                        applied
     */
    abstract public function mapWithKey(callable $fn): AbstractVector;

    /**
     * Returns a `AbstractVector` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `AbstractVector` and the provided `iterable`.
     *
     * If the number of elements of the `AbstractVector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `AbstractVector`.
     *
     * @psalm-return   AbstractVector<array{0: T, 1: Tu}> - The `AbstractVector` that combines the values of the current
     *           `AbstractVector` with the provided `iterable`.
     */
    abstract public function zip(iterable $iterable): AbstractVector;

    /**
     * Returns a `AbstractVector` containing the first `n` values of the current
     * `AbstractVector`.
     *
     * The returned `AbstractVector` will always be a proper subset of the current
     * `AbstractVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `AbstractVector`
     *
     * @psalm-return AbstractVector<T> - A `AbstractVector` that is a proper subset of the current
     *           `AbstractVector` up to `n` elements.
     */
    abstract public function take(int $n): AbstractVector;

    /**
     * Returns a `AbstractVector` containing the values of the current `AbstractVector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `AbstractVector` will always be a proper subset of the current
     * `AbstractVector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return AbstractVector<T> - A `AbstractVector` that is a proper subset of the current
     *           `AbstractVector` up until the callback returns `false`.
     */
    abstract public function takeWhile(callable $fn): AbstractVector;

    /**
     * Returns a `AbstractVector` containing the values after the `n`-th element of
     * the current `AbstractVector`.
     *
     * The returned `AbstractVector` will always be a proper subset of the current
     * `AbstractVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `AbstractVector`.
     *
     * @psalm-return AbstractVector<T> - A `AbstractVector` that is a proper subset of the current
     *           `AbstractVector` containing values after the specified `n`-th
     *           element.
     */
    abstract public function drop(int $n): AbstractVector;

    /**
     * Returns a `AbstractVector` containing the values of the current `AbstractVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `AbstractVector` will always be a proper subset of the current
     * `AbstractVector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback used to determine the starting element for the
     *              returned `AbstractVector`.
     *
     * @psalm-return AbstractVector<T> - A `AbstractVector` that is a proper subset of the current
     *           `AbstractVector` starting after the callback returns `true`.
     */
    abstract public function dropWhile(callable $fn): AbstractVector;

    /**
     * Returns a subset of the current `AbstractVector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `AbstractVector` will always be a proper subset of this
     * `AbstractVector`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `AbstractVector`
     * @psalm-param  int $len   - The length of the returned `AbstractVector`
     *
     * @psalm-return AbstractVector<T> - A `AbstractVector` that is a proper subset of the current
     *           `AbstractVector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    abstract public function slice(int $start, int $len): AbstractVector;
}
