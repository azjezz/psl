<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if method is defined in the given class.
 *
 * @param class-string $className
 */
function has_method(string $className, string $methodName): bool
{
    return new ReflectionClass($className)->hasMethod($methodName);
}
