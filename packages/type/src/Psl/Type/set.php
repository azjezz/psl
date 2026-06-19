<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @param TypeInterface<T> $type
 *
 * @return TypeInterface<Collection\SetInterface<T>>
 *
 * @api
 */
function set<T : int|string = int|string>(TypeInterface<T> $type): TypeInterface<Collection\SetInterface<T>>
{
    return new Internal\SetType($type);
}
