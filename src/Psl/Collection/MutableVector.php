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
 * @implements MutableVectorInterface<T>
 */
final class MutableVector implements MutableVectorInterface
{
    /**
     * @var array<int, T> $elements
     */
    private array $elements = [];

    /**
     * MutableVector constructor.
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
     * @return MutableVector<Ts>
     *
     * @pure
     */
    public static function fromArray(array $elements): MutableVector
    {
        /** @psalm-suppress ImpureMethodCall - conditionally pure */
        return new self($elements);
    }

    /**
     * Returns the first value in the current `MutableVector`.
     *
     * @return T|null The first value in the current `MutableVector`, or `null` if the
     *                current `MutableVector` is empty.
     *
     * @psalm-mutation-free
     */
    public function first(): mixed
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\first($this->elements);
    }

    /**
     * Returns the last value in the current `MutableVector`.
     *
     * @return T|null The last value in the current `MutableVector`, or `null` if the
     *                current `MutableVector` is empty.
     *
     * @psalm-mutation-free
     */
    public function last(): mixed
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
        /** @psalm-suppress ImpureMethodCall - conditionally pure */
        return Iter\Iterator::create($this->elements);
    }

    /**
     * Is the vector empty?
     *
     * @psalm-mutation-free
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the current `MutableVector`.
     *
     * @psalm-mutation-free
     */
    public function count(): int
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the current `MutableVector`.
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
     * Get an array copy of the current `MutableVector`.
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
     * Returns the value at the specified key in the current `MutableVector`.
     *
     * @param int $k
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function at(string|int $k): mixed
    {
        Psl\invariant($this->contains($k), 'Key (%s) is out-of-bounds.', $k);

        return $this->elements[$k];
    }

    /**
     * Determines if the specified key is in the current `MutableVector`.
     *
     * @param int $k
     *
     * @psalm-mutation-free
     */
    public function contains(int|string $k): bool
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\contains_key($this->elements, $k);
    }

    /**
     * Returns the value at the specified key in the current `MutableVector`.
     *
     * @param int $k
     *
     * @return T|null
     *
     * @psalm-mutation-free
     */
    public function get(string|int $k): mixed
    {
        return $this->elements[$k] ?? null;
    }

    /**
     * Returns the first key in the current `MutableVector`.
     *
     * @return int|null The first key in the current `MutableVector`, or `null` if the
     *                  current `MutableVector` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\first_key($this->elements);
    }

    /**
     * Returns the last key in the current `MutableVector`.
     *
     * @return int|null The last key in the current `MutableVector`, or `null` if the
     *                  current `MutableVector` is empty.
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
    public function linearSearch(mixed $search_value): ?int
    {
        foreach ($this->elements as $key => $element) {
            if ($search_value === $element) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Stores a value into the current vector with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * It returns the current vector, meaning changes made to the current
     * vector will be reflected in the returned vector.
     *
     * @param int $k The key to which we will set the value
     * @param T $v The value to set
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     *
     * @return MutableVector<T> returns itself
     */
    public function set(int|string $k, mixed $v): MutableVector
    {
        Psl\invariant($this->contains($k), 'Key (%s) is out-of-bounds.', $k);

        $this->elements[$k] = $v;

        return $this;
    }

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current vector associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current vector, meaning changes made to the current vector
     * will be reflected in the returned vector.
     *
     * @param iterable<int, T> $iterable The `iterable` with the new values to set
     *
     * @return MutableVector<T> returns itself
     */
    public function setAll(iterable $iterable): MutableVector
    {
        foreach ($iterable as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    /**
     * Removes the specified key (and associated value) from the current
     * vector.
     *
     * If the key is not in the current vector, the current vector is
     * unchanged.
     *
     * This will cause elements with higher keys to be assigned a new key that is one less
     * than their previous key.
     *
     * That is, values with keys $k + 1 to n - 1 will be given new keys $k to n - 2, where n is
     * the length of the current MutableVector before the call to remove().
     *
     * @param int $k The key to remove.
     *
     * @return MutableVector<T> returns itself.
     */
    public function remove(int|string $k): MutableVector
    {
        if ($this->contains($k)) {
            unset($this->elements[$k]);
            $this->elements = Vec\values($this->elements);
        }

        return $this;
    }

    /**
     * Removes all items from the vector.
     *
     * @return MutableVector<T> Returns itself
     */
    public function clear(): MutableVector
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Add a value to the vector and return the vector itself.
     *
     * @param T $v The value to add.
     *
     * @return MutableVector<T> Returns itself.
     */
    public function add(mixed $v): MutableVector
    {
        $this->elements[] = $v;

        return $this;
    }

    /**
     * For every element in the provided iterable, add the value into the current vector.
     *
     * @param iterable<T> $iterable The `iterable` with the new values to add
     *
     * @return MutableVector<T> returns itself.
     */
    public function addAll(iterable $iterable): MutableVector
    {
        foreach ($iterable as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * Returns a `MutableVector` containing the values of the current
     * `MutableVector`.
     *
     * @return MutableVector<T>
     *
     * @psalm-mutation-free
     */
    public function values(): MutableVector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return MutableVector::fromArray(Vec\values($this->elements));
    }

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableVector`.
     *
     * @return MutableVector<int>
     *
     * @psalm-mutation-free
     */
    public function keys(): MutableVector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return MutableVector::fromArray(Vec\keys($this->elements));
    }

    /**
     * Returns a `MutableVector` containing the values of the current `MutableVector`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableVector` remain unchanged in the
     * returned `MutableVector`.
     *
     * @param (callable(T): bool) $fn The callback containing the condition to apply to the current
     *                                `MutableVector` values.
     *
     * @return MutableVector<T> A `MutableVector` containing the values after a user-specified condition
     *                          is applied.
     */
    public function filter(callable $fn): MutableVector
    {
        return new MutableVector(Dict\filter($this->elements, $fn));
    }

    /**
     * Returns a `MutableVector` containing the values of the current `MutableVector`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableVector` remain unchanged in the
     * returned `MutableVector`; the keys will be used in the filtering process only.
     *
     * @param (callable(int, T): bool) $fn The callback containing the condition to apply to the current
     *                                     `MutableVector` keys and values.
     *
     * @return MutableVector<T> A `MutableVector` containing the values after a user-specified
     *                          condition is applied to the keys and values of the current `MutableVector`.
     */
    public function filterWithKey(callable $fn): MutableVector
    {
        return new MutableVector(Dict\filter_with_key($this->elements, $fn));
    }

    /**
     * Returns a `MutableVector` after an operation has been applied to each value
     * in the current `MutableVector`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableVector` to the
     * returned `MutableVector`.
     *
     * @template Tu
     *
     * @param (callable(T): Tu) $fn The callback containing the operation to apply to the current
     *                              `MutableVector` values.
     *
     * @return MutableVector<Tu> A `MutableVector` containing key/value pairs after a user-specified
     *                           operation is applied.
     */
    public function map(callable $fn): MutableVector
    {
        return new MutableVector(Dict\map($this->elements, $fn));
    }

    /**
     * Returns a `MutableVector` after an operation has been applied to each key and
     * value in the current `MutableVector`.
     *
     * Every key and value in the current `MutableVector` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableVector` to the returned
     * `MutableVector`. The keys are only used to help in the mapping operation.
     *
     * @template Tu
     *
     * @param (callable(int, T): Tu) $fn The callback containing the operation to apply to the current
     *                                   `MutableVector` keys and values
     *
     * @return MutableVector<Tu> A `MutableVector` containing the values after a user-specified
     *                           operation on the current `MutableVector`'s keys and values is applied.
     */
    public function mapWithKey(callable $fn): MutableVector
    {
        return new MutableVector(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `MutableVector` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `VectorInterface` and the provided `iterable`.
     *
     * If the number of elements of the `MutableVector` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param iterable<Tu> $iterable The `iterable` to use to combine with the
     *                               elements of this `MutableVector`.
     *
     * @return MutableVector<array{0: T, 1: Tu}> The `MutableVector` that combines the values of the current
     *                                `MutableVector` with the provided `iterable`.
     *
     * @psalm-mutation-free
     */
    public function zip(iterable $iterable): MutableVector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return MutableVector::fromArray(Vec\zip($this, $iterable));
    }

    /**
     * Returns a `MutableVector` containing the first `n` values of the current
     * `MutableVector`.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int $n The last element that will be included in the returned
     *               `MutableVector`.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    public function take(int $n): MutableVector
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `MutableVector` containing the values of the current `MutableVector`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * @param (callable(T): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): MutableVector
    {
        return new MutableVector(Dict\take_while($this->elements, $fn));
    }

    /**
     * Returns a `MutableVector` containing the values after the `n`-th element of
     * the current `MutableVector`.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `VectorInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int $n The last element to be skipped; the $n+1 element will be the
     *               first one in the returned `MutableVector`.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    public function drop(int $n): MutableVector
    {
        return $this->slice($n);
    }

    /**
     * Returns a `MutableVector` containing the values of the current `MutableVector`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableVector` will always be a proper subset of the current
     * `MutableVector`.
     *
     * @param (callable(T): bool) $fn The callback used to determine the starting element for the
     *                                returned `MutableVector`.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): MutableVector
    {
        return new MutableVector(Dict\drop_while($this->elements, $fn));
    }

    /**
     * Returns a subset of the current `MutableVector` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableVector` will always be a proper subset of this
     * `MutableVector`.
     *
     * @param int $start The starting key of this Vector to begin the returned
     *                   `MutableVector`.
     * @param null|int $length The length of the returned `MutableVector`
     *
     * @throws Psl\Exception\InvariantViolationException If $start or $len are negative.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` starting at `$start` up to but not including
     *                          the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): MutableVector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return MutableVector::fromArray(Dict\slice($this->elements, $start, $length));
    }

    /**
     * Returns a `MutableVector` containing the original `MutableVector` split into
     * chunks of the given size.
     *
     * If the original `MutableVector` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param int $size The size of each chunk.
     *
     * @return MutableVector<MutableVector<T>> A `MutableVector` containing the original
     *                                         `MutableVector` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): MutableVector
    {
        /**
         * @psalm-suppress MissingThrowsDocblock
         * @psalm-suppress ImpureFunctionCall
         */
        return MutableVector::fromArray(Vec\map(
            /**
             * @psalm-suppress MissingThrowsDocblock
             * @psalm-suppress ImpureFunctionCall
             */
            Vec\chunk($this->toArray(), $size),
            /**
             * @param list<T> $chunk
             *
             * @return MutableVector<T>
             */
            static fn(array $chunk) => MutableVector::fromArray($chunk)
        ));
    }
}
