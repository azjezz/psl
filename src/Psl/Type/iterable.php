<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;

/**
 * @template Tk
 * @template Tv
 *
 * @param TypeInterface<Tk> $key_type
 * @param TypeInterface<Tv> $value_type
 *
 * @throws Psl\Exception\InvariantViolationException If $key_value, or $value_type is optional.
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 */
function iterable(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\IterableType($key_type, $value_type);
}
