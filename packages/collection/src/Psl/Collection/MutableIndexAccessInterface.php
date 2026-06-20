<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for mutable, keyed collections to enable setting and removing
 * keys.
 *
 * @api
 */
interface MutableIndexAccessInterface<Tk: string|int, Tv> extends IndexAccessInterface<Tk, Tv>
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
     */
    public function remove(Tk $k): MutableIndexAccessInterface<Tk, Tv>;
}
