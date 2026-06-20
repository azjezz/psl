<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function nullable<T>(TypeInterface<T> $innerType): TypeInterface<T|null>
{
    return new Internal\NullableType::<T>($innerType);
}
