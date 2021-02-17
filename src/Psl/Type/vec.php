<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-template T
 *
 * @psalm-param TypeInterface<T> $value_type
 *
 * @psalm-return TypeInterface<list<T>>
 */
function vec(TypeInterface $value_type): TypeInterface
{
    return new Internal\VecType($value_type);
}
