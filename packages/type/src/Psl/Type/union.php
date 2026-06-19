<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<T> $first
 * @param TypeInterface<T> $second
 * @param TypeInterface<T> ...$rest
 *
 * @return TypeInterface<T>
 *
 * @api
 */
function union<T = mixed>(TypeInterface<T> $first, TypeInterface<T> $second, TypeInterface<T> ...$rest): TypeInterface<T>
{
    $accumulatedType = new Internal\UnionType($first, $second);

    foreach ($rest as $type) {
        $accumulatedType = new Internal\UnionType($accumulatedType, $type);
    }

    return $accumulatedType;
}
