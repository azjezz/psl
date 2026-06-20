<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<Collection\MutableMapInterface<Tk, Tv>>
 *
 * @api
 */
function mutable_map<Tk : int|string, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<Collection\MutableMapInterface<Tk, Tv>>
{
    return new Internal\MutableMapType($keyType, $valueType);
}
