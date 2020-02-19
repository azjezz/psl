<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * @return Generator
 *
 * @psalm-return Generator<int, mixed, mixed, void>
 */
function reverse(iterable $iterable): Generator
{
    $size = count($iterable);
    if (0 === $size) {
        return;
    }

    $values = to_array($iterable);
    for ($lo = 0, $hi = $size - 1; $lo < $hi; $lo++, $hi--) {
        yield $values[$hi];
    }

    for (; $lo >= 0; --$lo) {
        yield $values[$lo];
    }
}
