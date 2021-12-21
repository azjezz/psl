<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for all keyed collections to enable access its values.
 *
 * @template Tk of array-key
 * @template Tv
 */
interface IndexAccessInterface
{
    /**
     * Returns the value at the specified key in the current collection.
     *
     * @param Tk $k
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @return Tv
     *
     * @psalm-mutation-free
     */
    public function at(string|int $k): mixed;

    /**
     * Determines if the specified key is in the current collection.
     *
     * @param Tk $k
     *
     * @psalm-mutation-free
     */
    public function contains(int|string $k): bool;

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @param Tk $k
     *
     * @return Tv|null
     *
     * @psalm-mutation-free
     */
    public function get(string|int $k): mixed;
}
