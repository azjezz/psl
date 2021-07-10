<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl;
use Psl\Dict;
use Psl\Iter;
use Psl\Vec;

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
     * @var array<Tk, Tv> $elements
     *
     * @psalm-readonly
     */
    private array $elements;

    /**
     * AbstractMap constructor.
     *
     * @param iterable<Tk, Tv> $elements
     */
    public function __construct(iterable $elements)
    {
        $this->elements = Dict\from_iterable($elements);
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
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\first($this->elements);
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
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\first_key($this->elements);
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
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\last($this->elements);
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
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\last_key($this->elements);
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
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the current map.
     *
     * @psalm-mutation-free
     */
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
        return $this->toArray();
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @param Tk $k
     *
     * @throws Psl\Exception\InvariantViolationException If $k is out-of-bounds.
     *
     * @return Tv
     *
     * @psalm-mutation-free
     */
    public function at(string|int $k): mixed
    {
        Psl\invariant($this->contains($k), 'Key (%s) is out-of-bounds.', $k);

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
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Iter\contains_key($this->elements, $k);
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
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Vector::fromArray(Vec\keys($this->elements));
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
     * @param (callable(Tv): bool) $fn The callback containing the condition to apply to the current
     *                                 `Map` values.
     *
     * @return Map<Tk, Tv> A Map containing the values after a user-specified condition
     *                 is applied.
     */
    public function filter(callable $fn): Map
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
     * @param (callable(Tk, Tv): bool) $fn The callback containing the condition to apply to the current
     *                                     `Map` keys and values.
     *
     * @return Map<Tk, Tv> A `Map` containing the values after a user-specified
     *                 condition is applied to the keys and values of the current `Map`.
     */
    public function filterWithKey(callable $fn): Map
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
     * @param (callable(Tv): Tu) $fn The callback containing the operation to apply to the current
     *                               `Map` values.
     *
     * @return Map<Tk, Tu> A `Map` containing key/value pairs after a user-specified
     *                 operation is applied.
     */
    public function map(callable $fn): Map
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
     * @param (callable(Tk, Tv): Tu) $fn The callback containing the operation to apply to the current
     *                                   `Map` keys and values.
     *
     * @return Map<Tk, Tu> A `Map` containing the values after a user-specified
     *                 operation on the current `Map`'s keys and values is applied.
     */
    public function mapWithKey(callable $fn): Map
    {
        return new Map(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `Map` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `Map` and the provided `iterable`.
     *
     * If the number of elements of the `Map` are not equal to the
     * number of elements in the `iterable`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param iterable<Tu> $iterable The `iterable` to use to combine with the
     *                               elements of this `Map`.
     *
     * @return Map<Tk, array{0: Tv, 1: Tu}> The `Map` that combines the values of the current
     *                 `Map` with the provided `iterable`.
     *
     * @psalm-mutation-free
     */
    public function zip(iterable $iterable): Map
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        $array = Vec\values($iterable);

        /** @var array<Tk, array{0: Tv, 1: Tu}> $elements */
        $elements = [];

        foreach ($this->elements as $k => $v) {
            /**
             * @psalm-suppress ImpureFunctionCall - conditionally pure
             */
            $u = Iter\first($array);
            if (null === $u) {
                break;
            }

            /**
             * @psalm-suppress ImpureFunctionCall - conditionally pure
             *
             * @var iterable<int, Tu> $array
             */
            $array = Dict\drop($array, 1);

            $elements[$k] = [$v, $u];
        }

        return new Map($elements);
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
     * @param int $n The last element that will be included in the returned
     *               `Map`.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     *
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                 `Map` up to `n` elements.
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
     * @param (callable(Tv): bool) $fn The callback that is used to determine the stopping
     *                                 condition.
     *
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                 `Map` up until the callback returns `false`.
     */
    public function takeWhile(callable $fn): Map
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
     * @param int $n The last element to be skipped; the $n+1 element will be the
     *               first one in the returned `Map`.
     *
     * @throws Psl\Exception\InvariantViolationException If $n is negative.
     *
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                 `Map` containing values after the specified `n`-th element.
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
     * @param (callable(Tv): bool) $fn The callback used to determine the starting element for the
     *                                 returned `Map`.
     *
     * @return Map<Tk, Tv> A `Map` that is a proper subset of the current
     *                 `Map` starting after the callback returns `true`.
     */
    public function dropWhile(callable $fn): Map
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
     * @param int $start The starting key of this Vector to begin the returned
     *                   `Map`.
     * @param null|int $length The length of the returned `Map`
     *
     * @throws Psl\Exception\InvariantViolationException If $start or $length are negative.
     *
     * @return Map<Tk, Tv>  A `Map` that is a proper subset of the current
     *                 `Map` starting at `$start` up to but not including the element `$start + $length`.
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
     * @param int $size The size of each chunk.
     *
     * @return Vector<Map<Tk, Tv>> A `Vector` containing the original `Map` split into
     *                        chunks of the given size.
     *
     * @psalm-mutation-free
     */
    public function chunk(int $size): Vector
    {
        /** @var Vector<array{0: Tv, 1: Tk}> $entries_vectors */
        $entries_vectors = $this->zip($this->keys())->values();

        /** @psalm-suppress ImpureMethodCall */
        return $entries_vectors
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
