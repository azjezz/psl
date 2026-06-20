<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function nullish<T>(TypeInterface<T> $innerType): TypeInterface<T|null>
{
    return new Internal\NullishType::<T>($innerType);
}
