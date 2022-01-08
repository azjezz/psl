<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @param TypeInterface<T> $inner_type
 *
 * @return TypeInterface<T|null>
 */
function nullable(TypeInterface $inner_type): TypeInterface
{
    return new Internal\NullableType($inner_type);
}
