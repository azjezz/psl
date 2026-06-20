<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<non-empty-list<T>>
 *
 * @api
 */
function non_empty_vec<T>(TypeInterface<T> $valueType): TypeInterface<array>
{
    return new Internal\NonEmptyVecType::<T>($valueType);
}
