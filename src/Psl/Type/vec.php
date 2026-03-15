<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template T
 *
 * @param TypeInterface<T> $valueType
 *
 * @return TypeInterface<list<T>>
 */
function vec(TypeInterface $valueType): TypeInterface
{
    return new Internal\VecType($valueType);
}
