<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @api
 */
function mutable_vector<T>(TypeInterface<T> $valueType): TypeInterface<Collection\MutableVectorInterface<T>>
{
    return new Internal\MutableVectorType::<T>($valueType);
}
