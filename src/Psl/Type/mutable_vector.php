<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @template T
 *
 * @param TypeInterface<T> $valueType
 *
 * @return TypeInterface<Collection\MutableVectorInterface<T>>
 */
function mutable_vector(TypeInterface $valueType): TypeInterface
{
    return new Internal\MutableVectorType($valueType);
}
