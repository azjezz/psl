<?php

declare(strict_types=1);

namespace Psl\Iter;

function reverse(iterable $iterable): iterable
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
