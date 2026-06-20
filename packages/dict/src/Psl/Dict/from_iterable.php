<?php

declare(strict_types=1);

namespace Psl\Dict;

use function is_array;

/**
 * Convert the given iterable to a dict.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function from_iterable<Tk : int|string, Tv>(iterable $iterable): array
{
    if (is_array($iterable)) {
        return $iterable;
    }

    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$key] = $value;
    }

    return $result;
}
