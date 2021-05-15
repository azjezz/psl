<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;

/**
 * @template T of string|int|float|bool
 *
 * @param T $value
 *
 * @throws Psl\Type\Exception\AssertException If $value is not scalar.
 *
 * @return TypeInterface<T>
 */
function literal_scalar(string|int|float|bool $value): TypeInterface
{
    /** @psalm-suppress MissingThrowsDocblock */
    union(string(), bool(), num())->assert($value);

    return new Internal\LiteralScalarType($value);
}
