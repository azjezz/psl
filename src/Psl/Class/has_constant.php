<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if constant is defined in the given class.
 *
 * @param class-string $class_name
 */
function has_constant(string $class_name, string $constant_name): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->hasConstant($constant_name);
}
