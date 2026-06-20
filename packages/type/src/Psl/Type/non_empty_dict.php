<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<non-empty-array<Tk, Tv>>
 *
 * @api
 */
function non_empty_dict<Tk: string|int, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<array>
{
    return new Internal\NonEmptyDictType::<Tk, Tv>($keyType, $valueType);
}
