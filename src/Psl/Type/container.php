<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template Tk as array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 */
function container(TypeInterface $keyType, TypeInterface $valueType): TypeInterface
{
    return new Internal\ContainerType($keyType, $valueType);
}
