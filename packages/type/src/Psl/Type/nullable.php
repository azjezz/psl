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
 *
 * @api
 */
function nullable(TypeInterface $innerType): TypeInterface
{
    return new Internal\NullableType($innerType);
}
