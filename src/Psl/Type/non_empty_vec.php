<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @param TypeInterface<T> $value_type
 *
 * @return TypeInterface<non-empty-list<T>>
 */
function non_empty_vec(TypeInterface $value_type): TypeInterface
{
    return new Internal\NonEmptyVecType($value_type);
}
