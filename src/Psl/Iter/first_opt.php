<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Option\Option;

/**
 * Returns the first element of an iterable wrapped in {@see Option::some},
 * if the iterable is empty, {@see Option::none} will be returned.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return Option<T>
 *
 * @mago-ignore best-practices/loop-does-not-iterate
 */
function first_opt(iterable $iterable): Option
{
    foreach ($iterable as $v) {
        return Option::some($v);
    }

    return Option::none();
}
