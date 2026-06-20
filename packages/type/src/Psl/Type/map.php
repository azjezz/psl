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
 * @return TypeInterface<Collection\MapInterface<Tk, Tv>>
 *
 * @api
 */
function map<Tk : int|string, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<Collection\MapInterface<Tk, Tv>>
{
    return new Internal\MapType($keyType, $valueType);
}
