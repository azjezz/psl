<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<T> $innerType
 *
 * @return TypeInterface<T|null>
 *
 * @api
 */
function nullish<T = mixed>(TypeInterface<T> $innerType): TypeInterface<T|null>
{
    return new Internal\NullishType($innerType);
}
