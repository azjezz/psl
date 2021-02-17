<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @template T
 *
 * @psalm-param TypeInterface<T> $value_type
 *
 * @psalm-return TypeInterface<Collection\MutableVectorInterface<T>>
 */
function mutable_vector(TypeInterface $value_type): TypeInterface
{
    return new Internal\MutableVectorType($value_type);
}
