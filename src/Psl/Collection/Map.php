<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Iter;

/**
 * `Map` is an ordered dictionary-style collection.
 *
 * Like all objects in PHP, `Map`s have reference-like semantics. When a caller
 * passes a `Map` to a callee, the callee can modify the `Map` and the caller
 * will see the changes. `Map`s do not have "copy-on-write" semantics.
 *
 * `Map`s preserve insertion order of key/value pairs. When iterating over a
 * `Map`, the key/value pairs appear in the order they were inserted.
 *
 * `Map`s only support `int` keys and `string` keys. If a key of a different
 * type is used, an exception will be thrown.
 *
 * `Map`s do not support iterating while new keys are being added or elements
 * are being removed. When a new key is added or an element is removed, all
 * iterators that point to the `Map` shall be considered invalid.
 *
 * @template      Tk of array-key
 * @template      Tv
 *
 * @implements MutableMap<Tk, Tv>
 */
final class Map implements MutableMap
{
    /**
     * @psalm-var array<int, array{0: Tk, 1: Tv }>
     */
    private array $elements = [];

    /**
     * @psalm-param iterable<Tk, Tv> $values
     */
    public function __construct(iterable $values)
    {
        foreach ($values as $k => $v) {
            $this->elements[] = [$k, $v];
        }
    }

    /**
     * Add a value to the collection and return the collection itself.
     *
     * @param Pair<Tk, Tv> $value
     *
     * @psalm-return Map<Tk, Tv>
     */
    public function add($value): Map
    {
        Psl\invariant($value instanceof Pair, 'Expected $value to be instance of %s, %s given.', Pair::class, gettype($value));
        $this->elements[] = [$value->first(), $value->last()];

        /** @var Map<Tk, Tv> */
        return $this;
    }

    /**
     * For every element in the provided iterable, append a value into the current map.
     *
     * @psalm-param  iterable<Pair<Tk, Tv>> $values
     *
     * @psalm-return Map<Tk, Tv>
     */
    public function addAll(iterable $values): Map
    {
        foreach ($values as $pair) {
            Psl\invariant($pair instanceof Pair, 'Expected $iterable to only contain instances of %s, %s given.', Pair::class, gettype($pair));
            $this->elements[] = [$pair->first(), $pair->last()];
        }

        /** @var Map<Tk, Tv> */
        return $this;
    }

    /**
     * Removes all items from the collection.
     *
     * @psalm-return Map<Tk, Tv>
     */
    public function clear(): Map
    {
        $this->elements = [];

        /** @var Map<Tk, Tv> */
        return $this;
    }

    /**
     * Get access to the items in the collection.
     *
     * @psalm-return iterable<int, Pair<Tk, Tv>>
     * @return Pair[]
     */
    public function items(): Vector
    {
        return $this->mapWithKey(
        /**
         * @psalm-param  Tk $k
         * @psalm-param  Tv $v
         *
         * @psalm-return Pair<Tk, Tv>
         */
            fn ($k, $v) => new Pair($k, $v),
        )->values();
    }

    /**
     * Is the collection empty?
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the collection.
     */
    public function count(): int
    {
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the the collection.
     *
     * @psalm-return array<Tk, Tv>
     */
    public function toArray(): array
    {
        $elements = [];
        foreach ($this->elements as [$key, $value]) {
            $elements[$key] = $value;
        }

        return $elements;
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv
     */
    public function at($k)
    {
        if (!$this->containsKey($k)) {
            Psl\invariant_violation('Key (%s) is out-of-bound.', $k);
        }

        /** @psalm-var Tv */
        return $this->get($k);
    }

    /**
     * Determines if the specified key is in the current collection.
     *
     * @psalm-param Tk $k
     */
    public function containsKey($k): bool
    {
        foreach ($this->elements as [$key, $value]) {
            if ($k === $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv|null
     */
    public function get($k)
    {
        foreach ($this->elements as [$key, $value]) {
            if ($k === $key) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Determines if the specified key is in the current `Map`.
     *
     * This function is interchangeable with `containsKey()`.
     *
     * @psalm-param  Tk $m
     *
     * @psalm-return bool - `true` if the value is in the current `Map`; `false` otherwise
     */
    public function contains($m): bool
    {
        return $this->containsKey($m);
    }

    /**
     * Returns a `Vector` containing the values of the current
     * `Map`.
     *
     * The indices of the `Vector will be integer-indexed starting from 0,
     * no matter the keys of the `Map`.
     *
     * @psalm-return Vector<Tv> - a `Vector` containing the values of the current
     *                    `Map`
     */
    public function values(): Vector
    {
        $values = [];
        foreach ($this->elements as [$key, $value]) {
            $values[] = $value;
        }

        return new Vector($values);
    }

    /**
     * Returns a `Vector` containing the keys of the current `Map`.
     *
     * @psalm-return Vector<Tk> - a `Vector` containing the keys of the current
     *                    `Map`
     */
    public function keys(): Vector
    {
        $keys = [];
        foreach ($this->elements as [$key, $value]) {
            $keys[] = $key;
        }

        return new Vector($keys);
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
     *                 operation is applied
     */
    public function map(callable $fn): Map
    {
        /** @var array<int, array{0: Tk, 1: Tu} $mapped */
        $mapped = [];
        foreach ($this->elements as [$k, $v]) {
            $mapped[] = [$k, $fn($v)];
        }

        /** @var Map<Tk, Tu> $map */
        $map = new Map([]);
        $map->elements = $mapped;

        return $map;
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
     *                 operation on the current `Map`'s keys and values is
     *                 applied
     */
    public function mapWithKey(callable $fn): Map
    {
        /** @var Map<Tk, Tu> $map */
        $map = new Map([]);
        foreach ($this->elements as [$k, $v]) {
            $map->set($k, $fn($k, $v));
        }

        return $map;
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
     *                 is applied
     */
    public function filter(callable $fn): Map
    {
        /** @var Map<Tk, Tv> $map */
        $map = new Map([]);
        foreach ($this->elements as [$k, $v]) {
            if ($fn($v)) {
                $map->set($k, $v);
            }
        }

        return $map;
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
     *                 condition is applied to the keys and values of the current
     *                 `Map`
     */
    public function filterWithKey(callable $fn): Map
    {
        /** @var Map<Tk, Tv> $map */
        $map = new Map([]);
        foreach ($this->elements as [$k, $v]) {
            if ($fn($k, $v)) {
                $map->set($k, $v);
            }
        }

        return $map;
    }

    /**
     * Returns a `Map` where each value is a `Pair` that combines the
     * value of the current `Map` and the provided `iterable`.
     *
     * If the number of values of the current `Map` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * The keys associated with the current `Map` remain unchanged in the
     * returned `Map`.
     *
     * @psalm-template Tu
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                               elements of the current `Map`
     *
     * @psalm-return   Map<Tk, Pair<Tv, Tu>> - The `Map` that combines the values of the current
     *                 `Map` with the provided `iterable`
     */
    public function zip(iterable $iterable): Map
    {
        /** @var Map<Tk, Pair<Tv, Tu>> $map */
        $map = new Map([]);
        /** @psalm-var array<int, array{0: Tk, 1: Pair<Tv, Tu>}> $values */
        $other = new ImmVector($iterable);
        $i = 0;
        foreach ($this->elements as [$k, $v]) {
            if (!$other->containsKey($i)) {
                break;
            }

            $map->set($k, new Pair($v, $other->at($i)));
            ++$i;
        }

        return $map;
    }

    /**
     * Returns a `Map` containing the first `n` key/values of the current
     * `Map`.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element that will be included in the `Map`
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *                 `Map` up to `n` elements
     */
    public function take(int $n): Map
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `Map` containing the keys and values of the current
     * `Map` up to but not including the first value that produces `false`
     * when passed to the specified callback.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping condition
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *                 `Map` up until the callback returns `false`
     */
    public function takeWhile(callable $fn): Map
    {
        /** @var Map<Tk, Tv> $map */
        $map = new Map([]);
        foreach ($this->elements as [$key, $value]) {
            if (!$fn($value)) {
                break;
            }

            $map->set($key, $value);
        }

        return $map;
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
     * @psalm-param  int $n - The last element to be skipped; the `$n+1` element will be the
     *               first one in the returned `Map`
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *                 `Map` containing values after the specified `n`-th
     *                 element
     */
    public function drop(int $n): Map
    {
        return $this->slice($n, $this->count() - $n);
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @psalm-param (callable(Tk): bool) $fn - The callback used to determine the starting element for the
     *                                 current `Map`
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *                 `Map` starting after the callback returns `true`
     */
    public function dropWhile(callable $fn): Map
    {
        $failed = false;
        /** @var Map<Tk, Tv> $map */
        $map = new Map([]);
        foreach ($this->elements as [$key, $value]) {
            if (!$failed && !$fn($value)) {
                $failed = true;
            }

            if ($failed) {
                $map->set($key, $value);
            }
        }

        return $map;
    }

    /**
     * Returns a subset of the current `Map` starting from a given key
     * location up to, but not including, the element at the provided length from
     * the starting key location.
     *
     * `$start` is 0-based. `$len` is 1-based. So `slice(0, 2)` would return the
     * keys and values at key location 0 and 1.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @psalm-param  int $start - The starting key location of the current `Map` for
     *                   the featured `Map`
     * @psalm-param  int $len   - The length of the returned `Map`
     *
     * @psalm-return Map<Tk, Tv> - A `Map` that is a proper subset of the current
     *                     `Map` starting at `$start` up to but not including the
     *                     element `$start + $len`
     */
    public function slice(int $start, int $len): Map
    {
        Psl\invariant($start >= 0, 'Start offset must be non-negative');
        Psl\invariant($len >= 0, 'Length must be non-negative');
        if (0 === $len) {
            return new Map([]);
        }

        $i = 0;

        /** @var Map<Tk, Tv> $map */
        $map = new Map([]);
        foreach ($this->elements as [$key, $value]) {
            if ($i++ < $start) {
                continue;
            }

            $map->set($key, $value);
            if ($i >= $start + $len) {
                break;
            }
        }

        return $map;
    }

    /**
     * Returns a `Vector` that is the concatenation of the values of the
     * current `Map` and the values of the provided `iterable`.
     *
     * The provided `iterable` is concatenated to the end of the current
     * `Map` to produce the returned `Vector`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param    iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                               `Map`
     *
     * @psalm-return   Vector<Tv>
     *
     * @return Vector - The integer-indexed concatenated `Vector`
     */
    public function concat(iterable $iterable): Vector
    {
        return $this->values()->concat($iterable);
    }

    /**
     * Returns the first value in the current `Map`.
     *
     * @psalm-return Tv|null - The first value in the current `Map`,  or `null` if the
     *                 `Map` is empty
     */
    public function first()
    {
        /** @psalm-var array{0: Tk, 1: Tv}|null $first */
        $first = Iter\first($this->elements);

        return null !== $first ? $first[1] : null;
    }

    /**
     * Returns the first key in the current `Map`.
     *
     * @psalm-return Tk|null - The first key in the current `Map`, or `null` if the
     *                 `Map` is empty
     */
    public function firstKey()
    {
        /** @psalm-var array{0: Tk, 1: Tv}|null $first */
        $first = Iter\first($this->elements);

        /** @psalm-var null|Tk */
        return null !== $first ? $first[0] : null;
    }

    /**
     * Returns the last value in the current `Map`.
     *
     * @psalm-return Tv|null - The last value in the current `Map`, or `null` if the
     *                 `Map` is empty
     */
    public function last()
    {
        $last = null;
        foreach ($this->elements as [$k, $v]) {
            $last = $v;
        }

        return $last;
    }

    /**
     * Returns the last key in the current `Map`.
     *
     * @psalm-return Tk|null - The last key in the current `Map`, or null if the
     *                 `Map` is empty
     */
    public function lastKey()
    {
        $last = null;
        foreach ($this->elements as [$k, $v]) {
            $last = $k;
        }

        return $last;
    }

    /**
     * Removes the specified key (and associated value) from the current `Map`.
     *
     * This method is interchangeable with `removeKey()`.
     *
     * Future changes made to the current `Map` ARE reflected in the returned
     * `Map`, and vice-versa.
     *
     * @psalm-param  Tk $k - The key to remove
     *
     * @psalm-return Map<Tk, Tv> - Returns itself
     */
    public function remove($k): Map
    {
        $index = null;
        foreach ($this->elements as $i => [$key, $value]) {
            if ($k === $key) {
                $index = $i;
            }
        }

        if (null !== $index) {
            unset($this->elements[$index]);
        }

        /** @var Map<Tk, Tv> */
        return $this;
    }

    /**
     * Returns a new `Map` with the keys that are in the current `Map`, but not
     * in the provided `iterable`.
     *
     * @psalm-param  iterable<Tk, Tv> $iterable - The `iterable` on which to compare the keys
     *
     * @psalm-return Map<Tk, Tv> - A `Map` containing the keys (and associated values) of the
     *                 current `Map` that are not in the `iterable`
     */
    public function differenceByKey(iterable $iterable): Map
    {
        /** new Map<Tk, Tv> */
        return new Map(Iter\diff_by_key($this->getIterator(), $iterable));
    }

    /**
     * Returns a deep, immutable copy (`ImmMap`) of this `Map`.
     *
     * @psalm-return ImmMap<Tk, Tv> - an `ImmMap` that is a deep copy of this `Map`
     */
    public function immutable(): ImmMap
    {
        /** @var ImmMap<Tk, Tv> */
        return new ImmMap($this->getIterator());
    }

    /**
     * Stores a value into the current `Map` with the specified key, overwriting
     * the previous value associated with the key.
     *
     * This method is equivalent to `Map::add()`. If the key to set does not exist,
     * it is created. This is inconsistent with, for example, `Vector::set()`
     * where if the key is not found, an exception is thrown.
     *
     * `$map->set($k,$v)` is equivalent to `$map[$k] = $v` (except that `set()`
     * returns the current `Map`).
     *
     * Future changes made to the current `Map` ARE reflected in the returned
     * `Map`, and vice-versa.
     *
     * @psalm-param  Tk $k - The key to which we will set the value
     * @psalm-param  Tv $v - The value to set
     *
     * @psalm-return Map<Tk, Tv> - Returns itself
     */
    public function set($k, $v): Map
    {
        if ($this->contains($k)) {
            $this->remove($k);
        }

        $this->elements[] = [$k, $v];

        /** @var Map<Tk, Tv> */
        return $this;
    }

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current `Map` associaed with each key, overwriting the previous value
     * associated with the key.
     *
     * This method is equivalent to `Map::addAll()`. If a key to set does not
     * exist in the Map that does exist in the `iterable`, it is created. This
     * is inconsistent with, for example, the method `Vector::setAll()` where if
     * a key is not found, an exception is thrown.
     *
     * Future changes made to the current `Map` ARE reflected in the returned
     * `Map`, and vice-versa.
     *
     * @psalm-param  iterable<Tk, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return Map<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): Map
    {
        foreach ($iterable as $k => $v) {
            $this->set($k, $v);
        }

        /** @var Map<Tk, Tv> */
        return $this;
    }

    /**
     * Removes the specified key (and associated value) from the current `Map`.
     *
     * This method is interchangeable with `remove()`.
     *
     * Future changes made to the current `Map` ARE reflected in the returned
     * `Map`, and vice-versa.
     *
     * @psalm-param  Tk $k - The key to remove
     *
     * @psalm-return Map<Tk, Tv> - Returns itself
     */
    public function removeKey($k): Map
    {
        return $this->remove($k);
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iter\Iterator<Tk, Tv> - An instance of an object implementing Iterator
     */
    public function getIterator(): Iter\Iterator
    {
        /** @var \Generator<Tk, Tv, mixed, void> $gen */
        $gen = (function (): \Generator {
            foreach ($this->elements as [$k, $v]) {
                yield $k => $v;
            }
        })();

        /** @psalm-var Iter\Iterator<Tk, Tv> */
        return new Iter\Iterator($gen);
    }
}
