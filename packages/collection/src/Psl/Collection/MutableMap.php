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
use function array_search;
use function array_slice;
use function array_values;
use function count;
use function is_int;
use function is_string;
use function iterator_to_array;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @api
 */
final class MutableMap<Tk: string|int, Tv> implements MutableMapInterface<Tk, Tv>
{
    /**
     * @var array<Tk, Tv> $elements
     */
    private array $elements;

    /**
     * @param array<Tk, Tv> $elements
     *
     * @psalm-mutation-free
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * Creates and returns a default instance of {@see MutableMap}.
     *
     * @return static A default instance of {@see MutableMap}.
     *
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self::<Tk, Tv>([]);
    }

    /**
     * @param array<Tk, Tv> $elements
     *
     * @pure
     */
    public static function fromArray(array $elements): MutableMap<Tk, Tv>
    {
        return new self::<Tk, Tv>($elements);
    }

    /**
     * @param array<Tk, Tv> $items
     */
    public static function fromItems(iterable $items): MutableMap<Tk, Tv>
    {
        return self::<Tk, Tv>::fromArray(iterator_to_array($items));
    }

    /**
     * Returns the first value in the current collection.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function first(): Tv|null
    {
        $key = $this->firstKey();
        if (null === $key) {
            return null;
        }

        return $this->elements[$key];
    }

    /**
     * Returns the first key in the current collection.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function firstKey(): Tk|null
    {
        return array_key_first($this->elements);
    }

    /**
     * Returns the last value in the current collection.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function last(): Tv|null
    {
        $key = $this->lastKey();
        if (null === $key) {
            return null;
        }

        return $this->elements[$key];
    }

    /**
     * Returns the last key in the current collection.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function lastKey(): Tk|null
    {
        return array_key_last($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function linearSearch(Tv $searchValue): Tk|null
    {
        $key = array_search($searchValue, $this->elements, true);

        return false === $key ? null : $key;
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iterator<Tk, Tv>
     */
    #[Override]
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Is the map empty?
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isEmpty(): bool
    {
        return [] === $this->elements;
    }

    /**
     * Get the number of elements in the current map.
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
     * Get an array copy of the current map.
     *
     * @return array<Tk, Tv>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Returns the map's elements as an object.
     *
     * This ensures that the map is always serialized as a JSON object ({}) and not a JSON array ([]).
     *
     * PHP's `json_encode` serializes empty arrays as `[]`.  Also, arrays with sequential integer keys starting
     * from 0 are serialized  as JSON arrays too, like `[0 => 'a', 1 => 'b']` becoming `['a', 'b']`.
     *
     * By casting to an object, we guarantee that the map will always be a JSON object `{}` when serialized,
     * even if empty or having sequential integer keys.
     *
     * @return object
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function jsonSerialize(): object
    {
        return (object) $this->elements;
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function at(Tk $k): Tv
    {
        if (!array_key_exists($k, $this->elements)) {
            throw Exception\OutOfBoundsException::for($k);
        }

        return $this->elements[$k];
    }

    /**
     * Determines if the specified key is in the current map.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function contains(Tk $k): bool
    {
        return array_key_exists($k, $this->elements);
    }

    /**
     * Alias of `contains`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function containsKey(Tk $k): bool
    {
        return $this->contains($k);
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function get(Tk $k): Tv|null
    {
        return $this->elements[$k] ?? null;
    }

    /**
     * Returns a `MutableVector` containing the values of the current
     * `MutableMap`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): MutableVector<Tv>
    {
        return MutableVector::<Tv>::fromArray($this->elements);
    }

    /**
     * Returns a `MutableVector` containing the keys of the current `MutableMap`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): MutableVector<Tk>
    {
        return MutableVector::<Tk>::fromArray(array_keys($this->elements));
    }

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `MutableMap` remain unchanged in the
     * returned `MutableMap`.
     *
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `MutableMap` values.
     */
    #[Override]
    public function filter(Closure $fn): MutableMap<Tk, Tv>
    {
        return new MutableMap::<Tk, Tv>(array_filter($this->elements, $fn));
    }

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `MutableMap` remain unchanged in the
     * returned `MutableMap`; the keys will be used in the filtering process only.
     *
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `MutableMap` keys and values.
     */
    #[Override]
    public function filterWithKey(Closure $fn): MutableMap<Tk, Tv>
    {
        return new MutableMap::<Tk, Tv>(array_filter($this->elements, static fn($v, $k) => $fn($k, $v), ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Returns a `MutableMap` after an operation has been applied to each value
     * in the current `MutableMap`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `MutableMap` to the
     * returned `MutableMap`.
     *
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `MutableMap` values.
     */
    #[Override]
    public function map<Tu>(Closure $fn): MutableMap<Tk, Tu>
    {
        return new MutableMap::<Tk, Tu>(array_map($fn, $this->elements));
    }

    /**
     * Returns a `MutableMap` after an operation has been applied to each key and
     * value in the current `MutableMap`.
     *
     * Every key and value in the current `MutableMap` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `MutableMap` to the returned
     * `MutableMap`. The keys are only used to help in the mapping operation.
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `MutableMap` keys and values.
     */
    #[Override]
    public function mapWithKey<Tu>(Closure $fn): MutableMap<Tk, Tu>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            $result[$k] = $fn($k, $v);
        }

        return new MutableMap::<Tk, Tu>($result);
    }

    /**
     * Returns a `MutableMap` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `MutableMap` and the provided elements.
     *
     * If the number of elements of the `MutableMap` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the
     *                                       elements of this `MutableMap`.
     *
     * @return MutableMap<Tk, array{0: Tv, 1: Tu}> A `MutableMap` that combines the values of the current
     *                                             `MutableMap` with the provided elements.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function zip<Tu>(array $elements): MutableMap<Tk, array>
    {
        $elements = array_values($elements);
        $count = count($elements);
        /** @var array<Tk, array{0: Tv, 1: Tu}> $result */
        $result = [];
        $i = 0;
        foreach ($this->elements as $k => $v) {
            if ($i >= $count) {
                break;
            }

            $result[$k] = [$v, $elements[$i]];
            $i++;
        }

        return self::<Tk, array>::fromArray($result);
    }

    /**
     * Returns a `MutableMap` containing the first `n` values of the current
     * `MutableMap`.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `MutableMap`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): MutableMap<Tk, Tv>
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): MutableMap<Tk, Tv>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            if (!$fn($v)) {
                break;
            }

            $result[$k] = $v;
        }

        return new MutableMap::<Tk, Tv>($result);
    }

    /**
     * Returns a `MutableMap` containing the values after the `n`-th element of
     * the current `MutableMap`.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `MutableMap`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): MutableMap<Tk, Tv>
    {
        return self::<Tk, Tv>::fromArray(array_slice($this->elements, $n, null, true));
    }

    /**
     * Returns a `MutableMap` containing the values of the current `MutableMap`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `MutableMap` will always be a proper subset of the current
     * `MutableMap`.
     *
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `MutableMap`.
     */
    #[Override]
    public function dropWhile(Closure $fn): MutableMap<Tk, Tv>
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

        return new MutableMap::<Tk, Tv>($result);
    }

    /**
     * Returns a subset of the current `MutableMap` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `MutableMap` will always be a proper subset of this
     * `MutableMap`.
     *
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `MutableMap`
     * @param null|int<0, max> $length The length of the returned `MutableMap`
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): MutableMap<Tk, Tv>
    {
        return self::<Tk, Tv>::fromArray(array_slice($this->elements, $start, $length, true));
    }

    /**
     * Returns a `MutableVector` containing the original `MutableMap` split into
     * chunks of the given size.
     *
     * If the original `MutableMap` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): MutableVector<MutableMap<Tk, Tv>>
    {
        $chunks = array_map(MutableMap::<Tk, Tv>::fromArray(...), array_chunk($this->elements, $size, true));

        return MutableVector::<MutableMap<Tk, Tv>>::fromArray($chunks);
    }

    /**
     * Stores a value into the current map with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * It returns the current map, meaning changes made to the current
     * map will be reflected in the returned map.
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function set(Tk $k, Tv $v): MutableMap<Tk, Tv>
    {
        if (!array_key_exists($k, $this->elements)) {
            throw Exception\OutOfBoundsException::for($k);
        }

        $this->elements[$k] = $v;

        return $this;
    }

    /**
     * For every element in the provided elements, stores a value into the
     * current map associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `addAll()`.
     *
     * It the current map, meaning changes made to the current map
     * will be reflected in the returned map.
     *
     * @param array<Tk, Tv> $elements The elements with the new values to set
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function setAll(array $elements): MutableMap<Tk, Tv>
    {
        foreach ($elements as $k => $v) {
            $this->set($k, $v);
        }

        return $this;
    }

    /**
     * Add a value to the map and return the map itself.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function add(Tk $k, Tv $v): MutableMap<Tk, Tv>
    {
        $this->elements[$k] = $v;

        return $this;
    }

    /**
     * For every element in the provided elements, add the value into the current map.
     *
     * @param iterable<Tk, Tv> $elements The elements with the new values to add.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function addAll(iterable $elements): MutableMap<Tk, Tv>
    {
        foreach ($elements as $k => $v) {
            $this->add($k, $v);
        }

        return $this;
    }

    /**
     * Removes the specified key (and associated value) from the current
     * map.
     *
     * If the key is not in the current map, the current map is
     * unchanged.
     *
     * It the current map, meaning changes made to the current map
     * will be reflected in the returned map.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function remove(Tk $k): MutableMap<Tk, Tv>
    {
        if ($this->contains($k)) {
            unset($this->elements[$k]);
        }

        return $this;
    }

    /**
     * Removes all elements from the map.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function clear(): MutableMap<Tk, Tv>
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Determines if the specified offset exists in the current map.
     *
     * @param mixed $offset An offset to check for.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     *
     * @psalm-assert array-key $offset
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new Exception\InvalidOffsetException(
                'Invalid map read offset type, expected a string or an integer.',
            );
        }

        /** @var Tk $offset - technically, we don't know if the offset is of type Tk, but we can assume it is, as this causes no "harm". */
        return $this->contains($offset);
    }

    /**
     * Returns the value at the specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     * @throws Exception\OutOfBoundsException If the offset is out-of-bounds.
     *
     * @psalm-mutation-free
     *
     * @psalm-assert array-key $offset
     */
    #[Override]
    public function offsetGet(mixed $offset): Tv|null
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new Exception\InvalidOffsetException(
                'Invalid map read offset type, expected a string or an integer.',
            );
        }

        /** @var Tk $offset - technically, we don't know if the offset is of type Tk, but we can assume it is, as this causes no "harm". */
        return $this->at($offset);
    }

    /**
     * Sets the value at the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     *
     * @psalm-external-mutation-free
     *
     * @psalm-assert Tk $offset
     *
     * @throws Exception\InvalidOffsetException If the offset type is not valid.
     * @throws Exception\OutOfBoundsException If the offset is out-of-bounds.
     */
    #[Override]
    public function offsetSet(mixed $offset, Tv $value): void
    {
        if (!is_int($offset) && !is_string($offset)) {
            throw new Exception\InvalidOffsetException(
                'Invalid map write offset type, expected a string or an integer.',
            );
        }

        /** @var Tk $offset - technically, we don't know if the offset is of type Tk, but we can assume it is, as this causes no "harm". */
        $this->add($offset, $value);
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
                'Invalid map read offset type, expected a string or an integer.',
            );
        }

        /** @var Tk $offset - technically, we don't know if the offset is of type Tk, but we can assume it is, as this causes no "harm". */
        $this->remove($offset);
    }
}
