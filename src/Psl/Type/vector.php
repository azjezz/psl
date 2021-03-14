<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;
use Psl\Collection;

/**
 * @template T
 *
 * @param TypeInterface<T> $value_type
 *
 * @throws Psl\Exception\InvariantViolationException If $value_type is optional.
 *
 * @return TypeInterface<Collection\VectorInterface<T>>
 */
function vector(TypeInterface $value_type): TypeInterface
{
    return new Internal\VectorType($value_type);
}
