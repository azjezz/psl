<?php

declare(strict_types=1);

namespace Psl\Vec;

use function shuffle as php_shuffle;

/**
 * Shuffle the given iterable items.
 *
 * Example:
 *
 *      Vec\shuffle([1, 2, 3])
 *      => Vec(2, 3, 1)
 *
 *      Vec\shuffle(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Vec(2, 3, 1)
 *
 * @param iterable<T> $iterable
 *
 * @return list<T> the shuffled items as a list.
 *
 * @api
 */
function shuffle<T>(iterable $iterable): array
{
    $array = namespace\values::<mixed>($iterable);

    php_shuffle($array);

    return $array;
}
