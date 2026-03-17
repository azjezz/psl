<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_key_exists;
use function count;

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

    if (count($first) !== count($second)) {
        return false;
    }

    foreach ($first as $k => $v) {
        if (!array_key_exists($k, $second) || $second[$k] !== $v) {
            return false;
        }
    }

    return true;
}
