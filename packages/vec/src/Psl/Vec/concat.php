<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_merge;
use function array_values;
use function is_array;

/**
 * Returns a new list formed by concatenating the given lists together.
 *
 * @template T
 *
 * @param iterable<T> $first
 * @param iterable<T> ...$rest
 *
 * @return list<T>
 */
function concat(iterable $first, iterable ...$rest): array
{
    if (is_array($first) && $rest === []) {
        return array_values($first);
    }

    $first = namespace\values($first);
    foreach ($rest as $arr) {
        if (is_array($arr)) {
            $first = array_merge($first, array_values($arr));
        } else {
            foreach ($arr as $value) {
                $first[] = $value;
            }
        }
    }

    return $first;
}
