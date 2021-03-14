<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;

/**
 * @template T
 *
 * @param TypeInterface<T> $value_type
 *
 * @throws Psl\Exception\InvariantViolationException If $value_type is optional.
 *
 * @return TypeInterface<list<T>>
 */
function vec(TypeInterface $value_type): TypeInterface
{
    return new Internal\VecType($value_type);
}
