<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @template T
 *
 * @param class-string<T> $classname
 *
 * @return TypeInterface<T>
 */
function instance_of(string $classname): TypeInterface
{
    return new Internal\InstanceOfType($classname);
}
