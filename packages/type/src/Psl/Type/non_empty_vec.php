<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<T> $valueType
 *
 * @return TypeInterface<non-empty-list<T>>
 *
 * @api
 */
function non_empty_vec<T = mixed>(TypeInterface<T> $valueType): TypeInterface
{
    return new Internal\NonEmptyVecType($valueType);
}
