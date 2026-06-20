<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<TFirst> $first
 * @param TypeInterface<TSecond> $second
 * @param TypeInterface<TRest> ...$rest
 *
 * @return TypeInterface<TFirst&TSecond&TRest>
 *
 * @api
 */
function intersection<TFirst, TSecond, TRest>(TypeInterface<TFirst> $first, TypeInterface<TSecond> $second, TypeInterface<TRest> ...$rest): TypeInterface<TFirst&TSecond&TRest>
{
    $accumulatedType = new Internal\IntersectionType($first, $second);

    foreach ($rest as $type) {
        $accumulatedType = new Internal\IntersectionType($accumulatedType, $type);
    }

    /** @var TypeInterface<TFirst&TSecond&TRest> */
    return $accumulatedType;
}
