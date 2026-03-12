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
 * @return TypeInterface<T>
 */
function json_decoded(TypeInterface $inner_type): TypeInterface
{
    return new Internal\JsonDecodedType($inner_type);
}
