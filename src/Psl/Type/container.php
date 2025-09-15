<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template Tk as array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $key_type
 * @param TypeInterface<Tv> $value_type
 *
 * @return TypeInterface<iterable<Tk, Tv>>
 */
function container(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\ContainerType($key_type, $value_type);
}
