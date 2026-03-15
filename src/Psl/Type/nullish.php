<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template T
 *
 * @param TypeInterface<T> $innerType
 *
 * @return TypeInterface<T|null>
 */
function nullish(TypeInterface $innerType): TypeInterface
{
    return new Internal\NullishType($innerType);
}
