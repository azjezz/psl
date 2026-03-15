<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if constant is defined in the given class.
 *
 * @param class-string $className
 */
function has_constant(string $className, string $constantName): bool
{
    return new ReflectionClass($className)->hasConstant($constantName);
}
