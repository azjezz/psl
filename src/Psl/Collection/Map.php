<?php

declare(strict_types=1);

namespace Psl\Collection;

use Closure;
use Psl\Dict;
use Psl\Iter;

use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_slice;
use function array_values;
use function count;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @implements MapInterface<Tk, Tv>
 */
final class Map implements MapInterface
{
    /**
     * @param array<Tk, Tv> $elements
     */
    public function __construct(
        private readonly array $elements
    ) {
    }

    /**
     * @template Tsk of array-key
     * @template Tsv
     *
     * @param array<Tsk, Tsv> $elements
     *
     * @return Map<Tsk, Tsv>
     *
     * @pure
     */
    public static function fromArray(array $elements): Map
    {
        /** @psalm-suppress ImpureMethodCall - conditionally pure */
        return new self($elements);
    }

    /**
     * Returns the first value in the current collection.
     *
     * @return Tv|null The first value in the current collection, or `null` if the
     *                 current collection is empty.
     *
     * @psalm-mutation-free
     */
    public function first(): mixed
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
     * @return Tk|null The first key in the current collection, or `null` if the
     *                 current collection is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): int|string|null
    {
        return array_key_first($this->elements);
    }

    /**
     * Returns the last value in the current collection.
     *
     * @return Tv|null The last value in the current collection, or `null` if the
     *                 current collection is empty.
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
     * Returns the last key in the current collection.
     *
     * @return Tk|null The last key in the current collection, or `null` if the
     *                 current collection is empty.
     *
     * @psalm-mutation-free
     */
    public function lastKey(): int|string|null
    {
        return array_key_last($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @param Tv $search_value The value that will be search for in the current
     *                         collection.
     *
     * @return Tk|null The key (index) where that value is found; null if it is not found
     *
     * @psalm-mutation-free
     */
    public function linearSearch(mixed $search_value): int|string|null
    {
        foreach ($this->elements as $key => $element) {
            if ($search_value === $element) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iter\Iterator<Tk, Tv>
     */
    public function getIterator(): Iter\Iterator
    {
        return Iter\Iterator::create($this->elements);
    }

    /**
     * Is the map empty?
     *
     * @psalm-mutation-free
     */
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
    public function count(): int
    {
        /** @var int<0, max> */
        return count($this->elements);
    }

    /**
     * Get an array copy of the current map.
     *
     * @return array<Tk, Tv>
     *
     * @psalm-mutation-free
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Get an array copy of the current map.
     *
     * @return array<Tk, Tv>
     *
     * @psalm-mutation-free
     */
    public function jsonSerialize(): array
    {
        return $this->elements;
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @param Tk $k
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @return Tv
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
     * Determines if the specified key is in the current map.
     *
     * @param Tk $k
     *
     * @psalm-mutation-free
     */
    public function contains(int|string $k): bool
    {
        return array_key_exists($k, $this->elements);
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @param Tk $k
     *
     * @return Tv|null
     *
     * @psalm-mutation-free
     */
    public function get(string|int $k): mixed
    {
        return $this->elements[$k] ?? null;
    }


    /**
     * Returns a `Vector` containing the values of the current
     * `Map`.
     *
     * @return Vector<Tv>
     *
     * @psalm-mutation-free
     */
    public function values(): Vector
    {
        return Vector::fromArray($this->elements);
    }

    /**
     * Returns a `Vector` containing the keys of the current `Map`.
     *
     * @return Vector<Tk>
     *
     * @psalm-mutation-free
     */
    public function keys(): Vector
    {
        return Vector::fromArray(array_keys($this->elements));
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
     *
     * @return Map<Tk, Tv> A Map containing the values after a user-specified condition
     *                     is applied.
     */
    public function filter(Closure $fn): Map
    {
        return new Map(Dict\filter($this->elements, $fn));
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
     *
     * @return Map<Tk, Tv> A `Map` containing the values after a user-specified
     *                     condition is applied to the keys and values of the current `Map`.
     */
    public function filterWithKey(Closure $fn): Map
    {
        return new Map(Dict\filter_with_key($this->elements, $fn));
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
     * @template Tu
     *
     * @param (Closure(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                              `Map` values.
     *
     * @return Map<Tk, Tu> A `Map` containing key/value pairs after a user-specified
     *                     operation is applied.
     */
    public function map(Closure $fn): Map
    {
        return new Map(Dict\map($this->elements, $fn));
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
     * @template Tu
     *
     * @param (Closure(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                  `Map` keys and values.
     *
     * @return Map<Tk, Tu> A `Map` containing the values after a user-specified
     *                     operation on the current `Map`'s keys and values is applied.
     */
    public function mapWithKey(Closure $fn): Map
    {
        return new Map(Dict\map_with_key($this->elements, $fn));
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
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `Map`.
     *
     * @return Map<Tk, array{0: Tv, 1: Tu}> The `Map` that combines the values of the current `Map` with the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): Map
    {
        $elements = array_values($elements);
        /** @var array<Tk, array{0: Tv, 1: Tu}> $result */
        $result = [];
        foreach ($this->elements as $k => $v) {
            $u = $elements[0] ?? null;
            if (null === $u) {
                break;
            }

            $elements = array_slice($elements, 1);

            $result[$k] = [$v, $u];
        }

        return new Map($result);
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
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                     `Map` up to `n` elements.
     *
     * @psalm-mutation-free
     */
    public function take(int $n): Map
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
     *
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                     `Map` up until the callback returns `false`.
     */
    public function takeWhile(Closure $fn): Map
    {
        return new Map(Dict\take_while($this->elements, $fn));
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
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                     `Map` containing values after the specified `n`-th element.
     *
     * @psalm-mutation-free
     */
    public function drop(int $n): Map
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
     *
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                     `Map` starting after the callback returns `true`.
     */
    public function dropWhile(Closure $fn): Map
    {
        return new Map(Dict\drop_while($this->elements, $fn));
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
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                     `Map` starting at `$start` up to but not including the element `$start + $length`.
     *
     * @psalm-mutation-free
     */
    public function slice(int $start, ?int $length = null): Map
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        $result = Dict\slice($this->elements, $start, $length);

        return self::fromArray($result);
    }

    /**
     * Returns a `Vector` containing the original `Map` split into
     * chunks of the given size.
     *
     * If the original `Map` doesn't divide evenly, the final chunk will be smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return Vector<Map<Tk, Tv>> A `Vector` containing the original `Map` split into chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): Vector
    {
        /** @psalm-suppress ImpureMethodCall */
        return $this
            ->zip($this->keys()->toArray())
            ->values()
            ->chunk($size)
            ->map(
                /**
                 * @param Vector<array{0: Tv, 1: Tk}> $vector
                 *
                 * @return Map<Tk, Tv>
                 *
                 * @pure
                 */
                static function (Vector $vector): Map {
                    /** @var array<Tk, Tv> $array */
                    $array = [];
                    foreach ($vector->toArray() as [$v, $k]) {
                        $array[$k] = $v;
                    }

                    return Map::fromArray($array);
                }
            );
    }
}
