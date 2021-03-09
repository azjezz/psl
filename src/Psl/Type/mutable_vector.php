<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @template T
 *
 * @param TypeInterface<T> $value_type
 *
 * @return TypeInterface<Collection\MutableVectorInterface<T>>
 */
function mutable_vector(TypeInterface $value_type): TypeInterface
{
    return new Internal\MutableVectorType($value_type);
}
