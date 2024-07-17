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
use function count;

/**
 * @template T
 *
 * @implements VectorInterface<T>
 */
final readonly class Vector implements VectorInterface
{
    /**
     * @var list<T> $elements
     */
    private array $elements;

    /**
     * @param array<array-key, T> $elements
     *
     * @psalm-mutation-free
     */
    public function __construct(array $elements)
    {
        $list = [];
        foreach ($elements as $element) {
            $list[] = $element;
        }

        $this->elements = $list;
    }

    /**
     * Creates and returns a default instance of {@see Vector}.
     *
     * @return static A default instance of {@see Vector}.
     *
     * @pure
     */
    public static function default(): static
    {
        return new self([]);
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
    public function first(): mixed
    {
        return $this->elements[0] ?? null;
    }

    /**
     * Returns the last value in the current `Vector`.
     *
     * @return T|null The last value in the current `Vector`, or `null` if the
     *                current `Vector` is empty.
     *
     * @psalm-mutation-free
     */
    public function last(): mixed
    {
        $key = array_key_last($this->elements);
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
     * Is the `Vector` empty?
     *
     * @psalm-mutation-free
     */
    public function isEmpty(): bool
    {
        return [] === $this->elements;
    }

    /**
     * Get the number of elements in the current `Vector`.
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
     * Get an array copy of the current `Vector`.
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
     * Get an array copy of the current `Vector`.
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
     * Returns the value at the specified key in the current `Vector`.
     *
     * @param int<0, max> $k
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function at(int|string $k): mixed
    {
        if (!array_key_exists($k, $this->elements)) {
            throw Exception\OutOfBoundsException::for($k);
        }

        return $this->elements[$k];
    }

    /**
     * Determines if the specified key is in the current `Vector`.
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
     * Returns the value at the specified key in the current `Vector`.
     *
     * @param int<0, max> $k
     *
     * @return T|null
     *
     * @psalm-mutation-free
     */
    public function get(int|string $k): mixed
    {
        return $this->elements[$k] ?? null;
    }

    /**
     * Returns the first key in the current `Vector`.
     *
     * @return int<0, max>|null The first key in the current `Vector`, or `null` if the
     *                          current `Vector` is empty.
     *
     * @psalm-mutation-free
     */
    public function firstKey(): ?int
    {
        return [] === $this->elements ? null : 0;
    }

    /**
     * Returns the last key in the current `Vector`.
     *
     * @return int<0, max>|null The last key in the current `Vector`, or `null` if the
     *                          current `Vector` is empty.
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
     * Returns a `Vector` containing the values of the current
     * `Vector`.
     *
     * @return Vector<T>
     *
     * @psalm-mutation-free
     */
    public function values(): Vector
    {
        return self::fromArray($this->elements);
    }

    /**
     * Returns a `Vector` containing the keys of the current `Vector`.
     *
     * @return Vector<int<0, max>>
     *
     * @psalm-mutation-free
     */
    public function keys(): Vector
    {
        return self::fromArray(array_keys($this->elements));
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
     * @param (Closure(T): bool) $fn The callback containing the condition to apply to the current
     *                               `Vector` values.
     *
     * @return Vector<T> a Vector containing the values after a user-specified condition
     *                   is applied.
     */
    public function filter(Closure $fn): Vector
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
     * @param (Closure(int<0, max>, T): bool) $fn The callback containing the condition to apply to the current
     *                                            `Vector` keys and values.
     *
     * @return Vector<T> a `Vector` containing the values after a user-specified
     *                   condition is applied to the keys and values of the current `Vector`.
     */
    public function filterWithKey(Closure $fn): Vector
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
     * @param (Closure(T): Tu) $fn The callback containing the operation to apply to the current
     *                             `Vector` values.
     *
     * @return Vector<Tu> a `Vector` containing key/value pairs after a user-specified
     *                    operation is applied.
     */
    public function map(Closure $fn): Vector
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
     * @param (Closure(int<0, max>, T): Tu) $fn The callback containing the operation to apply to the current
     *                                          `Vector` keys and values.
     *
     * @return Vector<Tu> a `Vector` containing the values after a user-specified
     *                    operation on the current `Vector`'s keys and values is applied.
     */
    public function mapWithKey(Closure $fn): Vector
    {
        return new Vector(Dict\map_with_key($this->elements, $fn));
    }

    /**
     * Returns a `Vector` where each element is a `array{0: Tv, 1: Tu}` that combines the
     * element of the current `VectorInterface` and the provided elements array.
     *
     * If the number of elements of the `Vector` are not equal to the
     * number of elements in `$elements`, then only the combined elements
     * up to and including the final element of the one with the least number of
     * elements is included.
     *
     * @template Tu
     *
     * @param array<array-key, Tu> $elements The elements to use to combine with the elements of this `VectorInterface`.
     *
     * @return Vector<array{0: T, 1: Tu}> The `Vector` that combines the values of the current
     *                                    `Vector` with the provided elements.
     *
     * @psalm-mutation-free
     */
    public function zip(array $elements): Vector
    {
        /** @psalm-suppress ImpureFunctionCall - conditionally pure */
        return Vector::fromArray(Vec\zip($this->elements, $elements));
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
     * @param int<0, max> $n The last element that will be included in the returned
     *                       `Vector`.
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
     * @param (Closure(T): bool) $fn The callback that is used to determine the stopping
     *                               condition.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` up until the callback returns `false`.
     */
    public function takeWhile(Closure $fn): Vector
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
     * @param int<0, max> $n The last element to be skipped; the $n+1 element will be the
     *                       first one in the returned `Vector`.
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
     * @param (Closure(T): bool) $fn The callback used to determine the starting element for the
     *                               returned `Vector`.
     *
     * @return Vector<T> A `Vector` that is a proper subset of the current
     *                   `Vector` starting after the callback returns `true`.
     */
    public function dropWhile(Closure $fn): Vector
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
     * @param int<0, max> $start The starting key of this Vector to begin the returned
     *                           `Vector`.
     * @param null|int<0, max> $length The length of the returned `Vector`
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

    /**
     * Returns a `Vector` containing the original `Vector` split into
     * chunks of the given size.
     *
     * If the original `Vector` doesn't divide evenly, the final chunk will be
     * smaller.
     *
     * @param positive-int $size The size of each chunk.
     *
     * @return Vector<Vector<T>> A `Vector` containing the original `Vector` split
     *                           into chunks of the given size.
     *
     * @psalm-mutation-free
     *
     * @psalm-suppress LessSpecificImplementedReturnType - I don't see how this one is less specific than its inherited.
     */
    public function chunk(int $size): Vector
    {
        /**
         * @psalm-suppress MissingThrowsDocblock
         * @psalm-suppress ImpureFunctionCall
         */
        return static::fromArray(Vec\map(
            Vec\chunk($this->toArray(), $size),
            /**
             * @param list<T> $chunk
             *
             * @return Vector<T>
             */
            static fn(array $chunk) => static::fromArray($chunk)
        ));
    }
}
