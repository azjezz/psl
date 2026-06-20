<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for all keyed collections to enable access its values.
 *
 * @api
 */
interface IndexAccessInterface<Tk: string|int, Tv>
{
    /**
     * Returns the value at the specified key in the current collection.
     *
     * @throws Exception\OutOfBoundsException If $k is out-of-bounds.
     *
     * @psalm-mutation-free
     */
    public function at(Tk $k): Tv;

    /**
     * Determines if the specified key is in the current collection.
     *
     * @psalm-mutation-free
     */
    public function contains(Tk $k): bool;

    /**
     * Alias of `contains`.
     *
     * @see contains() method
     *
     * @psalm-mutation-free
     */
    public function containsKey(Tk $k): bool;

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @psalm-mutation-free
     */
    public function get(Tk $k): Tv|null;
}
