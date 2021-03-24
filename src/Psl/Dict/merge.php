<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Merges multiple iterables into a new dict.
 * In the case of duplicate keys, later values will overwrite the previous ones.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, Tv> ...$rest
 *
 * @return array<Tk, Tv>
 *
 * @no-named-arguments
 */
function merge(iterable $first, iterable ...$rest): array
{
    /** @var list<iterable<Tk, Tv>> $iterables */
    $iterables = [$first, ...$rest];

    return flatten($iterables);
}
