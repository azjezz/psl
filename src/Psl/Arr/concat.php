<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array formed by concatenating the given iterables together.
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T>         $first
 * @psalm-param list<iterable<T>>   $rest
 *
 * @psalm-return list<T>
 */
function concat(iterable $first, iterable ...$rest): array
{
    /** @psalm-var list<T> $result */
    $result = Iter\to_array($first);
    foreach ($rest as $iterable) {
        foreach ($iterable as $value) {
            $result[] = $value;
        }
    }

    return $result;
}
