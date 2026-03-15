<?php

declare(strict_types=1);

namespace Psl\Class;

use function class_exists;

/**
 * Checks if the class with the given name exists.
 *
 * @param string $className
 *
 * @psalm-assert-if-true =class-string $className
 */
function exists(string $className): bool
{
    return class_exists($className, true);
}
