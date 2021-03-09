<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Gets the first key of an iterable.
 *
 * Example:
 *
 *      Iter\first_key(
 *          Iter\reindex(Iter\range(5, 10), fn($v) => Str\chr(60 + $v))
 *      )
 *      => Str('A')
 *
 *      Iter\first_key(['a' ,'b'])
 *      => Int(0)
 *
 *      Iter\first_key([])
 *      => Null
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Tk|null
 */
function first_key(iterable $iterable)
{
    foreach ($iterable as $k => $_) {
        return $k;
    }

    return null;
}
