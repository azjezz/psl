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
 * @return TypeInterface<Collection\VectorInterface<T>>
 */
function vector(TypeInterface $valueType): TypeInterface
{
    return new Internal\VectorType($valueType);
}
