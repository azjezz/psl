<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<T> $valueType
 *
 * @return TypeInterface<list<T>>
 *
 * @api
 */
function vec<T>(TypeInterface<T> $valueType): TypeInterface
{
    return new Internal\VecType($valueType);
}
