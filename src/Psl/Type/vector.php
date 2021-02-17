<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @template T
 *
 * @psalm-param TypeInterface<T> $value_type
 *
 * @psalm-return TypeInterface<Collection\VectorInterface<T>>
 */
function vector(TypeInterface $value_type): TypeInterface
{
    return new Internal\VectorType($value_type);
}
