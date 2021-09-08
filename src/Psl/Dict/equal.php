<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Iter;

/**
 * Returns whether the two given dict have the same entries, using strict
 * equality. To guarantee equality of order as well as contents, use `===`.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $first
 * @param array<Tk, Tv> $second
 */
function equal(array $first, array $second): bool
{
    if ($first === $second) {
        return true;
    }

    if (Iter\count($first) !== Iter\count($second)) {
        return false;
    }

    foreach ($first as $k => $v) {
        if (!Iter\contains_key($second, $k) || $second[$k] !== $v) {
            return false;
        }
    }

    return true;
}
