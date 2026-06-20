<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param TypeInterface<Tk> $keyType
 * @param TypeInterface<Tv> $valueType
 *
 * @return TypeInterface<non-empty-array<Tk, Tv>>
 *
 * @api
 */
function non_empty_dict<Tk : int|string, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface
{
    return new Internal\NonEmptyDictType($keyType, $valueType);
}
