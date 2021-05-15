<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T of string|int|float|bool
 *
 * @param T $value
 *
 * @return TypeInterface<T>
 */
function literal_scalar(string|int|float|bool $value): TypeInterface
{
    return new Internal\LiteralScalarType($value);
}
