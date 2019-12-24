<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl\Iter;

/**
 * Pair is an immutable, fixed-size collection with exactly two elements (possibly of different types).
 *
 * Like all objects in PHP, Pairs have reference-like semantics.
 * The elements of a Pair cannot be mutated (i.e. you can't assign to the elements of a Pair)
 * though Pairs may contain mutable objects.
 *
 * Pairs only support integer keys. If a non-integer key is used, an exception will be thrown.
 *
 * Pairs support $m[$k] style syntax for getting values by key. Pairs also support isset($m[$k]) and empty($m[$k]) syntax, and they provide similar semantics as arrays.
 *
 * Pairs do not support taking elements by reference. If binding assignment (=&) is used with an element of a Pair, if an element of a Pair is passed by reference, or if a Pair is used with foreach by reference, an exception will be thrown.
 *
 * Pair keys are always 0 and 1, respectively.
 *
 * You may notice that many methods affecting the instance of Pair return an ImmVector --
 * Pairs are essentially backed by 2-element ImmVectors.
 *
 * @psalm-template Ta
 * @psalm-template Tb
 *
 * @template-implements ConstVector<Ta|Tb>
 */
final class Pair implements ConstVector
{
    /**
     * @psalm-var ImmVector<Ta|Tb>
     */
    private ImmVector $vector;

    /**
     * @psalm-param Ta $first
     * @psalm-param Tb $second
     */
    public function __construct($first, $second)
    {
        $this->vector = new ImmVector([$first, $second]);
    }

    /**
     * Get access to the items in the collection.
     *
     * @psalm-return array{0: Ta, 1: Tb}
     */
    public function items(): array
    {
        return [$this->first(), $this->last()];
    }

    /**
     * Is the collection empty?
     */
    public function isEmpty(): bool
    {
        return false;
    }

    /**
     * Get the number of items in the collection.
     */
    public function count(): int
    {
        return 2;
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param int $k
     *
     * @psalm-return Ta|Tb
     */
    public function at($k)
    {
        return $this->vector->at($k);
    }

    /**
     * Determines if the specified key is in the current collection.
     *
     * @psalm-param int $k
     */
    public function containsKey($k): bool
    {
        return 1 === $k || 2 === $k;
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param int $k
     *
     * @psalm-return Ta|Tb|null
     */
    public function get($k)
    {
        return $this->vector->get($k);
    }

    /**
     * Returns a ImmVector containing the values of the current ImmVector that meet a supplied
     * condition.
     *
     * @psalm-param (callable(Ta|Tb): bool) $fn
     *
     * @psalm-return ImmVector<Ta|Tb>
     */
    public function filter(callable $fn): ImmVector
    {
        return $this->vector->filter($fn);
    }

    /**
     * Returns a ImmVector containing the values of the current ImmVector that meet a supplied
     * condition applied to its keys and values.
     *
     * @psalm-param (callable(int, Ta|Tb): bool) $fn
     *
     * @psalm-return ImmVector<Ta|Tb>
     */
    public function filterWithKey(callable $fn): ImmVector
    {
        return $this->vector->filterWithKey($fn);
    }

    /**
     * Returns a ImmVector containing the values of the current
     * ImmVector. Essentially a copy of the current ImmVector.
     *
     * @psalm-return ImmVector<Ta|Tb>
     */
    public function values(): ImmVector
    {
        return $this->vector->values();
    }

    /**
     * Returns a `ImmVector` containing the keys of the current `Pair`.
     *
     * @psalm-return ImmVector<int>
     */
    public function keys(): ImmVector
    {
        return new ImmVector([0, 1]);
    }

    /**
     * Returns a `ImmVector` containing the values after an operation has been
     * applied to each value in the current `Pair`.
     *
     * Every value in the current `Pair` is affected by a call to `map()`,
     * unlike `filter()` where only values that meet a certain criteria are
     * affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Ta|Tb): Tu) $fn
     *
     * @psalm-return ImmVector<Tu>
     */
    public function map(callable $fn): ImmVector
    {
        return $this->vector->map($fn);
    }

    /**
     * Returns a `ImmVector` containing the values after an operation has been
     * applied to each key and value in the current `Pair`.
     *
     * Every key and value in the current `Pair` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, Ta|Tb): Tu) $fn - The callback containing the operation to apply to the
     *              `Pair` keys and values.
     *
     * @psalm-return ImmVector<Tu> - a `ImmVector` containing the values after a user-specified
     *           operation on the current Pair's keys and values is applied.
     */
    public function mapWithKey(callable $fn): ImmVector
    {
        return $this->vector->mapWithKey($fn);
    }

    /**
     * Returns a `ImmVector` where each element is a `Pair` that combines the
     * element of the current `Pair` and the provided `iterable`.
     *
     * If the number of elements of the `Pair` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param  iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `Pair`.
     *
     * @psalm-return ImmVector<Pair<Ta|Tb, Tu>> - The `ImmVector` that combines the values of the current
     *           `Pair` with the provided `iterable`.
     */
    public function zip(iterable $iterable): ImmVector
    {
        return $this->vector->zip($iterable);
    }

    /**
     * Returns a `ImmVector` containing the first `n` values of the current
     * `Pair`.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `Pair`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element that will be included in the returned
     *               `ImmVector`
     *
     * @psalm-return ImmVector<Ta|Tb> - A `ImmVector` that is a proper subset of the current
     *           `Pair` up to `n` elements.
     */
    public function take(int $n): ImmVector
    {
        return $this->vector->take($n);
    }

    /**
     * Returns a `ImmVector` containing the values of the current `Pair`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `Pair`.
     *
     * @psalm-param (callable(Ta|Tb): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return ImmVector<Ta|Tb> - A `ImmVector` that is a proper subset of the current
     *           `Pair` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): ImmVector
    {
        return $this->vector->takeWhile($fn);
    }

    /**
     * Returns a `ImmVector` containing the values after the `n`-th element of
     * the current `Pair`.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `ConstVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `ImmVector`.
     *
     * @psalm-return ImmVector<Ta|Tb> - A `ImmVector` that is a proper subset of the current
     *           `Pair` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): ImmVector
    {
        return $this->vector->drop($n);
    }

    /**
     * Returns a `ImmVector` containing the values of the current `ConstVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `ImmVector`.
     *
     * @psalm-param (callable(Ta|Tb): bool) $fn - The callback used to determine the starting element for the
     *              returned `ImmVector`.
     *
     * @psalm-return ImmVector<Ta|Tb> - A `ImmVector` that is a proper subset of the current
     *           `Pair` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): ImmVector
    {
        return $this->vector->dropWhile($fn);
    }

    /**
     * Returns a subset of the current `Pair` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `ImmVector` will always be a proper subset of this
     * `ImmVector`.
     *
     * @psalm-param int $start - The starting key of this Vector to begin the returned
     *                   `ImmVector`
     * @psalm-param int $len   - The length of the returned `ImmVector`
     *
     * @psalm-return ImmVector<Ta|Tb> - A `ImmVector` that is a proper subset of the current
     *           `ImmVector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): ImmVector
    {
        return $this->vector->slice($start, $len);
    }

    /**
     * Returns a `ImmVector` that is the concatenation of the values of the
     * current `Pair` and the values of the provided `iterable`.
     *
     * The values of the provided `iterable` is concatenated to the end of the
     * current `Pair` to produce the returned `ImmVector`.
     *
     * @psalm-template Tu of Ta|Tb
     *
     * @psalm-param     iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                       `Pair`.
     *
     * @psalm-return ImmVector<Tu> - The concatenated `ImmVector`.
     */
    public function concat(iterable $iterable): ImmVector
    {
        /** @psalm-var ImmVector<Tu> */
        return $this->vector->concat($iterable);
    }

    /**
     * Returns the first value in the current `Pair`.
     *
     * @psalm-return Ta - The first value in the current `Pair`
     */
    public function first()
    {
        /** @psalm-var Ta */
        return $this->vector->first();
    }

    /**
     * Returns the first key in the current `Pair`.
     *
     * @psalm-return int - The first key in the current `Pair`
     */
    public function firstKey(): int
    {
        return 0;
    }

    /**
     * Returns the last value in the current `Pair`.
     *
     * @psalm-return Tb - The last value in the current `Pair`
     */
    public function last()
    {
        /** @psalm-var Tb */
        return $this->vector->last();
    }

    /**
     * Returns the last key in the current `Pair`.
     *
     * @psalm-return int - The last key in the current `Pair`
     */
    public function lastKey(): int
    {
        return 1;
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param Ta|Tb $search_value - The value that will be search for in the current
     *                        `Pair`.
     *
     * @psalm-return int|null - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int
    {
        return $this->vector->linearSearch($search_value);
    }

    /**
     * Retrieve an external iterator.
     *
     * @psalm-return Iter\Iterator<int, Ta|Tb>
     */
    public function getIterator(): Iter\Iterator
    {
        return $this->vector->getIterator();
    }

    /**
     * Get an array copy of the the collection.
     *
     * @psalm-return array{0: Ta, 1: Tb}
     */
    public function toArray(): array
    {
        return [$this->first(), $this->last()];
    }
}
