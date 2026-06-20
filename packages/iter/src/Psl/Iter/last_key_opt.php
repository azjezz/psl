<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Option\Option;

/**
 * Returns the first key of an iterable wrapped in {@see Option::some},
 * if the iterable is empty, {@see Option::none} will be returned.
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @api
 */
function last_key_opt<Tk, Tv>(iterable $iterable): Option<Tk>
{
    $last = Option::none();
    foreach ($iterable as $k => $_) {
        $last = Option::<Tk>::some($k);
    }

    return $last;
}
