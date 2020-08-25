<?php

declare(strict_types=1);

namespace Psl\Arr;

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
 * @psalm-pure
 */
function keys(array $arr): array
{
    return \array_keys($arr);
}
