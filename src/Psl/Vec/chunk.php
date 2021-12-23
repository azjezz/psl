<?php

declare(strict_types=1);

namespace Psl\Vec;

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
    $result = [];
    $ii = 0;
    $chunk_number = -1;
    foreach ($iterable as $value) {
        if ($ii % $size === 0) {
            $result[] = [];
            $chunk_number++;
        }

        $result[$chunk_number][] = $value;
        $ii++;
    }

    return values($result);
}
