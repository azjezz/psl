<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if class is abstract.
 *
 * @param class-string $className
 */
function is_abstract(string $className): bool
{
    return new ReflectionClass($className)->isAbstract();
}
