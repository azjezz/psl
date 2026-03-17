<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template T
 *
 * @param TypeInterface<T> $first
 * @param TypeInterface<T> $second
 * @param TypeInterface<T> ...$rest
 *
 * @return TypeInterface<T>
 */
function union(TypeInterface $first, TypeInterface $second, TypeInterface ...$rest): TypeInterface
{
    $accumulatedType = new Internal\UnionType($first, $second);

    foreach ($rest as $type) {
        $accumulatedType = new Internal\UnionType($accumulatedType, $type);
    }

    return $accumulatedType;
}
