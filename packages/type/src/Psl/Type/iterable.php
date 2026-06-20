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
function iterable<Tk, Tv>(TypeInterface<Tk> $keyType, TypeInterface<Tv> $valueType): TypeInterface<iterable>
{
    return new Internal\IterableType::<Tk, Tv>($keyType, $valueType);
}
