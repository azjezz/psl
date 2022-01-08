<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $key_type
 * @param TypeInterface<Tv> $value_type
 *
 * @return TypeInterface<array<Tk, Tv>>
 */
function dict(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\DictType($key_type, $value_type);
}
