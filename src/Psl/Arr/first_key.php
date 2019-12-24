<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Get the first key of an array, if the array is empty, null will be returned.
 *
 * @psalm-template Tk as array-key
 *
 * @psalm-param array<Tk, mixed> $array
 *
 * @psalm-return Tk|null
 *
 * @psalm-ignore-nullable-return
 * @psalm-pure
 */
function first_key(array $array)
{
    return \array_key_first($array);
}
