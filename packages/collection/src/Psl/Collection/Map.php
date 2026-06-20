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
use function iterator_to_array;

use const ARRAY_FILTER_USE_BOTH;

/**
 * @api
 */
final readonly class Map<Tk: string|int, Tv> implements MapInterface<Tk, Tv>
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
     * Creates and returns a default instance of {@see Map}.
     *
     * @return static A default instance of {@see Map}.
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
    public static function fromArray(array $elements): Map<Tk, Tv>
    {
        return new self::<Tk, Tv>($elements);
    }

    /**
     * @param array<Tk, Tv> $items
     */
    public static function fromItems(iterable $items): Map<Tk, Tv>
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
     * Returns a `Vector` containing the values of the current
     * `Map`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function values(): Vector<Tv>
    {
        return Vector::<Tv>::fromArray($this->elements);
    }

    /**
     * Returns a `Vector` containing the keys of the current `Map`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function keys(): Vector<Tk>
    {
        return Vector::<Tk>::fromArray(array_keys($this->elements));
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * that meet a supplied condition.
     *
     * Only values that meet a certain criteria are affected by a call to
     * `filter()`, while all values are affected by a call to `map()`.
     *
     * The keys associated with the current `Map` remain unchanged in the
     * returned `Map`.
     *
     * @param (Closure(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                `Map` values.
     */
    #[Override]
    public function filter(Closure $fn): Map<Tk, Tv>
    {
        return new Map::<Tk, Tv>(array_filter($this->elements, $fn));
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * that meet a supplied condition applied to its keys and values.
     *
     * Only keys and values that meet a certain criteria are affected by a call
     * to `filterWithKey()`, while all values are affected by a call to
     * `mapWithKey()`.
     *
     * The keys associated with the current `Map` remain unchanged in the
     * returned `Map`; the keys will be used in the filtering process only.
     *
     * @param (Closure(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                    `Map` keys and values.
     */
    #[Override]
    public function filterWithKey(Closure $fn): Map<Tk, Tv>
    {
        return new Map::<Tk, Tv>(array_filter($this->elements, static fn($v, $k) => $fn($k, $v), ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Returns a `Map` after an operation has been applied to each value
     * in the current `Map`.
     *
     * Every value in the current Map is affected by a call to `map()`, unlike
     * `filter()` where only values that meet a certain criteria are affected.
     *
     * The keys will remain unchanged from the current `Map` to the
     * returned `Map`.
     *
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `Map` values.
     */
    #[Override]
    public function map<Tu>(Closure $fn): Map<Tk, Tu>
    {
        return new Map::<Tk, Tu>(array_map($fn, $this->elements));
    }

    /**
     * Returns a `Map` after an operation has been applied to each key and
     * value in the current `Map`.
     *
     * Every key and value in the current `Map` is affected by a call to
     * `mapWithKey()`, unlike `filterWithKey()` where only values that meet a
     * certain criteria are affected.
     *
     * The keys will remain unchanged from this `Map` to the returned
     * `Map`. The keys are only used to help in the mapping operation.
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `Map` keys and values.
     */
    #[Override]
    public function mapWithKey<Tu>(Closure $fn): Map<Tk, Tu>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            $result[$k] = $fn($k, $v);
        }

        return new Map::<Tk, Tu>($result);
    }

    /**
     * Returns a `Map` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `Map` and the provided elements.
     *
     * If the number of elements of the `Map` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `Map`.
     *
     * @return Map<Tk, array{0: Tv, 1: Tu}> The `Map` that combines the values of the current `Map` with the provided elements.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function zip<Tu>(array $elements): Map<Tk, array>
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

        return new Map::<Tk, array>($result);
    }

    /**
     * Returns a `Map` containing the first `n` values of the current
     * `Map`.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `Map`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function take(int $n): Map<Tk, Tv>
    {
        return $this->slice(0, $n);
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * up to but not including the first value that produces `false` when passed
     * to the specified callback.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @param (Closure(Tv): bool) $fn The callback that is used to determine the stopping
     *                                condition.
     */
    #[Override]
    public function takeWhile(Closure $fn): Map<Tk, Tv>
    {
        $result = [];
        foreach ($this->elements as $k => $v) {
            if (!$fn($v)) {
                break;
            }

            $result[$k] = $v;
        }

        return new Map::<Tk, Tv>($result);
    }

    /**
     * Returns a `Map` containing the values after the `n`-th element of
     * the current `Map`.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * `$n` is 1-based. So the first element is 1, the second 2, etc.
     *
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `Map`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function drop(int $n): Map<Tk, Tv>
    {
        return $this->slice($n);
    }

    /**
     * Returns a `Map` containing the values of the current `Map`
     * starting after and including the first value that produces `true` when
     * passed to the specified callback.
     *
     * The returned `Map` will always be a proper subset of the current
     * `Map`.
     *
     * @param (Closure(Tv): bool) $fn The callback used to determine the starting element for the
     *                                returned `Map`.
     */
    #[Override]
    public function dropWhile(Closure $fn): Map<Tk, Tv>
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

        return new Map::<Tk, Tv>($result);
    }

    /**
     * Returns a subset of the current `Map` starting from a given key up
     * to, but not including, the element at the provided length from the starting
     * key.
     *
     * `$start` is 0-based. $len is 1-based. So `slice(0, 2)` would return the
     * elements at key 0 and 1.
     *
     * The returned `Map` will always be a proper subset of this `Map`.
     *
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `Map`.
     * @param null|int<0, max> $length The length of the returned `Map`
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function slice(int $start, null|int $length = null): Map<Tk, Tv>
    {
        return self::<Tk, Tv>::fromArray(array_slice($this->elements, $start, $length, true));
    }

    /**
     * Returns a `Vector` containing the original `Map` split into
     * chunks of the given size.
     *
     * If the original `Map` doesn't divide evenly, the final chunk will be smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function chunk(int $size): Vector<Map<Tk, Tv>>
    {
        $chunks = array_map(static fn(array $chunk): Map => new Map::<Tk, Tv>($chunk), array_chunk($this->elements, $size, true));

        return Vector::<Map<Tk, Tv>>::fromArray($chunks);
    }
}
