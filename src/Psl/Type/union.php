<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;

/**
 * @template T
 *
 * @param TypeInterface<T> $first
 * @param TypeInterface<T> $second
 * @param TypeInterface<T> ...$rest
 *
 * @throws Psl\Exception\InvariantViolationException If $first, $second or one of $rest is optional.
 *
 * @return TypeInterface<T>
 */
function union(
    TypeInterface $first,
    TypeInterface $second,
    TypeInterface ...$rest
): TypeInterface {
    $accumulated_type = new Internal\UnionType($first, $second);

    foreach ($rest as $type) {
        $accumulated_type = new Internal\UnionType($accumulated_type, $type);
    }

    return $accumulated_type;
}
