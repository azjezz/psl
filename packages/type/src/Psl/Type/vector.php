<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @param TypeInterface<T> $valueType
 *
 * @return TypeInterface<Collection\VectorInterface<T>>
 *
 * @api
 */
function vector<T = mixed>(TypeInterface<T> $valueType): TypeInterface<Collection\VectorInterface<T>>
{
    return new Internal\VectorType($valueType);
}
