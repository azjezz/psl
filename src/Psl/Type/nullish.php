<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template T
 *
 * @param TypeInterface<T> $inner_type
 *
 * @return TypeInterface<T|null>
 */
function nullish(TypeInterface $inner_type): TypeInterface
{
    return new Internal\NullishType($inner_type);
}
