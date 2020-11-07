<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @template Tk of array-key
 * @template Tv
 *
 * @psalm-param Type<Tk> $key_type
 * @psalm-param Type<Tv> $value_type
 *
 * @psalm-return Type<Collection\MapInterface<Tk, Tv>>
 */
function map(Type $key_type, Type $value_type): Type
{
    return new Internal\MapType($key_type, $value_type);
}
