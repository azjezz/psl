<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\PseudoRandom;
use Psl\Vec;

/**
 * Retrieve a random value from a non-empty iterable.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @throws Exception\InvalidArgumentException If $iterable is empty.
 *
 * @return T
 */
function random(iterable $iterable)
{
    // We convert the iterable to an array before checking if it is empty,
    // this helps us avoids an issue when the iterable is a generator where
    // would exhaust it when calling `count`
    $values = Vec\values($iterable);
    if ([] === $values) {
        throw new Exception\InvalidArgumentException('Expected a non-empty iterable.');
    }
    
    $size = namespace\count($values);

    if (1 === $size) {
        /** @var T */
        return $values[0];
    }

    /** @psalm-suppress MissingThrowsDocblock */
    return $values[PseudoRandom\int(0, $size - 1)];
}
