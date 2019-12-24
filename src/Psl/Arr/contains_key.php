<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns true if the given array contains the key.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 * @psalm-param Tk            $key
 *
 * @psalm-pure
 */
function contains_key(array $array, $key): bool
{
    return \array_key_exists($key, $array);
}
