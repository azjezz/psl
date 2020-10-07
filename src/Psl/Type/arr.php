<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @psalm-param Type<Tk> $key_type
 * @psalm-param Type<Tv> $value_type
 *
 * @psalm-return Type<array<Tk, Tv>>
 */
function arr(Type $key_type, Type $value_type): Type
{
    return new Internal\ArrayType($key_type, $value_type);
}
