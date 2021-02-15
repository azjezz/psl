<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns whether the two given arrays have the same entries, using strict
 * equality. To guarantee equality of order as well as contents, use `===`.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 * @psalm-param array<Tk, Tv> $array2
 *
 * @deprecated use `Dict\equal` instead.
 *
 * @see Dict\equal()
 */
function equal(array $array, array $array2): bool
{
    return Dict\equal($array, $array2);
}
