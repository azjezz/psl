<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @param TypeInterface<T> $inner_type
 *
 * @return TypeInterface<T>
 */
function optional(TypeInterface $inner_type): TypeInterface
{
    return new Internal\OptionalType($inner_type);
}
