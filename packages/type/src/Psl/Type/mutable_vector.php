<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @param TypeInterface<T> $valueType
 *
 * @return TypeInterface<Collection\MutableVectorInterface<T>>
 *
 * @api
 */
function mutable_vector<T>(TypeInterface<T> $valueType): TypeInterface<Collection\MutableVectorInterface<T>>
{
    return new Internal\MutableVectorType($valueType);
}
