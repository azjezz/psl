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
 * @return TypeInterface<non-empty-list<T>>
 */
function non_empty_vec(TypeInterface $valueType): TypeInterface
{
    return new Internal\NonEmptyVecType($valueType);
}
