<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if class is read only.
 *
 * @param class-string $className
 */
function is_readonly(string $className): bool
{
    return new ReflectionClass($className)->isReadOnly();
}
