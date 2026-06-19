<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<array<Tk, Tv>>
 *
 * @api
 */
function dict<Tk : int|string = int|string, Tv = mixed>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface
{
    return new Internal\DictType($keyType, $valueType);
}
