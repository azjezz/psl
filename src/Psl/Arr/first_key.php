<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

use function array_key_first;

/**
 * Get the first key of an array, if the array is empty, null will be returned.
 *
 * @template Tk of array-key
 *
 * @param array<Tk, mixed> $array
 *
 * @return Tk|null
 *
 * @pure
 *
 * @deprecated use `Iter\first_key` instead.
 * @see Iter\first_key()
 */
function first_key(array $array)
{
    return array_key_first($array);
}
