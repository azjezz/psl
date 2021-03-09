<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Return all the keys of an array.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $arr
 *
 * @return list<Tk>
 *
 * @deprecated use `Vec\keys` instead.
 * @see Vec\keys()
 */
function keys(array $arr): array
{
    return Vec\keys($arr);
}
