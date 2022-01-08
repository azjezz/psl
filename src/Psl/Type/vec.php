<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @param TypeInterface<T> $value_type
 *
 * @return TypeInterface<list<T>>
 */
function vec(TypeInterface $value_type): TypeInterface
{
    return new Internal\VecType($value_type);
}
