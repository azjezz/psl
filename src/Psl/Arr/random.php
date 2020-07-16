<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;
use Psl\PseudoRandom;

/**
 * Retrieve a random value from a non-empty array.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $values
 *
 * @psalm-return Tv
 *
 * @throws Psl\Exception\InvariantViolationException If $values is empty.
 */
function random(array $values)
{
    Psl\invariant(!Iter\is_empty($values), 'Expected a non-empty-array.');

    /** @psalm-var list<Tv> $shuffled */
    $shuffled = namespace\shuffle($values);
    $size = Iter\count($values);
    if (1 === $size) {
        /** @psalm-var Tv */
        return $shuffled[0];
    }

    /** @psalm-suppress MissingThrowsDocblock */
    return at($shuffled, PseudoRandom\int(0, $size - 1));
}
