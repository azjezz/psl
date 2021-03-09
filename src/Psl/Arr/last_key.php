<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

use function array_key_last;

/**
 * Get the last key of an array, if the array is empty, null will be returned.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 *
 * @return Tk|null
 *
 * @pure
 *
 * @deprecated use `Iter\last_key` instead.
 * @see Iter\last_key()
 */
function last_key(array $array)
{
    /** @var Tk|null */
    return array_key_last($array);
}
