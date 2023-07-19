<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

use function array_key_exists;
use function array_key_last;
use function array_keys;
use function array_values;
use function count;

/**
 * @template T
 *
 * @implements MutableVectorInterface<T>
 */
final class MutableVector implements MutableVectorInterface
{
    /**
     * @var list<T> $elements
     */
    private array $elements = [];

    /**
     * MutableVector constructor.
     *
     * @param array<array-key, T> $elements
     */
    public function __construct(array $elements)
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
        return $this->elements[0] ?? null;
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
        $key = $this->lastKey();
        if (null === $key) {
            return null;
        }

        return $this->elements[$key];
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iter\Iterator<int<0, max>, T>
     */
    public function getIterator(): Iter\Iterator
    {
        return Iter\Iterator::create($this->elements);
    }

    /**
     * Is the vector empty?
     *
     * @psalm-mutation-free
     */
    public function isEmpty(): bool
    {
        return [] === $this->elements;
    }

    /**
     * Get the number of elements in the current `MutableVector`.
     *
     * @psalm-mutation-free
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        /** @var int<0, max> */
        return count($this->elements);
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
        return $this->elements;
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
        return $this->elements;
    }

    /**
     * Returns the value at the specified key in the current `MutableVector`.
     *
     * @param int<0, max> $k
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function at(string|int $k): mixed
    {
        if (!array_key_exists($k, $this->elements)) {
            throw Exception\OutOfBoundsException::for($k);
        }

        return $this->elements[$k];
    }

    /**
     * Determines if the specified key is in the current `MutableVector`.
     *
     * @param int<0, max> $k
     *
     * @psalm-mutation-free
     */
    public function contains(int|string $k): bool
    {
        return array_key_exists($k, $this->elements);
    }

    /**
     * Returns the value at the specified key in the current `MutableVector`.
     *
     * @param int<0, max> $k
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
     * @return int<0, max>|null The first key in the current `MutableVector`, or `null` if the
     *                          current `MutableVector` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int
    {
        return [] === $this->elements ? null : 0;
    }

    /**
     * Returns the last key in the current `MutableVector`.
     *
     * @return int<0, max>|null The last key in the current `MutableVector`, or `null` if the
     *                          current `MutableVector` is empty.
     *
     * @psalm-mutation-free
     */
    public function lastKey(): ?int
    {
        return array_key_last($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @param T $search_value The value that will be search for in the current
     *                        collection.
     *
     * @return int<0, max>|null The key (index) where that value is found; null if it is not found.
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
     * @param int<0, max> $k The key to which we will set the value
     * @param T $v The value to set
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @return MutableVector<T> returns itself
     */
    public function set(int|string $k, mixed $v): MutableVector
    {
        if (!array_key_exists($k, $this->elements)) {
            throw Exception\OutOfBoundsException::for($k);
        }

        $this->elements[$k] = $v;

        return $this;
    }

    /**
     * For every element in the provided elements array, stores a value into the
     * current vector associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current vector, meaning changes made to the current vector
     * will be reflected in the returned vector.
     *
     * @param array<int<0, max>, T> $elements The elements with the new values to set
     *
     * @return MutableVector<T> returns itself
     */
    public function setAll(array $elements): MutableVector
    {
        foreach ($elements as $k => $v) {
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
     * @param int<0, max> $k The key to remove.
     *
     * @return MutableVector<T> returns itself.
     */
    public function remove(int|string $k): MutableVector
    {
        if ($this->contains($k)) {
            $elements = $this->elements;
            unset($elements[$k]);
            $this->elements = array_values($elements);
        }

        return $this;
    }

    /**
     * Removes all elements from the vector.
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
     * For every element in the provided elements array, add the value into the current vector.
     *
     * @param array<array-key, T> $elements The elements with the new values to add
     *
     * @return MutableVector<T> returns itself.
     */
    public function addAll(array $elements): MutableVector
    {
        foreach ($elements as $item) {
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
        return MutableVector::fromArray($this->elements);
    }

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableVector`.
     *
     * @return MutableVector<int<0, max>>
     *
     * @psalm-mutation-free
     */
    public function keys(): MutableVector
    {
        return MutableVector::fromArray(array_keys($this->elements));
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
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `MutableVector` values.
     *
     * @return MutableVector<T> A `MutableVector` containing the values after a user-specified condition
     *                          is applied.
     */
    public function filter(Closure $fn): MutableVector
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
     * @param (Closure(int<0, max>, T): bool) $fn The callback containing the condition to apply to the current
     *                                            `MutableVector` keys and values.
     *
     * @return MutableVector<T> A `MutableVector` containing the values after a user-specified
     *                          condition is applied to the keys and values of the current `MutableVector`.
     */
    public function filterWithKey(Closure $fn): MutableVector
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
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `MutableVector` values.
     *
     * @return MutableVector<Tu> A `MutableVector` containing key/value pairs after a user-specified
     *                           operation is applied.
     */
    public function map(Closure $fn): MutableVector
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
     * @param (Closure(int<0, max>, T): Tu) $fn The callback containing the operation to apply to the current
     *                                          `MutableVector` keys and values
     *
     * @return MutableVector<Tu> A `MutableVector` containing the values after a user-specified
     *                           operation on the current `MutableVector`'s keys and values is applied.
     */
    public function mapWithKey(Closure $fn): MutableVector
    {
        return new MutableVector(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `MutableVector` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `VectorInterface` and the provided elements array.
     *
     * If the number of elements of the `MutableVector` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableVector`.
     *
     * @return MutableVector<array{0: T, 1: Tu}> The `MutableVector` that combines the values of the current
     *                                           `MutableVector` with the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): MutableVector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return MutableVector::fromArray(Vec\zip($this->elements, $elements));
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
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `MutableVector`.
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
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` up until the callback returns `false`.
     */
    public function takeWhile(Closure $fn): MutableVector
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
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableVector`.
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
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `MutableVector`.
     *
     * @return MutableVector<T> A `MutableVector` that is a proper subset of the current
     *                          `MutableVector` starting after the callback returns `true`.
     */
    public function dropWhile(Closure $fn): MutableVector
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
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `MutableVector`.
     * @param null|int<0, max> $length The length of the returned `MutableVector`
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
     * @param positive-int $size The size of each chunk.
     *
     * @return MutableVector<MutableVector<T>> A `MutableVector` containing the original
     *                                         `MutableVector` split into chunks of the given size.
     *
     * @psalm-mutation-free
     *
     * @psalm-suppress LessSpecificImplementedReturnType - I don't see how this one is less specific than its inherited.
     */
    public function chunk(int $size): MutableVector
    {
        /**
         * @psalm-suppress MissingThrowsDocblock
         * @psalm-suppress ImpureFunctionCall
         */
        return static::fromArray(Vec\map(
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
