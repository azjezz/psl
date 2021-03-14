<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;

/**
 * @template T
 *
 * @param TypeInterface<T> $inner_type
 *
 * @throws Psl\Exception\InvariantViolationException If $inner_type is optional.
 *
 * @return TypeInterface<T|null>
 */
function nullable(TypeInterface $inner_type): TypeInterface
{
    return new Internal\NullableType($inner_type);
}
