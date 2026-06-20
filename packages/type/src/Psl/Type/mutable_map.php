<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @api
 */
function mutable_map<Tk: string|int, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<Collection\MutableMapInterface<Tk, Tv>>
{
    return new Internal\MutableMapType::<Tk, Tv>($keyType, $valueType);
}
