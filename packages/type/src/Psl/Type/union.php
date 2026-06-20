<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function union<T>(TypeInterface<T> $first, TypeInterface<T> $second, TypeInterface<T> ...$rest): TypeInterface<T>
{
    $accumulatedType = new Internal\UnionType::<T, T>($first, $second);

    foreach ($rest as $type) {
        $accumulatedType = new Internal\UnionType::<T, T>($accumulatedType, $type);
    }

    return $accumulatedType;
}
