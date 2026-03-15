<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template Tk
 * @template Tv
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 */
function iterable(TypeInterface $keyType, TypeInterface $valueType): TypeInterface
{
    return new Internal\IterableType($keyType, $valueType);
}
