<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl\Arr;
use Psl\Iter;

/**
 * @psalm-template Tv
 *
 * @template-implements ConstVector<Tv>
 */
final class ImmVector implements ConstVector
{
    /**
     * @psalm-var array<int, Tv>
     */
    private array $items;

    /**
     * @psalm-param iterable<Tv> $items
     */
    public function __construct(iterable $items = [])
    {
        /** @psalm-var array<int, Tv> */
        $this->items = Iter\to_array($items);
    }

    /**
     * Get access to the items in the collection.
     *
     * @psalm-return iterable<int, Tv>
     */
    public function items(): iterable
    {
        return $this->items;
    }

    /**
     * Is the collection empty?
     */
    public function isEmpty(): bool
    {
        return Iter\is_empty($this->items);
    }

    /**
     * Get the number of items in the collection.
     */
    public function count(): int
    {
        return Iter\count($this->items);
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param  int $k
     *
     * @psalm-return Tv
     */
    public function at($k)
    {
        /** @psalm-var Tv */
        return Arr\at($this->items, $k);
    }

    /**
     * Determines if the specified key is in the current collection.
     *
     * @psalm-param int $k
     */
    public function containsKey($k): bool
    {
        return Arr\contains_key($this->items, $k);
    }

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-param int $k
     *
     * @psalm-return Tv|null
     */
    public function get($k)
    {
        return $this->items[$k] ?? null;
    }

    /**
     * Returns a ImmVector containing the values of the current ImmVector that meet a supplied
     * condition.
     *
     * @psalm-param (callable(Tv): bool) $fn
     *
     * @psalm-return ImmVector<Tv>
     */
    public function filter(callable $fn): ImmVector
    {
        return new ImmVector(Iter\filter($this->items, $fn));
    }

    /**
     * Returns a ImmVector containing the values of the current ImmVector that meet a supplied
     * condition applied to its keys and values.
     *
     * @psalm-param (callable(int, Tv): bool) $fn
     *
     * @psalm-return ImmVector<Tv>
     */
    public function filterWithKey(callable $fn): ImmVector
    {
        return new ImmVector(Iter\filter_keys($this->items, $fn));
    }

    /**
     * Returns a ImmVector containing the values of the current
     * ImmVector. Essentially a copy of the current ImmVector.
     *
     * @psalm-return ImmVector<Tv>
     */
    public function values(): ImmVector
    {
        return new ImmVector($this->items);
    }

    /**
     * Returns a `ImmVector` containing the keys of the current `ImmVector`.
     *
     * @psalm-return ImmVector<int>
     */
    public function keys(): ImmVector
    {
        /** @var iterable<int> $keys */
        $keys = Iter\keys($this->items);

        return new ImmVector($keys);
    }

    /**
     * Returns a `ImmVector` containing the values after an operation has been
     * applied to each value in the current `ImmVector`.
     *
     * Every value in the current `ImmVector` is affected by a call to `map()`,
     * unlike `filter()` where only values that meet a certain criteria are
     * affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(Tv): Tu) $fn
     *
     * @psalm-return ImmVector<Tu>
     */
    public function map(callable $fn): ImmVector
    {
        return new ImmVector(Iter\map($this->items, $fn));
    }

    /**
     * Returns a `ImmVector` containing the values after an operation has been
     * applied to each key and value in the current `ImmVector`.
     *
     * Every key and value in the current `ImmVector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * @psalm-template Tu
     *
     * @psalm-param (callable(int, Tv): Tu) $fn - The callback containing the operation to apply to the
     *              `ImmVector` keys and values.
     *
     * @psalm-return ImmVector<Tu> - a `ImmVector` containing the values after a user-specified
     *           operation on the current Vector's keys and values is applied.
     */
    public function mapWithKey(callable $fn): ImmVector
    {
        return new ImmVector(Iter\map_with_key($this->items, $fn));
    }

    /**
     * Returns a `ImmVector` where each element is a `Pair` that combines the
     * element of the current `ImmVector` and the provided `iterable`.
     *
     * If the number of elements of the `ImmVector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @psalm-template Tu
     *
     * @psalm-param  iterable<Tu> $iterable - The `iterable` to use to combine with the
     *                       elements of this `ImmVector`.
     *
     * @psalm-return ImmVector<Pair<Tv, Tu>> - The `ImmVector` that combines the values of the current
     *           `ImmVector` with the provided `iterable`.
     */
    public function zip(iterable $iterable): ImmVector
    {
        if (!Arr\is_array($iterable)) {
            $iterable = Iter\to_array($iterable);
        }

        $other = new ImmVector($iterable);
        $min = ($x = $other->count()) > ($y = $this->count()) ? $y : $x;

        /** @psalm-var array<int, Pair<Tv, Tu>> $values */
        $values = [];
        for ($i = 0; $i < $min; ++$i) {
            $values[] = new Pair($this->at($i), $other->at($i));
        }

        return new ImmVector($values);
    }

    /**
     * Returns a `ImmVector` containing the first `n` values of the current
     * `ImmVector`.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `ImmVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param int $n - The last element that will be included in the returned
     *               `ImmVector`
     *
     * @psalm-return ImmVector<Tv> - A `ImmVector` that is a proper subset of the current
     *           `ImmVector` up to `n` elements.
     */
    public function take(int $n): ImmVector
    {
        return new ImmVector(Iter\take($this->items, $n));
    }

    /**
     * Returns a `ImmVector` containing the values of the current `ImmVector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `ImmVector`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback that is used to determine the stopping
     *              condition.
     *
     * @psalm-return ImmVector<Tv> - A `ImmVector` that is a proper subset of the current
     *           `ImmVector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): ImmVector
    {
        return new ImmVector(Iter\take_while($this->items, $fn));
    }

    /**
     * Returns a `ImmVector` containing the values after the `n`-th element of
     * the current `ImmVector`.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `ImmVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @psalm-param  int $n - The last element to be skipped; the $n+1 element will be the
     *             first one in the returned `ImmVector`.
     *
     * @psalm-return ImmVector<Tv> - A `ImmVector` that is a proper subset of the current
     *           `ImmVector` containing values after the specified `n`-th
     *           element.
     */
    public function drop(int $n): ImmVector
    {
        return new ImmVector(Iter\drop($this->items, $n));
    }

    /**
     * Returns a `ImmVector` containing the values of the current `ImmVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `ImmVector` will always be a proper subset of the current
     * `ImmVector`.
     *
     * @psalm-param (callable(Tv): bool) $fn - The callback used to determine the starting element for the
     *              returned `ImmVector`.
     *
     * @psalm-return ImmVector<Tv> - A `ImmVector` that is a proper subset of the current
     *           `ImmVector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): ImmVector
    {
        return new ImmVector(Iter\drop_while($this->items, $fn));
    }

    /**
     * Returns a subset of the current `ImmVector` starting from a given key up
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
     * @psalm-return ImmVector<Tv> - A `ImmVector` that is a proper subset of the current
     *           `ImmVector` starting at `$start` up to but not including the
     *           element `$start + $len`.
     */
    public function slice(int $start, int $len): ImmVector
    {
        return new ImmVector(Iter\slice($this->items, $start, $len));
    }

    /**
     * Returns a `ImmVector` that is the concatenation of the values of the
     * current `ImmVector` and the values of the provided `iterable`.
     *
     * The values of the provided `iterable` is concatenated to the end of the
     * current `ImmVector` to produce the returned `ImmVector`.
     *
     * @psalm-template Tu of Tv
     *
     * @psalm-param iterable<Tu> $iterable - The `iterable` to concatenate to the current
     *                       `ImmVector`.
     *
     * @psalm-return ImmVector<Tu> - The concatenated `ImmVector`.
     */
    public function concat(iterable $iterable): ImmVector
    {
        /** @psalm-var array<int, Tu> $values */
        $values = Arr\concat($this->items, $iterable);

        return new ImmVector($values);
    }

    /**
     * Returns the first value in the current `ImmVector`.
     *
     * @psalm-return null|Tv - The first value in the current `ImmVector`, or `null` if the
     *           current `ImmVector` is empty.
     */
    public function first()
    {
        return $this->get(0);
    }

    /**
     * Returns the first key in the current `ImmVector`.
     *
     * @psalm-return int|null - The first key in the current `ImmVector`, or `null` if the
     *                  current `ImmVector` is empty
     */
    public function firstKey(): ?int
    {
        return $this->containsKey(0) ? 0 : null;
    }

    /**
     * Returns the last value in the current `ImmVector`.
     *
     * @psalm-return null|Tv - The last value in the current `ImmVector`, or `null` if the
     *           current `ImmVector` is empty.
     */
    public function last()
    {
        return $this->get($this->count() - 1);
    }

    /**
     * Returns the last key in the current `ImmVector`.
     *
     * @psalm-return int|null - The last key in the current `ImmVector`, or `null` if the
     *                  current `ImmVector` is empty
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
     * @psalm-param Tv $search_value - The value that will be search for in the current
     *                        `ImmVector`.
     *
     * @psalm-return int|null - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value): ?int
    {
        /**
         * @psalm-var int $k
         * @psalm-var Tv $v
         */
        foreach ($this->items as $k => $v) {
            if ($v === $search_value) {
                return $k;
            }
        }

        return null;
    }

    /**
     * Retrieve an external iterator.
     *
     * @psalm-return Iter\Iterator<int, Tv>
     */
    public function getIterator(): Iter\Iterator
    {
        return new Iter\Iterator($this->items);
    }

    /**
     * Get an array copy of the the collection.
     *
     * @psalm-return array<int, Tv>
     */
    public function toArray(): array
    {
        /** @psalm-var array<int, Tv> $items */
        return $this->items;
    }
}
