<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param T $value
 *
 * @return TypeInterface<T>
 *
 * @api
 */
function literal_scalar<T : string|int|float|bool = string|int|float|bool>(string|int|float|bool $value): TypeInterface<T>
{
    return new Internal\LiteralScalarType($value);
}
