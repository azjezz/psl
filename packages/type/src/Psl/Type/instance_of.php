<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @param class-string<T> $classname
 *
 * @return TypeInterface<T>
 *
 * @api
 */
function instance_of<T>(string $classname): TypeInterface<T>
{
    return new Internal\InstanceOfType($classname);
}
