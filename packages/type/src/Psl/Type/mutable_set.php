<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @api
 */
function mutable_set<T: string|int>(TypeInterface<T> $type): TypeInterface<Collection\MutableSetInterface<T>>
{
    return new Internal\MutableSetType::<T>($type);
}
