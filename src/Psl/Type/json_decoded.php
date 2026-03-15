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
 * @return TypeInterface<T>
 */
function json_decoded(TypeInterface $innerType): TypeInterface
{
    return new Internal\JsonDecodedType($innerType);
}
