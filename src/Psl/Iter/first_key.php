<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Arr;

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
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return null|Tk
 */
function first_key(iterable $iterable)
{
    /** @psalm-var null|Tk */
    return Arr\first_key(to_array_with_keys($iterable));
}
