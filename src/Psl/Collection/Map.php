<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Arr;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @implements MapInterface<Tk, Tv>
 */
final class Map implements MapInterface
{
    /**
     * @psalm-var array<Tk, Tv> $elements
     *
     * @psalm-readonly
     */
    private array $elements;

    /**
     * AbstractMap constructor.
     *
     * @psalm-param iterable<Tk, Tv> $elements
     */
    public function __construct(iterable $elements)
    {
        $this->elements = Dict\from_iterable($elements);
    }

    /**
     * Returns the first value in the current collection.
     *
     * @psalm-return Tv|null - The first value in the current collection, or `null` if the
     *           current collection is empty.
     */
    public function first()
    {
        return Arr\first($this->elements);
    }

    /**
     * Returns the first key in the current collection.
     *
     * @psalm-return Tk|null - The first key in the current collection, or `null` if the
     *                  current collection is empty.
     */
    public function firstKey()
    {
        return Arr\first_key($this->elements);
    }

    /**
     * Returns the last value in the current collection.
     *
     * @psalm-return Tv|null - The last value in the current collection, or `null` if the
     *           current collection is empty.
     */
    public function last()
    {
        return Arr\last($this->elements);
    }

    /**
     * Returns the last key in the current collection.
     *
     * @psalm-return Tk|null - The last key in the current collection, or `null` if the
     *                  current collection is empty.
     */
    public function lastKey()
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
     * @psalm-return Tk|null - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value)
    {
        foreach ($this->elements as $key => $element) {
            if ($search_value === $element) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieve an external iterator
     *
     * @psalm-return Iter\Iterator<Tk, Tv>
     */
    public function getIterator(): Iter\Iterator
    {
        return Iter\Iterator::create($this->elements);
    }

    /**
     * Is the map empty?
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the current map.
     */
    public function count(): int
    {
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the current map.
     *
     * @psalm-return array<Tk, Tv>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Get an array copy of the current map.
     *
     * @psalm-return array<Tk, Tv>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     */
    public function at($k)
    {
        return Arr\at($this->elements, $k);
    }

    /**
     * Determines if the specified key is in the current map.
     *
     * @psalm-param Tk $k
     */
    public function contains($k): bool
    {
        return Arr\contains_key($this->elements, $k);
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv|null
     */
    public function get($k)
    {
        return Arr\idx($this->elements, $k);
    }


    /**
     * Returns a `Vector` containing the values of the current
     * `Map`.
     *
     * @psalm-return Vector<Tv>
     */
    public function values(): Vector
    {
        return new Vector(Vec\values($this->elements));
    }

    /**
     * Returns a `Vector` containing the keys of the current `Map`.
     *
     * @psalm-return Vector<Tk>
     */
    public function keys(): Vector
    {
        return new Vector(Vec\keys($this->elements));
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `Map` remain unchanged in the
     * returned `Map`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `Map` values
     *
     * @psalm-return Map<Tk, Tv> - a Map containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): Map
    {
        return new Map(Dict\filter($this->elements, $fn));
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `Map` remain unchanged in the
     * returned `Map`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `Map` keys and values
     *
     * @psalm-return Map<Tk, Tv> - a `Map` containing the values after a user-specified
     *                        condition is applied to the keys and values of the current
     *                        `Map`
     */
    public function filterWithKey(callable $fn): Map
    {
        return new Map(Dict\filter_with_key($this->elements, $fn));
    }

    /**
     * Returns a `Map` after an operation has been applied to each value
     * in the current `Map`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `Map` to the
     * returned `Map`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `Map` values
     *
     * @psalm-return   Map<Tk, Tu> - a `Map` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): Map
    {
        return new Map(Dict\map($this->elements, $fn));
    }

    /**
     * Returns a `Map` after an operation has been applied to each key and
     * value in the current `Map`.
     *
     * Every key and value in the current `Map` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `Map` to the returned
     * `Map`. The keys are only used to help in the mapping operation.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `Map` keys and values
     *
     * @psalm-return   Map<Tk, Tu> - a `Map` containing the values after a user-specified
     *                        operation on the current `Map`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): Map
    {
        return new Map(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `Map` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `Map` and the provided `iterable`.
     *
     * If the number of elements of the `Map` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `Map`.
     *
     * @psalm-return   Map<Tk, array{0: Tv, 1: Tu}> - The `Map` that combines the values of the current
     *           `Map` with the provided `iterable`.
     */
    public function zip(iterable $iterable): Map
    {
        $array = Vec\values($iterable);

        /** @psalm-var array<Tk, array{0: Tv, 1: Tu}> $elements */
        $elements = [];

        foreach ($this->elements as $k => $v) {
            /** @psalm-var Tu|null $u */
            $u = Iter\first($array);
            if (null === $u) {
                break;
            }

            /** @psalm-var iterable<int, Tu> $array */
            $array = Dict\drop($array, 1);

            $elements[$k] = [$v, $u];
        }

        return new Map($elements);
    }

    /**
     * Returns a `Map` containing the first `n` values of the current
     * `Map`.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `Map`
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *           `Map` up to `n` elements.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     */
    public function take(int $n): Map
    {
        return new Map(Dict\take($this->elements, $n));
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *           `Map` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): Map
    {
        return new Map(Dict\take_while($this->elements, $fn));
    }

    /**
     * Returns a `Map` containing the values after the `n`-th element of
     * the current `Map`.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `Map`.
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *           `Map` containing values after the specified `n`-th element.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     */
    public function drop(int $n): Map
    {
        return new Map(Dict\drop($this->elements, $n));
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `Map`.
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *           `Map` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): Map
    {
        return new Map(Dict\drop_while($this->elements, $fn));
    }

    /**
     * Returns a subset of the current `Map` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `Map` will always be a proper subset of this
     * `Map`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `Map`
     * @psalm-param  int $len   - The length of the returned `Map`
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *           `Map` starting at `$start` up to but not including the
     *           element `$start + $len`.
     *
     * @throws Psl\Exception\InvariantViolationException If $start or $len are negative.
     */
    public function slice(int $start, int $len): Map
    {
        return new Map(Dict\slice($this->elements, $start, $len));
    }
}
