<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function optional<T>(TypeInterface<T> $innerType): TypeInterface<T>
{
    return new Internal\OptionalType::<T>($innerType);
}
