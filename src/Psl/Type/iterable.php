<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template Tk
 * @template Tv
 *
 * @param TypeInterface<Tk> $key_type
 * @param TypeInterface<Tv> $value_type
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 */
function iterable(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\IterableType($key_type, $value_type);
}
