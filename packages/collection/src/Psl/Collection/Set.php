<?php

declare(strict_types=1);

namespace Psl\Collection;

use ArrayIterator;
use Closure;
use Iterator;
use Override;

use function array_chunk;
use function array_filter;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function iterator_to_array;

use const ARRAY_FILTER_USE_KEY;

/**
 * @api
 */
final readonly class Set<T: string|int> implements SetInterface<T>
{
    /**
     * @var array<T, T> $elements
     */
    private array $elements;

    /**
     * Creates a new `Set` containing the values of the given array.
     *
     * @param array<array-key, T> $elements
     *
     * @psalm-mutation-free
     */
    public function __construct(array $elements)
    {
        $set = [];
        foreach ($elements as $element) {
            $set[$element] = $element;
        }

        $this->elements = $set;
    }

    /**
     * Creates and returns a default instance of {@see Set}.
     *
     * @return static A default instance of {@see Set}.
     *
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self::<T>([]);
    }

    /**
     * Create a set from the given array, using the values of the array as the set values.
     *
     * @param array<array-key, T> $elements
     *
     * @pure
     */
    public static function fromArray(array $elements): Set<T>
    {
        return new self::<T>($elements);
    }

    /**
     * Create a set from the given items, using the keys of the array as the set values.
     *
     * @param iterable<array-key, T> $items
     */
    public static function fromItems(iterable $items): Set<T>
    {
        $array = iterator_to_array($items);

        return self::<T>::fromArray($array);
    }

    /**
     * Create a set from the given $elements array, using the keys of the array as the set values.
     *
     * @param array<T, mixed> $elements
     *
     * @pure
     */
    public static function fromArrayKeys(array $elements): Set<T>
    {
        /** @var array<T, T> $set */
        $set = [];
        foreach ($elements as $key => $_) {
            $set[$key] = $key;
        }

        return new self::<T>($set);
    }

    /**
     * Returns the first value in the current `Set`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): T|null
    {
        return array_key_first($this->elements);
    }

    /**
     * Returns the last value in the current `Set`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function last(): T|null
    {
        return array_key_last($this->elements);
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iterator<T, T>
     */
    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Is the `Set` empty?
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isEmpty(): bool
    {
        return [] === $this->elements;
    }

    /**
     * Get the number of elements in the current `Set`.
     *
     * @psalm-mutation-free
     *
     * @return int<0, max>
     */
    #[Override]
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Get an array copy of the current `Set`.
     *
     * @return array<T, T>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Get an array copy of the current `Set`.
     *
     * @return array<T>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return array_values($this->elements);
    }

    /**
     * Returns the provided value if it exists in the current `Set`.
     *
     * As {@see Set} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it is returned to indicate presence in the set. If the value does not exist,
     * an {@see Exception\OutOfBoundsException} is thrown to indicate the absence of the value.
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function at(T $k): T
    {
        if (!array_key_exists($k, $this->elements)) {
            throw Exception\OutOfBoundsException::for($k);
        }

        return $this->elements[$k];
    }

    /**
     * Determines if the specified value is in the current set.
     *
     * As {@see Set} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it returns true to indicate presence in the set. If the value does not exist,
     * it returns false to indicate the absence of the value.
     *
     * @return bool True if the value is in the set, false otherwise.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function contains(T $k): bool
    {
        return array_key_exists($k, $this->elements);
    }

    /**
     * Alias of `contains`.
     *
     * @return bool True if the value is in the set, false otherwise.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function containsKey(T $k): bool
    {
        return $this->contains($k);
    }

    /**
     * Returns the provided value if it is part of the set, or null if it is not.
     *
     * As {@see Set} does not have keys, this method checks if the value exists in the set.
     * If the value exists, it is returned to indicate presence in the set. If the value does not exist,
     * null is returned to indicate the absence of the value.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function get(T $k): T|null
    {
        return $this->elements[$k] ?? null;
    }

    /**
     * Returns the first key in the current `Set`.
     *
     * As {@see Set} does not have keys, this method acts as an alias for {@see Set::first()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function firstKey(): T|null
    {
        return $this->first();
    }

    /**
     * Returns the last key in the current `Set`.
     *
     * As {@see Set} does not have keys, this method acts as an alias for {@see Set::last()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function lastKey(): T|null
    {
        return $this->last();
    }

    /**
     * Returns the key of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * As {@see Set} does not have keys, this method returns the value itself.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function linearSearch(T $searchValue): T|null
    {
        foreach ($this->elements as $key => $element) {
            if ($searchValue !== $element) {
                continue;
            }

            return $key;
        }

        return null;
    }

    /**
     * Returns a `Vector` containing the values of the current `Set`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): Vector<T>
    {
        return Vector::<T>::fromArray($this->elements);
    }

    /**
     * As {@see Set} does not have keys, this method acts as an alias for {@see Set::values()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): Vector<T>
    {
        return Vector::<T>::fromArray(array_keys($this->elements));
    }

    /**
     * Returns a `Set` containing the values of the current `Set`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `Set` values.
     */
    #[Override]
    public function filter(Closure $fn): Set<T>
    {
        return new Set::<T>(array_filter($this->elements, $fn, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Applies a user-defined condition to each value in the `Set`,
     *  considering the value as both key and value.
     *
     * This method extends {@see Set::filter()} by providing the value twice to the
     *  callback function: once as the "key" and once as the "value", even though {@see Set} do not have traditional key-value pairs.
     *
     * This allows for filtering based on both the value's "key" and "value" representation, which are identical.
     * It's particularly useful when the distinction between keys and values is relevant for the condition.
     *
     * @param (Closure(T, T): bool) $fn T
     */
    #[Override]
    public function filterWithKey(Closure $fn): Set<T>
    {
        return $this->filter(
            /**
             * @param T $k
             */
            static fn(string|int $k): bool => $fn($k, $k),
        );
    }

    /**
     * Returns a `Set` after an operation has been applied to each value
     * in the current `Set`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `Set` values.
     */
    #[Override]
    public function map<Tu: string|int>(Closure $fn): Set<Tu>
    {
        return new Set::<Tu>(array_map($fn, $this->elements));
    }

    /**
     * Transform the values of the current `Set` by applying the provided callback,
     *  considering the value as both key and value.
     *
     * Similar to {@see Set::map()}, this method extends the functionality by providing the value twice to the
     *  callback function: once as the "key" and once as the "value",
     *
     * The allows for transformations that take into account the value's dual role. It's useful for operations where the distinction
     *  between keys and values is relevant.
     *
     * @param (Closure(T, T): Tu) $fn
     */
    #[Override]
    public function mapWithKey<Tu: string|int>(Closure $fn): Set<Tu>
    {
        return $this->map::<Tu>(
            /**
             * @param T $k
             */
            static fn(string|int $k): string|int => $fn($k, $k),
        );
    }

    /**
     * Always throws an exception since `Set` can only contain array-key values.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `SetInterface`.
     *
     * @psalm-mutation-free
     *
     * @throws Exception\RuntimeException Always throws an exception since `Set` can only contain array-key values.
     */
    #[Override]
    public function zip<Tu>(array $elements): never
    {
        throw new Exception\RuntimeException('Cannot zip a Set.');
    }

    /**
     * Returns a `Set` containing the first `n` values of the current
     * `Set`.
     *
     * The returned `Set` will always be a proper subset of the current
     * `Set`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `Set`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): Set<T>
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `Set` containing the values of the current `Set`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `Set` will always be a proper subset of the current
     * `Set`.
     *
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): Set<T>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            if (!$fn($v)) {
                break;
            }

            $result[$k] = $v;
        }

        return new Set::<T>($result);
    }

    /**
     * Returns a `Set` containing the values after the `n`-th element of
     * the current `Set`.
     *
     * The returned `Set` will always be a proper subset of the current
     * `SetInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `Set`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): Set<T>
    {
        return $this->slice($n);
    }

    /**
     * Returns a `Set` containing the values of the current `Set`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `Set` will always be a proper subset of the current
     * `Set`.
     *
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `Set`.
     */
    #[Override]
    public function dropWhile(Closure $fn): Set<T>
    {
        $result = [];
        $dropping = true;
        foreach ($this->elements as $k => $v) {
            if ($dropping && $fn($v)) {
                continue;
            }

            $dropping = false;
            $result[$k] = $v;
        }

        return new Set::<T>($result);
    }

    /**
     * Returns a subset of the current `Set` starting from a given index up
     * to, but not including, the element at the provided length from the starting
     * index.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at index 0 and 1.
     *
     * The returned `Set` will always be a proper subset of this
     * `Set`.
     *
     * @param int<0, max> $start The starting index of this set to begin the returned
     *                           `Set`.
     * @param int<0, max> $length The length of the returned `Set`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): Set<T>
    {
        return self::<T>::fromArray(array_slice($this->elements, $start, $length, true));
    }

    /**
     * Returns a `Vector` containing the original `Set` split into
     * chunks of the given size.
     *
     * If the original `Set` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): Vector<Set<T>>
    {
        return Vector::<Set<T>>::fromArray(array_map(static::<T>::fromArray(...), array_chunk($this->toArray(), $size)));
    }
}
