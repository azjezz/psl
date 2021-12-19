<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if method is defined in the given class.
 *
 * @param class-string $class_name
 */
function has_method(string $class_name, string $method_name): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->hasMethod($method_name);
}
