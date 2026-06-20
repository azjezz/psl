<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 *
 * @api
 */
function container<Tk: string|int, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<iterable>
{
    return new Internal\ContainerType::<Tk, Tv>($keyType, $valueType);
}
