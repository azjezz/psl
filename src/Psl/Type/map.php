<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @param TypeInterface<Tk> $key_type
 * @param TypeInterface<Tv> $value_type
 *
 * @return TypeInterface<Collection\MapInterface<Tk, Tv>>
 */
function map(TypeInterface $key_type, TypeInterface $value_type): TypeInterface
{
    return new Internal\MapType($key_type, $value_type);
}
