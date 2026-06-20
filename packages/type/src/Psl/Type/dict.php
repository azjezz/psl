<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<array<Tk, Tv>>
 *
 * @api
 */
function dict<Tk: string|int, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<array>
{
    return new Internal\DictType::<Tk, Tv>($keyType, $valueType);
}
