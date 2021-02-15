<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Arr;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * @template   Tk of array-key
 * @template   Tv
 *
 * @implements MutableMapInterface<Tk, Tv>
 */
final class MutableMap implements MutableMapInterface
{
    /**
     * @psalm-var array<Tk, Tv> $elements
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
     *                  current collection is empty
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
     *                  current collection is empty
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
     * Returns a `MutableVector` containing the values of the current
     * `MutableMap`.
     *
     * @psalm-return MutableVector<Tv>
     */
    public function values(): MutableVector
    {
        return new MutableVector(Vec\values($this->elements));
    }

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableMap`.
     *
     * @psalm-return MutableVector<Tk>
     */
    public function keys(): MutableVector
    {
        return new MutableVector(Vec\keys($this->elements));
    }

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
     * @psalm-return MutableMap<Tk, Tv> - a MutableMap containing the values after a user-specified condition
     *                        is applied
     */
    public function filter(callable $fn): MutableMap
    {
        return new MutableMap(Dict\filter($this->elements, $fn));
    }

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
    public function filterWithKey(callable $fn): MutableMap
    {
        return new MutableMap(Dict\filter_with_key($this->elements, $fn));
    }

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
     * @psalm-return   MutableMap<Tk, Tu> - a `MutableMap` containing key/value pairs after a user-specified
     *                        operation is applied
     */
    public function map(callable $fn): MutableMap
    {
        return new MutableMap(Dict\map($this->elements, $fn));
    }

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
     * @psalm-return   MutableMap<Tk, Tu> - a `MutableMap` containing the values after a user-specified
     *                        operation on the current `MutableMap`'s keys and values is
     *                        applied
     */
    public function mapWithKey(callable $fn): MutableMap
    {
        return new MutableMap(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `MutableMap` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableMap` and the provided `iterable`.
     *
     * If the number of elements of the `MutableMap` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `MutableMap`.
     *
     * @psalm-return   MutableMap<Tk, array{0: Tv, 1: Tu}> - The `MutableMap` that combines the values of the current
     *           `MutableMap` with the provided `iterable`.
     */
    public function zip(iterable $iterable): MutableMap
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

            $array = Dict\drop($array, 1);

            $elements[$k] = [$v, $u];
        }

        return new MutableMap($elements);
    }

    /**
     * Returns a `MutableMap` containing the first `n` values of the current
     * `MutableMap`.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param $n - The last element that will be included in the returned
     *             `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *           `MutableMap` up to `n` elements.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     */
    public function take(int $n): MutableMap
    {
        return new MutableMap(Dict\take($this->elements, $n));
    }

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *           `MutableMap` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableMap
    {
        return new MutableMap(Dict\take_while($this->elements, $fn));
    }

    /**
     * Returns a `MutableMap` containing the values after the `n`-th element of
     * the current `MutableMap`.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `MutableMap`.
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *           `MutableMap` containing values after the specified `n`-th
     *           element.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     */
    public function drop(int $n): MutableMap
    {
        return new MutableMap(Dict\drop($this->elements, $n));
    }

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `MutableMap`.
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *           `MutableMap` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableMap
    {
        return new MutableMap(Dict\drop_while($this->elements, $fn));
    }

    /**
     * Returns a subset of the current `MutableMap` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableMap` will always be a proper subset of this
     * `MutableMap`.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `MutableMap`
     * @psalm-param  int $len   - The length of the returned `MutableMap`
     *
     * @psalm-return MutableMap<Tk, Tv> - A `MutableMap` that is a proper subset of the current
     *           `MutableMap` starting at `$start` up to but not including the
     *           element `$start + $len`.
     *
     * @throws Psl\Exception\InvariantViolationException If $start or $len are negative.
     */
    public function slice(int $start, int $len): MutableMap
    {
        return new MutableMap(Dict\slice($this->elements, $start, $len));
    }

    /**
     * Stores a value into the current map with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * It returns the current map, meaning changes made to the current
     * map will be reflected in the returned map.
     *
     * @psalm-param  Tk $k - The key to which we will set the value
     * @psalm-param  Tv $v - The value to set
     *
     * @psalm-return MutableMap<Tk, Tv> - Returns itself
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     */
    public function set($k, $v): MutableMap
    {
        Psl\invariant($this->contains($k), 'Key (%s) is out-of-bounds.', $k);

        $this->elements[$k] = $v;

        return $this;
    }

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current map associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current map, meaning changes made to the current map
     * will be reflected in the returned map.
     *
     * @psalm-param  iterable<Tk, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return MutableMap<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): MutableMap
    {
        foreach ($iterable as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    /**
     * Add a value to the map and return the map itself.
     *
     * @psalm-param  Tk $k - The key to which we will add the value
     * @psalm-param  Tv $v - The value to set
     *
     * @psalm-return MutableMap<Tk, Tv> - Returns itself
     */
    public function add($k, $v): MutableMap
    {
        $this->elements[$k] = $v;

        return $this;
    }

    /**
     * For every element in the provided iterable, add the value into the current map.
     *
     * @psalm-param  iterable<Tk, Tv> $iterable - The `iterable` with the new values to add
     *
     * @psalm-return MutableMap<Tk, Tv> - Returns itself
     */
    public function addAll(iterable $iterable): MutableMap
    {
        foreach ($iterable as $k => $v) {
            $this->add($k, $v);
        }

        return $this;
    }

    /**
     * Removes the specified key (and associated value) from the current
     * map.
     *
     * If the key is not in the current map, the current map is
     * unchanged.
     *
     * It the current map, meaning changes made to the current map
     * will be reflected in the returned map.
     *
     * @psalm-param  Tk $k - The key to remove
     *
     * @psalm-return MutableMap<Tk, Tv> - Returns itself
     */
    public function remove($k): MutableMap
    {
        if ($this->contains($k)) {
            unset($this->elements[$k]);
        }

        return $this;
    }

    /**
     * Removes all items from the map.
     *
     * @psalm-return MutableMap<Tk, Tv>
     */
    public function clear(): MutableMap
    {
        $this->elements = [];

        return $this;
    }
}
