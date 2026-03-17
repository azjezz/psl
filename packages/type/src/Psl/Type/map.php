<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @pure
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<Collection\MapInterface<Tk, Tv>>
 */
function map(TypeInterface $keyType, TypeInterface $valueType): TypeInterface
{
    return new Internal\MapType($keyType, $valueType);
}
