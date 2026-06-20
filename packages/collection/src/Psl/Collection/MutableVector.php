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
use function array_key_last;
use function array_keys;
use function array_map;
use function array_search;
use function array_slice;
use function array_values;
use function count;
use function is_int;
use function iterator_to_array;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @api
 */
final class MutableVector<T> implements MutableVectorInterface<T>
{
    /**
     * @var list<T> $elements
     */
    private array $elements = [];

    /**
     * MutableVector constructor.
     *
     * @param array<array-key, T> $elements
     *
     * @psalm-mutation-free
     */
    public function __construct(array $elements)
    {
        $this->elements = array_values($elements);
    }

    /**
     * Creates and returns a default instance of {@see MutableVector}.
     *
     * @return static A default instance of {@see MutableVector}.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public static function default(): static
    {
        return new self::<T>([]);
    }

    /**
     * Create a vector from the given $elements array.
     *
     * @param array<array-key, T> $elements
     *
     * @pure
     */
    public static function fromArray(array $elements): MutableVector<T>
    {
        return new self::<T>($elements);
    }

    /**
     * Create a vector from the given $items iterable.
     *
     * @param iterable<array-key, T> $items
     */
    public static function fromItems(iterable $items): MutableVector<T>
    {
        $array = iterator_to_array($items);

        return self::<T>::fromArray($array);
    }

    /**
     * Returns the first value in the current `MutableVector`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): T|null
    {
        return $this->elements[0] ?? null;
    }

    /**
     * Returns the last value in the current `MutableVector`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function last(): T|null
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
     * @return Iterator<int, T>
     */
    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Is the vector empty?
     *
     * @psalm-mutation-free
     */
    #[Override]
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
    #[Override]
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Get an array copy of the current `MutableVector`.
     *
     * @return list<T>
     *
     * @psalm-mutation-free
     */
    #[Override]
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
    #[Override]
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
     * @psalm-mutation-free
     */
    #[Override]
    public function at(int|string $k): T
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
    #[Override]
    public function contains(int|string $k): bool
    {
        return array_key_exists($k, $this->elements);
    }

    /**
     * Alias of `contains`.
     *
     * @param int<0, max> $k
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function containsKey(int|string $k): bool
    {
        return $this->contains($k);
    }

    /**
     * Returns the value at the specified key in the current `MutableVector`.
     *
     * @param int<0, max> $k
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function get(int|string $k): T|null
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
    #[Override]
    public function firstKey(): null|int
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
    #[Override]
    public function lastKey(): null|int
    {
        return array_key_last($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @return int<0, max>|null The key (index) where that value is found; null if it is not found.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function linearSearch(T $searchValue): null|int
    {
        $key = array_search($searchValue, $this->elements, true);

        return false === $key ? null : $key;
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
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function set(int|string $k, T $v): MutableVector<T>
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
     * @psalm-external-mutation-free
     */
    #[Override]
    public function setAll(array $elements): MutableVector<T>
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
     * @psalm-external-mutation-free
     */
    #[Override]
    public function remove(int|string $k): MutableVector<T>
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
     * @psalm-external-mutation-free
     */
    #[Override]
    public function clear(): MutableVector<T>
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Add a value to the vector and return the vector itself.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function add(T $v): MutableVector<T>
    {
        $this->elements[] = $v;

        return $this;
    }

    /**
     * For every element in the provided elements iterable, add the value into the current vector.
     *
     * @param iterable<T> $elements The elements with the new values to add
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function addAll(iterable $elements): MutableVector<T>
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
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): MutableVector<T>
    {
        return MutableVector::<T>::fromArray($this->elements);
    }

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableVector`.
     *
     * @return MutableVector<int<0, max>>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): MutableVector<int>
    {
        return MutableVector::<int>::fromArray(array_keys($this->elements));
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
     */
    #[Override]
    public function filter(Closure $fn): MutableVector<T>
    {
        return new MutableVector::<T>(array_filter($this->elements, $fn));
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
     */
    #[Override]
    public function filterWithKey(Closure $fn): MutableVector<T>
    {
        return new MutableVector::<T>(array_filter(
            $this->elements,
            static fn($v, $k) => $fn($k, $v),
            ARRAY_FILTER_USE_BOTH,
        ));
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
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `MutableVector` values.
     */
    #[Override]
    public function map<Tu>(Closure $fn): MutableVector<Tu>
    {
        return new MutableVector::<Tu>(array_map($fn, $this->elements));
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
     * @param (Closure(int<0, max>, T): Tu) $fn The callback containing the operation to apply to the current
     *                                          `MutableVector` keys and values
     */
    #[Override]
    public function mapWithKey<Tu>(Closure $fn): MutableVector<Tu>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            $result[$k] = $fn($k, $v);
        }

        return new MutableVector::<Tu>($result);
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
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `MutableVector`.
     *
     * @return MutableVector<array{0: T, 1: Tu}> The `MutableVector` that combines the values of the current
     *                                           `MutableVector` with the provided elements.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function zip<Tu>(array $elements): MutableVector<array>
    {
        $elements = array_values($elements);
        $result = [];
        foreach ($this->elements as $i => $v) {
            if (!array_key_exists($i, $elements)) {
                break;
            }

            $result[] = [$v, $elements[$i]];
        }

        return MutableVector::<array>::fromArray($result);
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
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MutableVector<T>
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
     */
    #[Override]
    public function takeWhile(Closure $fn): MutableVector<T>
    {
        $result = [];
        foreach ($this->elements as $v) {
            if (!$fn($v)) {
                break;
            }

            $result[] = $v;
        }

        return new MutableVector::<T>($result);
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
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MutableVector<T>
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
     */
    #[Override]
    public function dropWhile(Closure $fn): MutableVector<T>
    {
        $result = [];
        $dropping = true;
        foreach ($this->elements as $v) {
            if ($dropping && $fn($v)) {
                continue;
            }

            $dropping = false;
            $result[] = $v;
        }

        return new MutableVector::<T>($result);
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
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MutableVector<T>
    {
        return MutableVector::<T>::fromArray(array_slice($this->elements, $start, $length, true));
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
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): MutableVector<MutableVector<T>>
    {
        return static::<MutableVector<T>>::fromArray(array_map(MutableVector::<T>::fromArray(...), array_chunk($this->toArray(), $size)));
    }

    /**
     * Determines if the specified offset exists in the current vector.
     *
     * @param mixed $offset An offset to check for.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not a positive integer.
     *
     * @psalm-mutation-free
     *
     * @psalm-assert int<0, max> $offset
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        if (!is_int($offset) || $offset < 0) {
            throw new Exception\InvalidOffsetException('Invalid vector read offset type, expected a positive integer.');
        }

        return $this->contains($offset);
    }

    /**
     * Returns the value at the specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not a positive integer.
     * @throws Exception\OutOfBoundsException If the offset does not exist.
     *
     * @psalm-mutation-free
     *
     * @psalm-assert int<0, max> $offset
     */
    #[Override]
    public function offsetGet(mixed $offset): T|null
    {
        if (!is_int($offset) || $offset < 0) {
            throw new Exception\InvalidOffsetException('Invalid vector read offset type, expected a positive integer.');
        }

        return $this->at($offset);
    }

    /**
     * Sets the value at the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     *
     * @psalm-external-mutation-free
     *
     * @psalm-assert null|int<0, max> $offset
     *
     * @throws Exception\InvalidOffsetException If the offset is not null or a positive integer.
     * @throws Exception\OutOfBoundsException If the offset is out-of-bounds.
     */
    #[Override]
    public function offsetSet(mixed $offset, T $value): void
    {
        if (null === $offset) {
            $this->add($value);

            return;
        }

        if (!is_int($offset) || $offset < 0) {
            throw new Exception\InvalidOffsetException(
                'Invalid vector write offset type, expected a positive integer or null.',
            );
        }

        $this->set($offset, $value);
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
        if (!is_int($offset) || $offset < 0) {
            throw new Exception\InvalidOffsetException('Invalid vector read offset type, expected a positive integer.');
        }

        $this->remove($offset);
    }
}
