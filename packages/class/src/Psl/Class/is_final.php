<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if class is final.
 *
 * @param class-string $className
 */
function is_final(string $className): bool
{
    return new ReflectionClass($className)->isFinal();
}
