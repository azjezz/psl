<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk
 * @template Tv
 *
 * @psalm-param TypeInterface<Tk> $key_type_spec
 * @psalm-param TypeInterface<Tv> $value_type_spec
 *
 * @psalm-return TypeInterface<iterable<Tk, Tv>>
 */
function iterable(TypeInterface $key_type_spec, TypeInterface $value_type_spec): TypeInterface
{
    return new Internal\IterableType($key_type_spec, $value_type_spec);
}
