<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The base interface implemented for a collection type so that base information such
 * as count and its items are available.
 *
 * Every concrete class indirectly implements this interface.
 *
 * @template Tv
 */
interface ConstCollection extends \Countable
{
    /**
     * Get access to the items in the collection.
     *
     * @psalm-return iterable<int, Tv>
     */
    public function items(): iterable;

    /**
     * Is the collection empty?
     */
    public function isEmpty(): bool;

    /**
     * Get the number of items in the collection.
     */
    public function count(): int;

    /**
     * Get an array copy of the the collection.
     *
     * @psalm-return array<array-key, Tv>
     */
    public function toArray(): array;
}
