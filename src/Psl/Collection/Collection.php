<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * Collection is the primary collection interface for mutable collections.
 *
 * Assuming you want the ability to clear out your collection, you would implement this (or a child of this)
 * interface. Otherwise, you can implement OutputCollection only.
 * If your collection to be immutable, implement ConstCollection only instead.
 *
 * @psalm-template Tv
 *
 * @template-extends ConstCollection<Tv>
 * @template-extends OutputCollection<Tv>
 */
interface Collection extends ConstCollection, OutputCollection
{
    /**
     * Add a value to the collection and return the collection itself.
     *
     * @psalm-param Tv $value
     *
     * @psalm-return Collection<Tv>
     */
    public function add($value): Collection;

    /**
     * For every element in the provided iterable, append a value into the current collection.
     *
     * @psalm-param iterable<Tv> $values
     *
     * @psalm-return Collection<Tv>
     */
    public function addAll(iterable $values): Collection;

    /**
     * Removes all items from the collection.
     *
     * @psalm-return Collection<Tv>
     */
    public function clear(): Collection;
}
