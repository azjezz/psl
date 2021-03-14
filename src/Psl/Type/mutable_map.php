<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl;
use Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $key_type
 * @param TypeInterface<Tv> $value_type
 *
 * @throws Psl\Exception\InvariantViolationException If $key_value, or $value_type is optional.
 *
 * @return TypeInterface<Collection\MutableMapInterface<Tk, Tv>>
 */
function mutable_map(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\MutableMapType($key_type, $value_type);
}
