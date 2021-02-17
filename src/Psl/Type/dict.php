<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param TypeInterface<Tk> $key_type
 * @psalm-param TypeInterface<Tv> $value_type
 *
 * @psalm-return TypeInterface<array<Tk, Tv>>
 */
function dict(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\DictType($key_type, $value_type);
}
