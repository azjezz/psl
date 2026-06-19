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
 * @return Option<Tk>
 *
 * @mago-expect lint:loop-does-not-iterate
 *
 * @api
 */
function first_key_opt<Tk = mixed, Tv = mixed>(iterable $iterable): Option<Tk>
{
    foreach ($iterable as $k => $_) {
        return Option::some($k);
    }

    return Option::none();
}
