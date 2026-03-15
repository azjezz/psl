<?php

declare(strict_types=1);

namespace Psl\Class;

use function class_exists;

/**
 * Checks if the class with the given name has already been defined.
 *
 * @param string $className
 *
 * @psalm-assert-if-true class-string $className
 *
 * @pure
 */
function defined(string $className): bool
{
    return class_exists($className, false);
}
