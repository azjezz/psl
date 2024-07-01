<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Collection;

/**
 * @psalm-pure
 *
 * @template T of array-key
 *
 * @param TypeInterface<T> $type
 *
 * @return TypeInterface<Collection\SetInterface<T>>
 */
function set(TypeInterface $type): TypeInterface
{
    return new Internal\SetType($type);
}
