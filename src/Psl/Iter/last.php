<?php

declare(strict_types=1);

namespace Psl\Iter;

use function array_key_last;
use function is_array;

/**
 * Returns the last element of an iterable, if the iterable is empty, null will be returned.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return ($iterable is non-empty-array|non-empty-list ? T : T|null)
 */
function last(iterable $iterable): mixed
{
    if (is_array($iterable)) {
        if ([] === $iterable) {
            return null;
        }

        return $iterable[array_key_last($iterable)];
    }

    $last = null;
    foreach ($iterable as $v) {
        $last = $v;
    }

    return $last;
}
