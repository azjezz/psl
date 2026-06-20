<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @api
 */
function set<T: string|int>(TypeInterface<T> $type): TypeInterface<Collection\SetInterface<T>>
{
    return new Internal\SetType::<T>($type);
}
