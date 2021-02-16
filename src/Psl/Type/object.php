<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @psalm-param class-string<T> $classname
 *
 * @psalm-return TypeInterface<T>
 */
function object(string $classname): TypeInterface
{
    return new Internal\ObjectType($classname);
}
