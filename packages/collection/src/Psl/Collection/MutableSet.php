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
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function is_int;
use function is_string;
use function iterator_to_array;

use const ARRAY_FILTER_USE_KEY;

/**
 * @api
 */
final class MutableSet<T: string|int> implements MutableSetInterface<T>
{
    /**
     * @var array<T, T>
     */
    private array $elements = [];

    /**
     * Creates a new `MutableSet` containing the values of the given array.
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
     * Creates and returns a default instance of {@see MutableSet}.
     *
     * @return static A default instance of {@see MutableSet}.
     *
     * @psalm-external-mutation-free
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
    public static function fromArray(array $elements): MutableSet<T>
    {
        return new self::<T>($elements);
    }

    /**
     * Create a set from the given iterable, using the values of the iterable as the set values.
     *
     * @param iterable<T, T> $items
     */
    public static function fromItems(iterable $items): MutableSet<T>
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
    public static function fromArrayKeys(array $elements): MutableSet<T>
    {
        /** @var array<T, T> $set */
        $set = [];
        foreach ($elements as $element => $_) {
            $set[$element] = $element;
        }

        return new self::<T>($set);
    }

    /**
     * Returns the first value in the current `MutableSet`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): T|null
    {
        return array_key_first($this->elements);
    }

    /**
     * Returns the last value in the current `MutableSet`.
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
     * Is the set empty?
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isEmpty(): bool
    {
        return [] === $this->elements;
    }

    /**
     * Get the number of elements in the current `MutableSet`.
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
     * Get an array copy of the current `MutableSet`.
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
     * Get an array copy of the current `MutableSet`.
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
     * Returns the provided value if it exists in the current `MutableSet`.
     *
     * As {@see MutableSet} does not have keys, this method checks if the value exists in the set.
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

        // the key exists, and we know it's the same as the value.
        return $k;
    }

    /**
     * Determines if the specified value is in the current set.
     *
     * As {@see MutableSet} does not have keys, this method checks if the value exists in the set.
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
     * As {@see MutableSet} does not have keys, this method checks if the value exists in the set.
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
     * Returns the first key in the current `MutableSet`.
     *
     * As {@see MutableSet} does not have keys, this method acts as an alias for {@see MutableSet::first()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function firstKey(): T|null
    {
        return $this->first();
    }

    /**
     * Returns the last key in the current `MutableSet`.
     *
     * As {@see MutableSet} does not have keys, this method acts as an alias for {@see MutableSet::last()}.
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
     * As {@see MutableSet} does not have keys, this method returns the value itself.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function linearSearch(T $searchValue): T|null
    {
        foreach ($this->elements as $element) {
            if ($searchValue !== $element) {
                continue;
            }

            return $element;
        }

        return null;
    }

    /**
     * Removes the specified value from the current set.
     *
     * If the value is not in the current set, the current set is unchanged.
     */
    #[Override]
    public function remove(T $k): MutableSet<T>
    {
        unset($this->elements[$k]);

        return $this;
    }

    /**
     * Removes all elements from the set.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function clear(): MutableSet<T>
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Add a value to the set and return the set itself.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function add(T $v): MutableSet<T>
    {
        $this->elements[$v] = $v;

        return $this;
    }

    /**
     * For every element in the provided elements iterable, add the value into the current set.
     *
     * @param iterable<T> $elements The elements with the new values to add
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function addAll(iterable $elements): MutableSet<T>
    {
        foreach ($elements as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * Returns a `MutableVector` containing the values of the current `MutableSet`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): MutableVector<T>
    {
        return MutableVector::<T>::fromArray($this->elements);
    }

    /**
     * As {@see MutableSet} does not have keys, this method acts as an alias for {@see MutableSet::values()}.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): MutableVector<T>
    {
        return MutableVector::<T>::fromArray($this->elements);
    }

    /**
     * Returns a `MutableSet` containing the values of the current `MutableSet`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableSet` remain unchanged in the
     * returned `MutableSet`.
     *
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `MutableSet` values.
     */
    #[Override]
    public function filter(Closure $fn): MutableSet<T>
    {
        return new MutableSet::<T>(array_filter($this->elements, $fn, ARRAY_FILTER_USE_KEY));
    }

    /**
     * Applies a user-defined condition to each value in the `MutableSet`,
     *  considering the value as both key and value.
     *
     * This method extends {@see MutableSet::filter()} by providing the value twice to the
     *  callback function: once as the "key" and once as the "value", even though {@see MutableSet} do not have traditional key-value pairs.
     *
     * This allows for filtering based on both the value's "key" and "value" representation, which are identical.
     * It's particularly useful when the distinction between keys and values is relevant for the condition.
     *
     * @param (Closure(T, T): bool) $fn T
     */
    #[Override]
    public function filterWithKey(Closure $fn): MutableSet<T>
    {
        return $this->filter(
            /**
             * @param T $k
             */
            static fn(string|int $k): bool => $fn($k, $k),
        );
    }

    /**
     * Returns a `MutableSet` after an operation has been applied to each value
     * in the current `MutableSet`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableSet` to the
     * returned `MutableSet`.
     *
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `MutableSet` values.
     */
    #[Override]
    public function map<Tu: string|int>(Closure $fn): MutableSet<Tu>
    {
        return new MutableSet::<Tu>(array_map($fn, $this->elements));
    }

    /**
     * Transform the values of the current `MutableSet` by applying the provided callback,
     *  considering the value as both key and value.
     *
     * Similar to {@see MutableSet::map()}, this method extends the functionality by providing the value twice to the
     *  callback function: once as the "key" and once as the "value",
     *
     * The allows for transformations that take into account the value's dual role. It's useful for operations where the distinction
     *  between keys and values is relevant.
     *
     * @param (Closure(T, T): Tu) $fn
     */
    #[Override]
    public function mapWithKey<Tu: string|int>(Closure $fn): MutableSet<Tu>
    {
        return $this->map::<Tu>(
            /**
             * @param T $k
             */
            static fn(string|int $k): string|int => $fn($k, $k),
        );
    }

    /**
     * Always throws an exception since `MutableSet` can only contain array-key values.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableSet`.
     *
     * @psalm-mutation-free
     *
     * @throws Exception\RuntimeException Always throws an exception since `MutableSet` can only contain array-key values.
     */
    #[Override]
    public function zip<Tu>(array $elements): never
    {
        throw new Exception\RuntimeException('Cannot zip a MutableSet.');
    }

    /**
     * Returns a `MutableSet` containing the first `n` values of the current
     * `MutableSet`.
     *
     * The returned `MutableSet` will always be a proper subset of the current
     * `MutableSet`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `MutableSet`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MutableSet<T>
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `MutableSet` containing the values of the current `MutableSet`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableSet` will always be a proper subset of the current
     * `MutableSet`.
     *
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): MutableSet<T>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            if (!$fn($v)) {
                break;
            }

            $result[$k] = $v;
        }

        return new MutableSet::<T>($result);
    }

    /**
     * Returns a `MutableSet` containing the values after the `n`-th element of
     * the current `MutableSet`.
     *
     * The returned `MutableSet` will always be a proper subset of the current
     * `setInterface`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableSet`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MutableSet<T>
    {
        return $this->slice($n);
    }

    /**
     * Returns a `MutableSet` containing the values of the current `MutableSet`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableSet` will always be a proper subset of the current
     * `MutableSet`.
     *
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `MutableSet`.
     */
    #[Override]
    public function dropWhile(Closure $fn): MutableSet<T>
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

        return new MutableSet::<T>($result);
    }

    /**
     * Returns a subset of the current `MutableSet` starting from a given index up
     * to, but not including, the element at the provided length from the starting
     * index.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at index 0 and 1.
     *
     * The returned `MutableSet` will always be a proper subset of this
     * `MutableSet`.
     *
     * @param int<0, max> $start The starting index of this set to begin the returned
     *                           `MutableSet`.
     * @param int<0, max> $length The length of the returned `MutableSet`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MutableSet<T>
    {
        return MutableSet::<T>::fromArray(array_slice($this->elements, $start, $length, true));
    }

    /**
     * Returns a `MutableVector` containing the original `MutableSet` split into
     * chunks of the given size.
     *
     * If the original `MutableSet` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): MutableVector<MutableSet<T>>
    {
        return MutableVector::<MutableSet<T>>::fromArray(array_map(MutableSet::<T>::fromArray(...), array_chunk($this->toArray(), $size)));
    }

    /**
     * Determines if the specified offset exists in the current set.
     *
     * @param mixed $offset An offset to check for.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     *
     * @return bool Returns true if the specified offset exists, false otherwise.
     *
     * @psalm-mutation-free
     *
     * @psalm-assert array-key $offset
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new Exception\InvalidOffsetException(
                'Invalid set read offset type, expected a string or an integer.',
            );
        }

        /** @var T $offset - technically, we don't know if the offset is of type T, but we can assume it is, as this causes no "harm". */
        return $this->contains($offset);
    }

    /**
     * Returns the value at the specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not array-key.
     * @throws Exception\OutOfBoundsException If the offset does not exist.
     *
     * @psalm-mutation-free
     *
     * @psalm-assert array-key $offset
     */
    #[Override]
    public function offsetGet(mixed $offset): T
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new Exception\InvalidOffsetException(
                'Invalid set read offset type, expected a string or an integer.',
            );
        }

        /** @var T $offset - technically, we don't know if the offset is of type T, but we can assume it is, as this causes no "harm". */
        return $this->at($offset);
    }

    /**
     * Sets the value at the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     *
     * @psalm-external-mutation-free
     *
     * @psalm-assert null|array-key $offset
     *
     * @throws Exception\InvalidOffsetException If the offset is not null or the value is not the same as the offset.
     */
    #[Override]
    public function offsetSet(mixed $offset, T $value): void
    {
        if (null === $offset || $offset === $value) {
            $this->add($value);

            return;
        }

        throw new Exception\InvalidOffsetException(
            'Invalid set write offset type, expected null or the same as the value.',
        );
    }

    /**
     * Unsets the value at the specified offset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @psalm-external-mutation-free
     *
     * @psalm-assert array-key $offset
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new Exception\InvalidOffsetException(
                'Invalid set read offset type, expected a string or an integer.',
            );
        }

        /** @var T $offset - technically, we don't know if the offset is of type T, but we can assume it is, as this causes no "harm". */
        $this->remove($offset);
    }
}
