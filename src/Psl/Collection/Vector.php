<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

/**
 * @template T
 *
 * @implements VectorInterface<T>
 */
final class Vector implements VectorInterface
{
    /**
     * @var array<int, T> $elements
     *
     * @psalm-readonly
     */
    private array $elements = [];

    /**
     * Vector constructor.
     *
     * @param iterable<T> $elements
     */
    public function __construct(iterable $elements)
    {
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }
    }

    /**
     * Create a vector from the given $elements array.
     *
     * @template Ts
     *
     * @param array<array-key, Ts> $elements
     *
     * @return Vector<Ts>
     *
     * @pure
     */
    public static function fromArray(array $elements): Vector
    {
        /** @psalm-suppress ImpureMethodCall - safe */
        return new self($elements);
    }

    /**
     * Returns the first value in the current `Vector`.
     *
     * @return T|null The first value in the current `Vector`, or `null` if the
     *                current `Vector` is empty.
     *
     * @psalm-mutation-free
     */
    public function first()
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\first($this->elements);
    }

    /**
     * Returns the last value in the current `Vector`.
     *
     * @return T|null The last value in the current `Vector`, or `null` if the
     *                current `Vector` is empty.
     *
     * @psalm-mutation-free
     */
    public function last()
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\last($this->elements);
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iter\Iterator<int, T>
     */
    public function getIterator(): Iter\Iterator
    {
        return Iter\Iterator::create($this->elements);
    }

    /**
     * Is the `Vector` empty?
     *
     * @psalm-mutation-free
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the current `Vector`.
     *
     * @psalm-mutation-free
     */
    public function count(): int
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the current `Vector`.
     *
     * @return list<T>
     *
     * @psalm-mutation-free
     */
    public function toArray(): array
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Vec\values($this->elements);
    }


    /**
     * Get an array copy of the current `Vector`.
     *
     * @return list<T>
     *
     * @psalm-mutation-free
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Returns the value at the specified key in the current `Vector`.
     *
     * @param int $k
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function at($k)
    {
        Psl\invariant($this->contains($k), 'Key (%s) is out-of-bounds.', $k);

        return $this->elements[$k];
    }

    /**
     * Determines if the specified key is in the current `Vector`.
     *
     * @param int $k
     *
     * @psalm-mutation-free
     */
    public function contains($k): bool
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\contains_key($this->elements, $k);
    }

    /**
     * Returns the value at the specified key in the current `Vector`.
     *
     * @param int $k
     *
     * @return T|null
     *
     * @psalm-mutation-free
     */
    public function get($k)
    {
        return $this->elements[$k] ?? null;
    }

    /**
     * Returns the first key in the current `Vector`.
     *
     * @return int|null The first key in the current `Vector`, or `null` if the
     *                  current `Vector` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\first_key($this->elements);
    }

    /**
     * Returns the last key in the current `Vector`.
     *
     * @return int|null The last key in the current `Vector`, or `null` if the
     *                  current `Vector` is empty.
     *
     * @psalm-mutation-free
     */
    public function lastKey(): ?int
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\last_key($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @param T $search_value The value that will be search for in the current
     *                        collection.
     *
     * @return int|null The key (index) where that value is found; null if it is not found.
     *
     * @psalm-mutation-free
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
     * @return Vector<T>
     *
     * @psalm-mutation-free
     */
    public function values(): Vector
    {
        return Vector::fromArray($this->elements);
    }

    /**
     * Returns a `Vector` containing the keys of the current `Vector`.
     *
     * @return Vector<int>
     *
     * @psalm-mutation-free
     */
    public function keys(): Vector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Vector::fromArray(Vec\keys($this->elements));
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
     * @param (callable(T): bool) $fn The callback containing the condition to apply to the current
     *                                `Vector` values.
     *
     * @return Vector<T> a Vector containing the values after a user-specified condition
     *                   is applied.
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
     * @param (callable(int, T): bool) $fn The callback containing the condition to apply to the current
     *                                     `Vector` keys and values.
     *
     * @return Vector<T> a `Vector` containing the values after a user-specified
     *                   condition is applied to the keys and values of the current `Vector`.
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
     * @template Tu
     *
     * @param (callable(T): Tu) $fn The callback containing the operation to apply to the current
     *                              `Vector` values.
     *
     * @return Vector<Tu> a `Vector` containing key/value pairs after a user-specified
     *                    operation is applied.
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
     * @template Tu
     *
     * @param (callable(int, T): Tu) $fn The callback containing the operation to apply to the current
     *                                   `Vector` keys and values.
     *
     * @return Vector<Tu> a `Vector` containing the values after a user-specified
     *                    operation on the current `Vector`'s keys and values is applied.
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
     * @template Tu
     *
     * @param iterable<Tu> $iterable The `iterable` to use to combine with the
     *                               elements of this `VectorInterface`.
     *
     * @return Vector<array{0: T, 1: Tu}> The `Vector` that combines the values of the current
     *                         `Vector` with the provided `iterable`.
     *
     * @psalm-mutation-free
     */
    public function zip(iterable $iterable): Vector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Vector::fromArray(Vec\zip($this, $iterable));
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
     * @param $n The last element that will be included in the returned
     *  `Vector`.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    public function take(int $n): Vector
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `Vector` will always be a proper subset of the current
     * `Vector`.
     *
     * @param (callable(T): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` up until the callback returns `false`.
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
     * @param int $n The last element to be skipped; the $n+1 element will be the
     *               first one in the returned `Vector`.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    public function drop(int $n): Vector
    {
        return $this->slice($n);
    }

    /**
     * Returns a `Vector` containing the values of the current `Vector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `Vector` will always be a proper subset of the current
     * `Vector`.
     *
     * @param (callable(T): bool) $fn The callback used to determine the starting element for the
     *                                returned `Vector`.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` starting after the callback returns `true`.
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
     * @param int $start The starting key of this Vector to begin the returned
     *                   `Vector`.
     * @param null|int $length The length of the returned `Vector`
     *
     * @throws Psl\Exception\InvariantViolationException If $start or $len are negative.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` starting at `$start` up to but not including the
     *                   element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): Vector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return self::fromArray(Dict\slice($this->elements, $start, $length));
    }
}
