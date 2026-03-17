<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<array<Tk, Tv>>
 */
function dict(TypeInterface $keyType, TypeInterface $valueType): TypeInterface
{
    return new Internal\DictType($keyType, $valueType);
}
