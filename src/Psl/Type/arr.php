<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @psalm-param Type<Tk> $key_type_spec
 * @psalm-param Type<Tv> $value_type_spec
 *
 * @psalm-return Type<array<Tk, Tv>>
 */
function arr(Type $key_type_spec, Type $value_type_spec): Type
{
    return new Internal\ArrayType($key_type_spec, $value_type_spec);
}
