<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk
 * @template Tv
 *
 * @psalm-param Type<Tk> $key_type_spec
 * @psalm-param Type<Tv> $value_type_spec
 *
 * @psalm-return Type<iterable<Tk, Tv>>
 */
function iterable(Type $key_type_spec, Type $value_type_spec): Type
{
    return new Internal\IterableType($key_type_spec, $value_type_spec);
}
