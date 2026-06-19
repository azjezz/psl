<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @param TypeInterface<T> $type
 *
 * @return TypeInterface<Collection\MutableSetInterface<T>>
 *
 * @api
 */
function mutable_set<T : int|string = int|string>(TypeInterface<T> $type): TypeInterface<Collection\MutableSetInterface<T>>
{
    return new Internal\MutableSetType($type);
}
