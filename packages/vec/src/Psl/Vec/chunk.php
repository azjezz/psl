<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_chunk;
use function is_array;

/**
 * Returns a list containing the original list split into chunks of the given
 * size.
 *
 * If the original list doesn't divide evenly, the final chunk will be
 * smaller.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 * @param positive-int $size
 *
 * @return list<list<T>>
 */
function chunk(iterable $iterable, int $size): array
{
    if (is_array($iterable)) {
        return array_chunk($iterable, $size);
    }

    $result = [];
    $chunk = [];
    $ii = 0;
    foreach ($iterable as $value) {
        $chunk[] = $value;
        if ((++$ii % $size) === 0) {
            $result[] = $chunk;
            $chunk = [];
        }
    }

    if ($chunk !== []) {
        $result[] = $chunk;
    }

    return $result;
}
