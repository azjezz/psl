<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param class-string<T>|null $classname
 *
 * @return ($classname is null ? TypeInterface<class-string> : TypeInterface<class-string<T>>)
 *
 * @api
 */
function class_string<T>(string|null $classname = null): TypeInterface<string>
{
    return new Internal\ClassStringType::<T>($classname);
}
