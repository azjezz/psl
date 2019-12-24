<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;
use Psl\Random;

/**
 * Retrieve a random value from a non-empty array.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $values
 *
 * @psalm-return Tv
 *
 * @psalm-suppress MissingReturnType
 */
function random(array $values)
{
    Psl\invariant(!Iter\is_empty($values), 'Expected non-empty-array');

    /** @psalm-var array<int, Tv> $shuffled */
    $shuffled = shuffle($values);
    $size = Iter\count($values);
    if (1 === $size) {
        /** @psalm-var Tv */
        return $shuffled[0];
    }

    /** @psalm-var Tv */
    return at($shuffled, Random\int(0, $size - 1));
}
