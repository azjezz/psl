<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @template T
 *
 * @param class-string<T>|null $classname
 *
 * @return ($classname is null ? TypeInterface<class-string> : TypeInterface<class-string<T>>)
 *
 * @api
 */
function class_string(string|null $classname = null): TypeInterface
{
    return new Internal\ClassStringType($classname);
}
