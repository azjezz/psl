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
     * @return Tv
     */
    public function at($k);

    /**
     * Determines if the specified key is in the current collection.
     *
     * @param Tk $k
     */
    public function contains($k): bool;

    /**
     * Returns the value at the specified key in the current collection.
     *
     * @param Tk $k
     *
     * @return Tv|null
     */
    public function get($k);
}
