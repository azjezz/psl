<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @api
 */
function vector<T>(TypeInterface<T> $valueType): TypeInterface<Collection\VectorInterface<T>>
{
    return new Internal\VectorType::<T>($valueType);
}
