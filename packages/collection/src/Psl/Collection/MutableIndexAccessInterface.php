<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for mutable, keyed collections to enable setting and removing
 * keys.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @extends IndexAccessInterface<Tk, Tv>
 */
interface MutableIndexAccessInterface extends IndexAccessInterface
{
    /**
     * Removes the specified key (and associated value) from the current
     * collection.
     *
     * If the key is not in the current collection, the current collection is
     * unchanged.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @param Tk $k The key to remove
     *
     * @return MutableIndexAccessInterface<Tk, Tv> Returns itself
     */
    public function remove(int|string $k): MutableIndexAccessInterface;
}
