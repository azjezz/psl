<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Iter;

/**
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-implements ConstMap<Tk, Tv>
 * @psalm-implements ConstMapAccess<Tk, Tv>
 */
final class ImmMap implements ConstMap
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
     * Get access to the items in the collection.
     *
     * @psalm-return iterable<int, Pair<Tk, Tv>>
     */
    public function items(): iterable
    {
        return $this->mapWithKey(
            /**
             * @psalm-param Tk $k
             * @psalm-param Tv $v
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
     * @psalm-param Tk $k
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
     * Determines if the specified key is in the current `ImmMap`.
     *
     * This function is interchangeable with `containsKey()`.
     *
     * @psalm-param Tk $m
     *
     * @psalm-return bool - `true` if the value is in the current `ImmMap`; `false` otherwise
     */
    public function contains($m): bool
    {
        return $this->containsKey($m);
    }

    /**
     * Returns a `Vector` containing the values of the current
     * `ImmMap`.
     *
     * The indices of the `Vector will be integer-indexed starting from 0,
     * no matter the keys of the `ImmMap`.
     *
     * @psalm-return Vector<Tv> - a `Vector` containing the values of the current
     *                    `ImmMap`
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
     * Returns a `Vector` containing the keys of the current `ImmMap`.
     *
     * @psalm-return Vector<Tk> - a `Vector` containing the keys of the current
     *                    `ImmMap`
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
     * Returns a `ImmMap` after an operation has been applied to each value
     * in the current `ImmMap`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `ImmMap` to the
     * returned `ImmMap`.
     *
     * @psalm-tempalte Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                               `ImmMap` values
     *
     * @psalm-return ImmMap<Tk, Tu> - a `ImmMap` containing key/value pairs after a user-specified
     *                 operation is applied
     */
    public function map(callable $fn): ImmMap
    {
        /** @var array<int, array{0: Tk, 1: Tu}> $elements */
        $elements = [];
        foreach ($this->elements as [$k, $v]) {
            $elements[] = [$k, $fn($v)];
        }

        /** @var ImmMap<Tk, Tu> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `ImmMap` after an operation has been applied to each key and
     * value in the current `ImmMap`.
     *
     * Every key and value in the current `ImmMap` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `ImmMap` to the returned
     * `ImmMap`. The keys are only used to help in the mapping operation.
     *
     * @psalm-tempalte Tu
     *
     * @psalm-param (callable(Tk, Tv): Tu) $fn - The callback containing the operation to apply to the current
     *                                   `ImmMap` keys and values
     *
     * @psalm-return ImmMap<Tk, Tu> - a `ImmMap` containing the values after a user-specified
     *                 operation on the current `ImmMap`'s keys and values is
     *                 applied
     */
    public function mapWithKey(callable $fn): ImmMap
    {
        /** @var array<int, array{0: Tk, 1: Tu}> $elements */
        $elements = [];
        foreach ($this->elements as [$k, $v]) {
            $elements[] = [$k, $fn($k, $v)];
        }

        /** @var ImmMap<Tk, Tu> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `ImmMap` containing the values of the current `ImmMap`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `ImmMap` remain unchanged in the
     * returned `ImmMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                 `ImmMap` values
     *
     * @psalm-return ImmMap<Tk, Tv> - a Map containing the values after a user-specified condition
     *                 is applied
     */
    public function filter(callable $fn): ImmMap
    {
        /** @var array<int, array{0: Tk, 1: Tv}> $elements */
        $elements = [];
        foreach ($this->elements as [$k, $v]) {
            if ($fn($v)) {
                $elements[] = [$k, $v];
            }
        }

        /** @var ImmMap<Tk, Tv> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `ImmMap` containing the values of the current `ImmMap`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `ImmMap` remain unchanged in the
     * returned `ImmMap`; the keys will be used in the filtering process only.
     *
     * @psalm-param (callable(Tk, Tv): bool) $fn - The callback containing the condition to apply to the current
     *                                     `ImmMap` keys and values
     *
     * @psalm-return ImmMap<Tk, Tv> - a `ImmMap` containing the values after a user-specified
     *                 condition is applied to the keys and values of the current
     *                 `ImmMap`
     */
    public function filterWithKey(callable $fn): ImmMap
    {
        /** @var array<int, array{0: Tk, 1: Tv}> $elements */
        $elements = [];
        foreach ($this->elements as [$k, $v]) {
            if ($fn($k, $v)) {
                $elements[] = [$k, $v];
            }
        }

        /** @var ImmMap<Tk, Tv> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `ImmMap` where each value is a `Pair` that combines the
     * value of the current `ImmMap` and the provided `iterable`.
     *
     * If the number of values of the current `ImmMap` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * The keys associated with the current `ImmMap` remain unchanged in the
     * returned `ImmMap`.
     *
     * @psalm-template Tu
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                               elements of the current `ImmMap`
     *
     * @psalm-return ImmMap<Tk, Pair<Tv, Tu>> - The `ImmMap` that combines the values of the current
     *                 `ImmMap` with the provided `iterable`
     */
    public function zip(iterable $iterable): ImmMap
    {
        /** @var array<int, array{0: Tk, 1: Pair<Tv, Tu>}> $elements */
        $elements = [];
        $other = new ImmVector($iterable);
        $i = 0;
        foreach ($this->elements as [$k, $v]) {
            if ($other->containsKey($i)) {
                break;
            }

            $elements[] = [$k, new Pair($v, $other->at($i))];
            ++$i;
        }

        /** @var ImmMap<Tk, Pair<Tv, Tu>> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `ImmMap` containing the first `n` key/values of the current
     * `ImmMap`.
     *
     * The returned `ImmMap` will always be a proper subset of the current
     * `ImmMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element that will be included in the `ImmMap`
     *
     * @psalm-return ImmMap<Tk, Tv> - A `ImmMap` that is a proper subset of the current
     *                 `ImmMap` up to `n` elements
     */
    public function take(int $n): ImmMap
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `ImmMap` containing the keys and values of the current
     * `ImmMap` up to but not including the first value that produces `false`
     * when passed to the specified callback.
     *
     * The returned `ImmMap` will always be a proper subset of the current
     * `ImmMap`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping condition
     *
     * @psalm-return ImmMap<Tk, Tv> - A `ImmMap` that is a proper subset of the current
     *                 `ImmMap` up until the callback returns `false`
     */
    public function takeWhile(callable $fn): ImmMap
    {
        /** @var array<int, array{0: Tk, 1: Tv}> $elements */
        $elements = [];
        foreach ($this->elements as [$key, $value]) {
            if (!$fn($value)) {
                break;
            }

            $elements[] = [$key, $value];
        }

        /** @var ImmMap<Tk, Tv> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `ImmMap` containing the values after the `n`-th element of
     * the current `ImmMap`.
     *
     * The returned `ImmMap` will always be a proper subset of the current
     * `ImmMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element to be skipped; the `$n+1` element will be the
     *               first one in the returned `ImmMap`
     *
     * @psalm-return ImmMap<Tk, Tv> - A `ImmMap` that is a proper subset of the current
     *                 `ImmMap` containing values after the specified `n`-th
     *                 element
     */
    public function drop(int $n): ImmMap
    {
        return $this->slice($n, $this->count() - $n);
    }

    /**
     * Returns a `ImmMap` containing the values of the current `ImmMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `ImmMap` will always be a proper subset of the current
     * `ImmMap`.
     *
     * @psalm-param (callable(Tk): bool) $fn - The callback used to determine the starting element for the
     *                                 current `ImmMap`
     *
     * @psalm-return ImmMap<Tk, Tv> - A `ImmMap` that is a proper subset of the current
     *                 `ImmMap` starting after the callback returns `true`
     */
    public function dropWhile(callable $fn): ImmMap
    {
        $failed = false;
        /** @var array<int, array{0: Tk, 1: Tv}> $elements */
        $elements = [];
        foreach ($this->elements as [$key, $value]) {
            if (!$failed && !$fn($value)) {
                $failed = true;
            }

            if ($failed) {
                $elements[] = [$key, $value];
            }
        }

        /** @var ImmMap<Tk, Tv> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a subset of the current `ImmMap` starting from a given key
     * location up to, but not including, the element at the provided length from
     * the starting key location.
     *
     * `$start` is 0-based. `$len` is 1-based. So `slice(0, 2)` would return the
     * keys and values at key location 0 and 1.
     *
     * The returned `ImmMap` will always be a proper subset of the current
     * `ImmMap`.
     *
     * @psalm-param int $start - The starting key location of the current `ImmMap` for
     *                   the featured `ImmMap`
     * @psalm-param int $len   - The length of the returned `ImmMap`
     *
     * @psalm-return ImmMap<Tk, Tv> - A `ImmMap` that is a proper subset of the current
     *                     `ImmMap` starting at `$start` up to but not including the
     *                     element `$start + $len`
     */
    public function slice(int $start, int $len): ImmMap
    {
        Psl\invariant($start >= 0, 'Start offset must be non-negative');
        Psl\invariant($len >= 0, 'Length must be non-negative');
        if (0 === $len) {
            return new ImmMap([]);
        }

        $i = 0;

        /** @var array<int, array{0: Tk, 1: Tv}> $elements */
        $elements = [];
        foreach ($this->elements as [$key, $value]) {
            if ($i++ < $start) {
                continue;
            }

            $elements[] = [$key, $value];
            if ($i >= $start + $len) {
                break;
            }
        }

        /** @var ImmMap<Tk, Tv> $map */
        $map = new ImmMap([]);
        $map->elements = $elements;

        return $map;
    }

    /**
     * Returns a `MutableVector` that is the concatenation of the values of the
     * current `ImmMap` and the values of the provided `iterable`.
     *
     * The provided `iterable` is concatenated to the end of the current
     * `ImmMap` to produce the returned `MutableVector`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                               `ImmMap`
     *
     * @psalm-return Vector<Tu> - The integer-indexed concatenated `MutableVector`
     */
    public function concat(iterable $iterable): Vector
    {
        return $this->values()->concat($iterable);
    }

    /**
     * Returns the first value in the current `ImmMap`.
     *
     * @psalm-return Tv|null - The first value in the current `ImmMap`,  or `null` if the
     *                 `ImmMap` is empty
     */
    public function first()
    {
        $first = Iter\first($this->elements);

        return null !== $first ? $first[1] : null;
    }

    /**
     * Returns the first key in the current `ImmMap`.
     *
     * @psalm-return Tk|null - The first key in the current `ImmMap`, or `null` if the
     *                 `ImmMap` is empty
     */
    public function firstKey()
    {
        /** @psalm-var array{0: Tk, 1: Tv}|null $first */
        $first = Iter\first($this->elements);

        /** @psalm-var null|Tk */
        return null !== $first ? $first[0] : null;
    }

    /**
     * Returns the last value in the current `ImmMap`.
     *
     * @psalm-return Tv|null - The last value in the current `ImmMap`, or `null` if the
     *                 `ImmMap` is empty
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
     * Returns the last key in the current `ImmMap`.
     *
     * @psalm-return Tk|null - The last key in the current `ImmMap`, or null if the
     *                 `ImmMap` is empty
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
     * Returns a new `ImmMap` with the keys that are in the current `ImmMap`, but not
     * in the provided `iterable`.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` on which to compare the keys
     *
     * @psalm-return ImmMap<Tk, Tv> - A `ImmMap` containing the keys (and associated values) of the
     *                 current `ImmMap` that are not in the `iterable`
     */
    public function differenceByKey(iterable $iterable): ImmMap
    {
        /** @var ImmMap<Tk, Tv> */
        return new ImmMap(Iter\diff_by_key($this->getIterator(), $iterable));
    }

    /**
     * Returns a deep, mutable copy (`Map`) of this `ImmMap`.
     *
     * @psalm-return Map<Tk, Tv> - a `Map` that is a deep copy of this `ImmMap`
     */
    public function mutable(): Map
    {
        /** @var Map<Tk, Tv> */
        return new Map(clone $this);
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
