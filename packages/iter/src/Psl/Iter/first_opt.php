<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Option\Option;

/**
 * Returns the first element of an iterable wrapped in {@see Option::some},
 * if the iterable is empty, {@see Option::none} will be returned.
 *
 * @param iterable<T> $iterable
 *
 * @mago-expect lint:loop-does-not-iterate
 *
 * @api
 */
function first_opt<T>(iterable $iterable): Option<T>
{
    foreach ($iterable as $v) {
        return Option::<T>::some($v);
    }

    return Option::none();
}
