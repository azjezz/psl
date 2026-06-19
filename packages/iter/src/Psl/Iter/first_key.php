<?php

declare(strict_types=1);

namespace Psl\Iter;

use function array_key_first;
use function is_array;

/**
 * Returns the first key of an iterable, if the iterable is empty, null will be returned.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return ($iterable is non-empty-array|non-empty-list ? Tk : Tk|null)
 *
 * @mago-expect lint:loop-does-not-iterate
 *
 * @api
 */
function first_key<Tk = mixed, Tv = mixed>(iterable $iterable): mixed
{
    if (is_array($iterable)) {
        return array_key_first($iterable);
    }

    foreach ($iterable as $k => $_) {
        return $k;
    }

    return null;
}
