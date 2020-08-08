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
     * Stores a value into the current collection with the specified key,
     * overwriting the previous value associated with the key.
     *
     * It returns the current collection, meaning changes made to the current
     * collection will be reflected in the returned collection.
     *
     * @psalm-param Tk $k - The key to which we will set the value
     * @psalm-param Tv $v - The value to set
     *
     * @psalm-return MutableIndexAccessInterface<Tk, Tv> - Returns itself
     */
    public function set($k, $v): MutableIndexAccessInterface;

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current collection associated with each key, overwriting the previous value
     * associated with the key.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return MutableIndexAccessInterface<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): MutableIndexAccessInterface;

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
     * @psalm-param Tk $k - The key to remove
     *
     * @psalm-return MutableIndexAccessInterface<Tk, Tv> - Returns itself
     */
    public function remove($k): MutableIndexAccessInterface;
}
