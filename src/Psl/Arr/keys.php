<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Return all the keys of an array.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $arr
 *
 * @psalm-return list<Tk>
 *
 * @deprecated use `Vec\keys` instead.
 *
 * @see Vec\keys()
 */
function keys(array $arr): array
{
    return Vec\keys($arr);
}
