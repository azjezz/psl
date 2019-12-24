<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new dict formed by merging the iterable elements of the
 * given iterable. In the case of duplicate keys, later values will overwrite
 * the previous ones.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<iterable<Tk, Tv>> $iterables
 *
 * @psalm-return array<Tk, Tv>
 */
function flatten(iterable $iterables): array
{
    $result = [];
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            $result[$key] = $value;
        }
    }

    return $result;
}
