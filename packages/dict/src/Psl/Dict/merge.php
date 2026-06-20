<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_replace;
use function is_array;

/**
 * Merges multiple iterables into a new dict.
 * In the case of duplicate keys, later values will overwrite the previous ones.
 *
 * @param iterable<Tk, Tv> $first
 * @param iterable<Tk, Tv> ...$rest
 *
 * @return array<Tk, Tv>
 *
 * @no-named-arguments
 *
 * @api
 */
function merge<Tk: string|int, Tv>(iterable $first, iterable ...$rest): array
{
    if (is_array($first)) {
        foreach ($rest as $iterable) {
            if (is_array($iterable)) {
                continue;
            }

            /** @var list<iterable<Tk, Tv>> $iterables */
            $iterables = [$first, ...$rest];

            return namespace\flatten::<Tk, Tv>($iterables);
        }

        /** @var array<array<Tk, Tv>> $rest */
        return array_replace($first, ...$rest);
    }

    /** @var list<iterable<Tk, Tv>> $iterables */
    $iterables = [$first, ...$rest];

    return namespace\flatten::<Tk, Tv>($iterables);
}
