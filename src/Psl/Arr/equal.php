<?php

declare(strict_types=1);

namespace Psl\Arr;

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
 * @psalm-pure
 */
function equal(array $array, array $array2): bool
{
    if ($array === $array2) {
        return true;
    }

    if (\count($array) !== \count($array2)) {
        return false;
    }

    foreach ($array as $key => $value) {
        if (!contains_key($array2, $key) || $array2[$key] !== $value) {
            return false;
        }
    }

    return true;
}
