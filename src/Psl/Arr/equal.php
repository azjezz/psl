<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns whether the two given arrays have the same entries, using strict
 * equality. To guarantee equality of order as well as contents, use `===`.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param array<Tk, Tv> $array2
 *
 * @deprecated use `Dict\equal` instead.
 * @see Dict\equal()
 */
function equal(array $array, array $array2): bool
{
    return Dict\equal($array, $array2);
}
