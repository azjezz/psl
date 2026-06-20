<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function json_decoded<T>(TypeInterface<T> $innerType): TypeInterface<T>
{
    return new Internal\JsonDecodedType::<T>($innerType);
}
