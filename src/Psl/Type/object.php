<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @psalm-param class-string<T> $classname
 *
 * @psalm-return Type<T>
 */
function object(string $classname): Type
{
    return new Internal\ObjectType($classname);
}
