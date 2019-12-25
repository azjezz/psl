<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Arr;

/**
 * @psalm-template Tk1 as array-key
 * @psalm-template Tk2 as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk1, Tv>       $first
 * @psalm-param iterable<Tk2, mixed>    $second
 * @psalm-param iterable<Tk2, mixed>    ...$rest
 *
 * @psalm-return iterable<Tk1, Tv>
 */
function diff_by_key(iterable $first, iterable $second, iterable ...$rest): iterable
{
    if (is_empty($first)) {
        return [];
    }

    if (is_empty($second) && is_empty($rest)) {
        return $first;
    }

    $union = Arr\merge($second, ...$rest);
    /** @psalm-var iterable<Tk1, Tv> $result */
    $result = filter_keys(
        $first,
        /** @psalm-param Tk1 $key */
        fn ($key) => !contains_key($union, $key)
    );

    return $result;
}
