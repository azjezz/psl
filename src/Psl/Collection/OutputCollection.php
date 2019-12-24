<?php

declare(strict_types=1);

namespace Psl\Collection;

/**
 * The interface implemented for a mutable collection type so that values can be added to it.
 *
 * @psalm-template Tv
 */
interface OutputCollection
{
    /**
     * Add a value to the collection and return the collection itself.
     *
     * @psalm-param Tv $value
     *
     * @psalm-return OutputCollection<Tv>
     */
    public function add($value): OutputCollection;

    /**
     * For every element in the provided iterable, append a value into the current collection.
     *
     * @psalm-param iterable<Tv> $values
     *
     * @psalm-return OutputCollection<Tv>
     */
    public function addAll(iterable $values): OutputCollection;
}
