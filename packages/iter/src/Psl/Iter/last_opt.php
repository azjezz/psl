<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Option\Option;

/**
 * Returns the last element of an iterable wrapped in {@see Option::some},
 * if the iterable is empty, {@see Option::none} will be returned.
 *
 * @param iterable<Tv> $iterable
 *
 * @return Option<Tv>
 *
 * @api
 */
function last_opt<Tv>(iterable $iterable): Option<Tv>
{
    $last = Option::none();
    foreach ($iterable as $v) {
        $last = Option::some($v);
    }

    return $last;
}
