<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface for mutable, keyed collections to enable setting and removing
 * keys.
 *
 * @template Tk
 * @template Tv
 *
 * @extends ConstIndexAccess<Tk, Tv>
 */
interface IndexAccess extends ConstIndexAccess
{
    /**
     * Stores a value into the current collection with the specified key,
     * overwriting the previous value associated with the key.
     *
     * If the key is not present, an exception is thrown. If you want to add
     * a value even if a key is not present, use `add()`.
     *
     * `$coll->set($k,$v)` is semantically equivalent to `$coll[$k] = $v`
     * (except that `set()` returns the current collection).
     *
     * It returns the current collection, meaning changes made to the current
     * collection will be reflected in the returned collection.
     *
     * @psalm-param Tk $k - The key to which we will set the value
     * @psalm-param Tv $v - The value to set
     *
     * @psalm-return IndexAccess<Tk, Tv> - Returns itself
     */
    public function set($k, $v): IndexAccess;

    /**
     * For every element in the provided `iterable`, stores a value into the
     * current collection associated with each key, overwriting the previous value
     * associated with the key.
     *
     * If a key is not present the current collection that is present in the
     * `iterable`, an exception is thrown. If you want to add a value even if a
     * key is not present, use `addAll()`.
     *
     * It the current collection, meaning changes made to the current collection
     * will be reflected in the returned collection.
     *
     * @psalm-param iterable<Tk, Tv> $iterable - The `iterable` with the new values to set
     *
     * @psalm-return IndexAccess<Tk, Tv> - Returns itself
     */
    public function setAll(iterable $iterable): IndexAccess;

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
     * @psalm-return IndexAccess<Tk, Tv> - Returns itself
     */
    public function removeKey($k): IndexAccess;
}
