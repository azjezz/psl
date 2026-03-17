<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Create a new type that always asserts that the value matches the provided type,
 * even when coercing.
 *
 * @pure
 *
 * @template T
 *
 * @param TypeInterface<T> $type
 *
 * @return TypeInterface<T>
 */
function always_assert(TypeInterface $type): TypeInterface
{
    return new Internal\AlwaysAssertType($type);
}
