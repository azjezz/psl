<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if class is read only.
 *
 * @param class-string $class_name
 */
function is_readonly(string $class_name): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->isReadOnly();
}
