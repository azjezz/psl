<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Arr;
use Psl\Iter;

/**
 * @template Tv
 *
 * @implements MutableVector<Tv>
 */
final class Vector implements MutableVector
{
    /**
     * @psalm-var ImmVector<Tv> iterable
     */
    private ImmVector $immutable;

    /**
     * @psalm-param iterable<Tv> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->immutable = new ImmVector($items);
    }

    /**
     * Get access to the items in the collection.
     *
     * @psalm-return array<int, Tv>
     */
    public function items(): array
    {
        return $this->immutable->items();
    }

    /**
     * Is the collection empty?
     */
    public function isEmpty(): bool
    {
        return $this->immutable->isEmpty();
    }

    /**
     * Get the number of items in the collection.
     */
    public function count(): int
    {
        return $this->immutable->count();
    }

    /**
     * Returns the value at the specified key in the current vector.
     *
     * @psalm-param  int $k
     *
     * @psalm-return Tv
     */
    public function at($k)
    {
        return $this->immutable->at($k);
    }

    /**
     * Determines if the specified key is in the current vector.
     *
     * @psalm-param int $k
     */
    public function containsKey($k): bool
    {
        return $this->immutable->containsKey($k);
    }

    /**
     * Returns the value at the specified key in the current vector.
     *
     * @psalm-param  int $k
     *
     * @psalm-return Tv|null
     */
    public function get($k)
    {
        return $this->immutable->get($k);
    }

    /**
     * Returns a Vector containing the values of the current Vector that meet a supplied
     * condition.
     *
     * @psalm-param (callable(Tv): bool) $fn
     *
     * @psalm-return Vector<Tv>
     */
    public function filter(callable $fn): Vector
    {
        $this->immutable = $this->immutable->filter($fn);

        return $this;
    }

    /**
     * Returns a Vector containing the values of the current Vector that meet a supplied
     * condition applied to its keys and values.
     *
     * @psalm-param (callable(int, Tv): bool) $fn
     *
     * @psalm-return Vector<Tv>
     */
    public function filterWithKey(callable $fn): Vector
    {
        $this->immutable = $this->immutable->filterWithKey($fn);

        return $this;
    }

    /**
     * Returns a Vector containing the values of the current
     * Vector. Essentially a copy of the current Vector.
     *
     * @psalm-return Vector<Tv>
     */
    public function values(): Vector
    {
        return new Vector($this->items());
    }

    /**
     * Returns a `Vector` containing the keys of the current `Vector`.
     *
     * @psalm-return Vector<int>
     */
    public function keys(): Vector
    {
        /** @psalm-var Vector<int> */
        return new Vector($this->immutable->keys());
    }

    /**
     * Returns a `Vector` containing the results of applying an operation to each
     * value in the current `Vector`.
     *
     * `map()`'s result contains a value for every value in the current `Vector`;
     * unlike `filter()`, where only values that meet a certain criterion are
     * included in the resulting `Vector`.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the
     *                    current `Vector`'s values.
     *
     * @psalm-return   Vector<Tu> - A `Vector` containing the results of applying a user-specified
     *           operation to each value of the current `Vector` in turn.
     */
    public function map(callable $fn): Vector
    {
        return new Vector($this->immutable->map($fn));
    }

    /**
     * Returns a `Vector` containing the values after an operation has been
     * applied to each key and value in the current `Vector`.
     *
     * Every key and value in the current `Vector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, Tv): Tu) $fn - The callback containing the operation to apply to the
     *              `Vector` keys and values.
     *
     * @psalm-return   Vector<Tu> - a `Vector` containing the values after a user-specified
     *           operation on the current Vector's keys and values is applied.
     */
    public function mapWithKey(callable $fn): Vector
    {
        return new Vector(Iter\map_with_key($this->items(), $fn));
    }

    /**
     * Returns a `Vector` where each element is a `Pair` that combines the
     * element of the current `Vector` and the provided `iterable`.
     *
     * If the number of elements of the `Vector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `Vector`.
     *
     * @psalm-return Vector<Pair<Tv, Tu>> - The `Vector` that combines the values of the current
     *           `Vector` with the provided `iterable`.
     */
    public function zip(iterable $iterable): Vector
    {
        if (!Arr\is_array($iterable)) {
            $iterable = Iter\to_array($iterable);
        }

        /** @psalm-var Vector<Tu> $other */
        $other = new Vector($iterable);
        $min = ($x = $other->count()) > ($y = $this->count()) ? $y : $x;

        /** @psalm-var Vector<Pair<Tv, Tu>> $values */
        $values = new Vector();
        for ($i = 0; $i < $min; ++$i) {
            $values->add(new Pair($this->at($i), $other->at($i)));
        }

        /** @psalm-var Vector<Pair<Tv, Tu>> */
        return $values;
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
     * @psalm-param  int $n - The last element that will be included in the returned
     *               `Vector`
     *
     * @psalm-return Vector<Tv> - A `Vector` that is a proper subset of the current
     *           `Vector` up to `n` elements.
     */
    public function take(int $n): Vector
    {
        $this->immutable = $this->immutable->take($n);

        return $this;
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return Vector<Tv> - A `Vector` that is a proper subset of the current
     *           `Vector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): Vector
    {
        $this->immutable = $this->immutable->takeWhile($fn);

        return $this;
    }

    /**
     * Returns a `Vector` containing the values after the `n`-th element of
     * the current `Vector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `Vector`.
     *
     * @psalm-return Vector<Tv> - A `Vector` that is a proper subset of the current
     *           `Vector` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): Vector
    {
        $this->immutable = $this->immutable->drop($n);

        return $this;
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `Vector`.
     *
     * @psalm-return Vector<Tv> - A `Vector` that is a proper subset of the current
     *           `Vector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): Vector
    {
        $this->immutable = $this->immutable->dropWhile($fn);

        return $this;
    }

    /**
     * Returns a subset of the current `Vector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * @psalm-param  int $start - The starting key of this Vector to begin the returned
     *                   `Vector`
     * @psalm-param  int $len   - The length of the returned `Vector`
     *
     * @psalm-return Vector<Tv> - A `Vector` that is a proper subset of the current
     *           `Vector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): Vector
    {
        $this->immutable = $this->immutable->slice($start, $len);

        return $this;
    }

    /**
     * Returns a `Vector` that is the concatenation of the values of the
     * current `Vector` and the values of the provided `iterable`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                       `Vector`.
     *
     * @psalm-return   Vector<Tu> - The concatenated `Vector`.
     */
    public function concat(iterable $iterable): Vector
    {
        /** @psalm-var array<int, Tu> $values */
        $values = Arr\concat($this->items(), $iterable);

        return new Vector($values);
    }

    /**
     * Returns the first value in the current `Vector`.
     *
     * @psalm-return null|Tv - The first value in the current `Vector`, or `null` if the
     *           current `Vector` is empty.
     */
    public function first()
    {
        return $this->get(0);
    }

    /**
     * Returns the first key in the current `Vector`.
     *
     * @psalm-return int|null - The first key in the current `Vector`, or `null` if the
     *                  current `Vector` is empty
     */
    public function firstKey(): ?int
    {
        return $this->containsKey(0) ? 0 : null;
    }

    /**
     * Returns the last value in the current `Vector`.
     *
     * @psalm-return null|Tv - The last value in the current `Vector`, or `null` if the
     *           current `Vector` is empty.
     */
    public function last()
    {
        return $this->get($this->count() - 1);
    }

    /**
     * Returns the last key in the current `Vector`.
     *
     * @psalm-return int|null - The last key in the current `Vector`, or `null` if the
     *                  current `Vector` is empty
     */
    public function lastKey(): ?int
    {
        return ($x = $this->count() - 1) < 0 ? null : $x;
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param  Tv $search_value - The value that will be search for in the current
     *                        `Vector`.
     *
     * @psalm-return int|null - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int
    {
        return $this->immutable->linearSearch($search_value);
    }

    /**
     * Retrieve an external iterator.
     *
     * @psalm-return Iter\Iterator<int, Tv>
     */
    public function getIterator(): Iter\Iterator
    {
        return $this->immutable->getIterator();
    }

    /**
     * Get an array copy of the the collection.
     *
     * @psalm-return array<int, Tv>
     */
    public function toArray(): array
    {
        return $this->immutable->toArray();
    }

    /**
     * Add a value to the collection and return the collection itself.
     *
     * @psalm-param  Tv $value
     *
     * @psalm-return Vector<Tv>
     */
    public function add($value): Vector
    {
        return $this->addAll([$value]);
    }

    /**
     * For every element in the provided iterable, append a value into the current vector.
     *
     * @psalm-param  iterable<Tv> $values
     *
     * @psalm-return Vector<Tv>
     */
    public function addAll(iterable $values): Vector
    {
        $this->immutable = new ImmVector(Arr\concat($this->toArray(), $values));

        return $this;
    }

    /**
     * Removes all items from the collection.
     *
     * @return Vector<Tv>
     */
    public function clear(): Vector
    {
        $this->immutable = new ImmVector();

        return $this;
    }

    /**
     * Stores a value into the current vector with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * `$vec->set($k,$v)` is semantically equivalent to `$vec[$k] = $v`
     * (except that `set()` returns the current vector).
     *
     * @psalm-param  int $k - The key to which we will set the value
     * @psalm-param  Tv  $v - The value to set
     *
     * @psalm-return Vector<Tv> - Returns itself
     */
    public function set($k, $v): Vector
    {
        /** @var array<int, Tv> $arr */
        $arr = $this->toArray();
        Psl\invariant(Arr\contains_key($arr, $k), 'Key (%d) is out-of-bound. If you want to add a value even if a key is not present, use `add()`.', $k);
        $arr[$k] = $v;
        $this->immutable = new ImmVector($arr);

        return $this;
    }

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current vector associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If a key is not present the current vector that is present in the
     * `iterable`, an exception is thrown. If you want to add a value even if a
     * key is not present, use `addAll()`.
     *
     * @psalm-param  iterable<int, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return Vector<Tv> - Returns itself
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function setAll(iterable $iterable): Vector
    {
        /** @var array<int, Tv> $arr */
        $arr = $this->toArray();
        foreach ($iterable as $k => $v) {
            Psl\invariant(Arr\contains_key($arr, $k), 'Key (%d) is out-of-bound. If you want to add a value even if a key is not present, use `addAll()`.', $k);

            $arr[$k] = $v;
        }

        $this->immutable = new ImmVector($arr);

        return $this;
    }

    /**
     * Removes the specified key (and associated value) from the current
     * collection.
     *
     * If the key is not in the current vector, the current vector is
     * unchanged.
     *
     * It the current vector, meaning changes made to the current vector
     * will be reflected in the returned collection.
     *
     * @psalm-param  int $k - The key to remove
     *
     * @psalm-return Vector<Tv> - Returns itself
     */
    public function removeKey($k): Vector
    {
        if ($this->immutable->containsKey($k)) {
            $arr = $this->toArray();
            unset($arr[$k]);
            $this->immutable = new ImmVector($arr);
        }

        return $this;
    }

    /**
     * Returns a deep, immutable copy (`ImmVector`) of this `Vector`.
     *
     * @psalm-return ImmVector<Tv> - an `ImmVector` that is a deep copy of this `Vector`
     */
    public function immutable(): ImmVector
    {
        return clone $this->immutable;
    }
}
