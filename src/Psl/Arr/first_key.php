<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

use function array_key_first;

/**
 * Get the first key of an array, if the array is empty, null will be returned.
 *
 * @psalm-template Tk of array-key
 *
 * @psalm-param array<Tk, mixed> $array
 *
 * @psalm-return Tk|null
 *
 * @psalm-pure
 *
 * @deprecated use `Iter\first_key` instead.
 *
 * @see Iter\first_key()
 */
function first_key(array $array)
{
    return array_key_first($array);
}
