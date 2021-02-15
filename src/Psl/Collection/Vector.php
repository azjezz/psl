<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Arr;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * @template   T
 *
 * @implements VectorInterface<T>
 */
final class Vector implements VectorInterface
{
    /**
     * @psalm-var array<int, T> $elements
     *
     * @psalm-readonly
     */
    private array $elements = [];

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
     * Returns the first value in the current collection.
     *
     * @psalm-return T|null - The first value in the current collection, or `null` if the
     *           current collection is empty.
     */
    public function first()
    {
        return Arr\first($this->elements);
    }

    /**
     * Returns the last value in the current collection.
     *
     * @psalm-return T|null - The last value in the current collection, or `null` if the
     *           current collection is empty.
     */
    public function last()
    {
        return Arr\last($this->elements);
    }

    /**
     * Retrieve an external iterator
     *
     * @psalm-return Iter\Iterator<int, T>
     */
    public function getIterator(): Iter\Iterator
    {
        return Iter\Iterator::create($this->elements);
    }

    /**
     * Is the vector empty?
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the current vector.
     */
    public function count(): int
    {
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the current vector.
     *
     * @psalm-return list<T>
     */
    public function toArray(): array
    {
        return Vec\values($this->elements);
    }


    /**
     * Get an array copy of the current vector.
     *
     * @psalm-return list<T>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the value at the specified key in the current vector.
     *
     * @psalm-param  int $k
     *
     * @psalm-return T
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     */
    public function at($k)
    {
        return Arr\at($this->elements, $k);
    }

    /**
     * Determines if the specified key is in the current vector.
     *
     * @psalm-param int $k
     */
    public function contains($k): bool
    {
        return Arr\contains_key($this->elements, $k);
    }

    /**
     * Returns the value at the specified key in the current vector.
     *
     * @psalm-param  int $k
     *
     * @psalm-return T|null
     */
    public function get($k)
    {
        return Arr\idx($this->elements, $k);
    }

    /**
     * Returns the first key in the current `AbstractVector`.
     *
     * @psalm-return int|null - The first key in the current `AbstractVector`, or `null` if the
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
     * @psalm-return int|null - The last key in the current `AbstractVector`, or `null` if the
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
     * @psalm-param  T $search_value - The value that will be search for in the current
     *                        collection.
     *
     * @psalm-return int|null - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int
    {
        foreach ($this->elements as $key => $element) {
            if ($search_value === $element) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Returns a `Vector` containing the values of the current
     * `Vector`.
     *
     * @psalm-return Vector<T>
     */
    public function values(): Vector
    {
        return new Vector($this->elements);
    }

    /**
     * Returns a `Vector` containing the keys of the current `Vector`.
     *
     * @psalm-return Vector<int>
     */
    public function keys(): Vector
    {
        return new Vector(Vec\keys($this->elements));
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `Vector` remain unchanged in the
     * returned `Vector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback containing the condition to apply to the current
     *                                 `Vector` values
     *
     * @psalm-return Vector<T> - a Vector containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): Vector
    {
        return new Vector(Dict\filter($this->elements, $fn));
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `Vector` remain unchanged in the
     * returned `Vector`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(int, T): bool) $fn - The callback containing the condition to apply to the current
     *                                     `Vector` keys and values
     *
     * @psalm-return Vector<T> - a `Vector` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `Vector`
     */
    public function filterWithKey(callable $fn): Vector
    {
        return new Vector(Dict\filter_with_key($this->elements, $fn));
    }

    /**
     * Returns a `Vector` after an operation has been applied to each value
     * in the current `Vector`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `Vector` to the
     * returned `Vector`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(T): Tu) $fn - The callback containing the operation to apply to the current
     *                               `Vector` values
     *
     * @psalm-return   Vector<Tu> - a `Vector` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): Vector
    {
        return new Vector(Dict\map($this->elements, $fn));
    }

    /**
     * Returns a `Vector` after an operation has been applied to each key and
     * value in the current `Vector`.
     *
     * Every key and value in the current `Vector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `Vector` to the returned
     * `Vector`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, T): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `Vector` keys and values
     *
     * @psalm-return   Vector<Tu> - a `Vector` containing the values after a user-specified
     *                        operation on the current `Vector`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): Vector
    {
        return new Vector(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `Vector` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `VectorInterface` and the provided `iterable`.
     *
     * If the number of elements of the `Vector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `VectorInterface`.
     *
     * @psalm-return   Vector<array{0: T, 1: Tu}> - The `Vector` that combines the values of the current
     *           `Vector` with the provided `iterable`.
     */
    public function zip(iterable $iterable): Vector
    {
        return new Vector(Vec\zip($this, $iterable));
    }

    /**
     * Returns a `Vector` containing the first `n` values of the current
     * `Vector`.
     *
     * The returned `Vector` will always be a proper subset of the current
     * `Vector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `Vector`
     *
     * @psalm-return Vector<T> - A `Vector` that is a proper subset of the current
     *           `Vector` up to `n` elements.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     */
    public function take(int $n): Vector
    {
        return new Vector(Dict\take($this->elements, $n));
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `Vector` will always be a proper subset of the current
     * `Vector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return Vector<T> - A `Vector` that is a proper subset of the current
     *           `Vector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): Vector
    {
        return new Vector(Dict\take_while($this->elements, $fn));
    }

    /**
     * Returns a `Vector` containing the values after the `n`-th element of
     * the current `Vector`.
     *
     * The returned `Vector` will always be a proper subset of the current
     * `VectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `Vector`.
     *
     * @psalm-return Vector<T> - A `Vector` that is a proper subset of the current
     *           `Vector` containing values after the specified `n`-th
     *           element.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     */
    public function drop(int $n): Vector
    {
        return new Vector(Dict\drop($this->elements, $n));
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `Vector` will always be a proper subset of the current
     * `Vector`.
     *
     * @psalm-param (callable(T): bool) $fn - The callback used to determine the starting element for the
     *              returned `Vector`.
     *
     * @psalm-return Vector<T> - A `Vector` that is a proper subset of the current
     *           `Vector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): Vector
    {
        return new Vector(Dict\drop_while($this->elements, $fn));
    }

    /**
     * Returns a subset of the current `Vector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `Vector` will always be a proper subset of this
     * `Vector`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `Vector`
     * @psalm-param  int $len   - The length of the returned `Vector`
     *
     * @psalm-return Vector<T> - A `Vector` that is a proper subset of the current
     *           `Vector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     *
     * @throws Psl\Exception\InvariantViolationException If $start or $len are negative.
     */
    public function slice(int $start, int $len): Vector
    {
        return new Vector(Dict\slice($this->elements, $start, $len));
    }
}
