<?php

declare(strict_types=1);

namespace Psl\Collection;

use Psl\Arr;
use Psl\Iter;

/**
 * @template   Tk of array-key
 * @template   Tv
 *
 * @implements IMap<Tk, Tv>
 */
abstract class AbstractAccessibleCollection implements IAccessibleCollection
{
    /**
     * @var array<Tk, Tv> $elements
     */
    protected array $elements;

    /**
     * Returns the first value in the current collection.
     *
     * @psalm-return null|Tv - The first value in the current collection, or `null` if the
     *           current collection is empty.
     */
    final public function first()
    {
        return Iter\first($this->elements);
    }

    /**
     * Returns the first key in the current collection.
     *
     * @psalm-return null|Tk - The first key in the current collection, or `null` if the
     *                  current collection is empty
     */
    public function firstKey()
    {
        return Iter\first_key($this->elements);
    }

    /**
     * Returns the last value in the current collection.
     *
     * @psalm-return null|Tv - The last value in the current collection, or `null` if the
     *           current collection is empty.
     */
    final public function last()
    {
        return Iter\last($this->elements);
    }

    /**
     * Returns the last key in the current collection.
     *
     * @psalm-return null|Tk - The last key in the current collection, or `null` if the
     *                  current collection is empty
     */
    public function lastKey()
    {
        return Iter\last_key($this->elements);
    }

    /**
     * Returns the index of the first element that matches the search value.
     *
     * If no element matches the search value, this function returns null.
     *
     * @psalm-param  Tv $search_value - The value that will be search for in the current
     *                        collection.
     *
     * @psalm-return null|Tk - The key (index) where that value is found; null if it is not found
     */
    public function linearSearch($search_value)
    {
        foreach ($this->elements as $key => $element) {
            if ($search_value === $element) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Retrieve an external iterator
     *
     * @psalm-return Iter\Iterator<Tk, Tv>
     */
    final public function getIterator(): Iter\Iterator
    {
        return new Iter\Iterator($this->elements);
    }

    /**
     * Is the map empty?
     */
    final public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    /**
     * Get the number of items in the current map.
     */
    final public function count(): int
    {
        return Iter\count($this->elements);
    }

    /**
     * Get an array copy of the current map.
     *
     * @psalm-return array<Tk, Tv>
     */
    final public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv
     */
    final public function at($k)
    {
        return Arr\at($this->elements, $k);
    }

    /**
     * Determines if the specified key is in the current map.
     *
     * @psalm-param Tk $k
     */
    final public function contains($k): bool
    {
        return Iter\contains_key($this->elements, $k);
    }

    /**
     * Returns the value at the specified key in the current map.
     *
     * @psalm-param  Tk $k
     *
     * @psalm-return Tv|null
     */
    final public function get($k)
    {
        return Arr\idx($this->elements, $k);
    }
}
