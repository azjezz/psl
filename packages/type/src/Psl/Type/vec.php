<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<list<T>>
 *
 * @api
 */
function vec<T>(TypeInterface<T> $valueType): TypeInterface<array>
{
    return new Internal\VecType::<T>($valueType);
}
