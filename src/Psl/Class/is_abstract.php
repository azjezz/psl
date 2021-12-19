<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if class is abstract.
 *
 * @param class-string $class_name
 */
function is_abstract(string $class_name): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->isAbstract();
}
