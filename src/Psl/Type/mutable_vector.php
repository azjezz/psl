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
 * @return TypeInterface<Collection\MutableVectorInterface<T>>
 */
function mutable_vector(TypeInterface $value_type): TypeInterface
{
    return new Internal\MutableVectorType($value_type);
}
