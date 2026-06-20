<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 *
 * @api
 */
function container<Tk : int|string, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface
{
    return new Internal\ContainerType($keyType, $valueType);
}
