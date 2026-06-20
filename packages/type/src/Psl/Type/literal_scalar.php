<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @api
 */
function literal_scalar<T: string|int|float|bool>(T $value): TypeInterface<T>
{
    return new Internal\LiteralScalarType::<T>($value);
}
