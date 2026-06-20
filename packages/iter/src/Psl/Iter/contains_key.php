<?php

declare(strict_types=1);

namespace Psl\Iter;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;

/**
 * Returns true if the given iterable contains the key.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @api
 */
function contains_key<Tk, Tv>(iterable $iterable, Tk $key): bool
{
    if (is_array($iterable) && (is_int($key) || is_string($key))) {
        return array_key_exists($key, $iterable);
    }

    foreach ($iterable as $k => $_) {
        if ($key === $k) {
            return true;
        }
    }

    return false;
}
