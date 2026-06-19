<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<T> $innerType
 *
 * @return TypeInterface<T>
 *
 * @api
 */
function optional<T = mixed>(TypeInterface<T> $innerType): TypeInterface<T>
{
    return new Internal\OptionalType($innerType);
}
