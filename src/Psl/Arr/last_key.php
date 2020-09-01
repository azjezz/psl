<?php

declare(strict_types=1);

namespace Psl\Arr;

use function array_key_last;

/**
 * Get the last key of an array, if the array is empty, null will be returned.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return Tk|null
 *
 * @psalm-pure
 */
function last_key(array $array)
{
    /** @psalm-var Tk|null */
    return array_key_last($array);
}
