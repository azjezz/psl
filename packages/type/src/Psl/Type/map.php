<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @api
 */
function map<Tk: string|int, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<Collection\MapInterface<Tk, Tv>>
{
    return new Internal\MapType::<Tk, Tv>($keyType, $valueType);
}
