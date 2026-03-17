<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Option\Option;

/**
 * Returns the last element of an iterable wrapped in {@see Option::some},
 * if the iterable is empty, {@see Option::none} will be returned.
 *
 * @template Tv
 *
 * @param iterable<Tv> $iterable
 *
 * @return Option<Tv>
 */
function last_opt(iterable $iterable): Option
{
    $last = Option::none();
    foreach ($iterable as $v) {
        $last = Option::some($v);
    }

    return $last;
}
