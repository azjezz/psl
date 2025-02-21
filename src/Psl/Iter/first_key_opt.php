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
 * @template Tk
 * @template Tv
 *
 * @return Option<Tk>
 *
 * @mago-ignore best-practices/loop-does-not-iterate
 */
function first_key_opt(iterable $iterable): Option
{
    foreach ($iterable as $k => $_) {
        return Option::some($k);
    }

    return Option::none();
}
