<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template TFirst
 * @template TSecond
 * @template TRest
 *
 * @param TypeInterface<TFirst> $first
 * @param TypeInterface<TSecond> $second
 * @param TypeInterface<TRest> ...$rest
 *
 * @return TypeInterface<TFirst&TSecond&TRest>
 */
function intersection(TypeInterface $first, TypeInterface $second, TypeInterface ...$rest): TypeInterface
{
    $accumulatedType = new Internal\IntersectionType($first, $second);

    foreach ($rest as $type) {
        $accumulatedType = new Internal\IntersectionType($accumulatedType, $type);
    }

    /** @var TypeInterface<TFirst&TSecond&TRest> */
    return $accumulatedType;
}
