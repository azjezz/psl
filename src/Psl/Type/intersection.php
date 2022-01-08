<?php

declare(strict_types=1);

namespace Psl\Type;

/**
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
function intersection(
    TypeInterface $first,
    TypeInterface $second,
    TypeInterface ...$rest
): TypeInterface {
    $accumulated_type = new Internal\IntersectionType($first, $second);

    foreach ($rest as $type) {
        $accumulated_type = new Internal\IntersectionType($accumulated_type, $type);
    }

    /** @var TypeInterface<TFirst&TSecond&TRest> */
    return $accumulated_type;
}
