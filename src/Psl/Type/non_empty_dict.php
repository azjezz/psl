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
 * @return TypeInterface<non-empty-array<Tk, Tv>>
 */
function non_empty_dict(TypeInterface $keyType, TypeInterface $valueType): TypeInterface
{
    return new Internal\NonEmptyDictType($keyType, $valueType);
}
