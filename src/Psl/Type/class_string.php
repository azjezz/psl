<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @template T
 *
 * @param class-string<T> $classname
 *
 * @return TypeInterface<class-string<T>>
 */
function class_string(string $classname): TypeInterface
{
    return new Internal\ClassStringType($classname);
}
