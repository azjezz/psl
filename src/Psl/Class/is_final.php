<?php

declare(strict_types=1);

namespace Psl\Class;

use ReflectionClass;

/**
 * Checks if class is final.
 *
 * @param class-string $class_name
 */
function is_final(string $class_name): bool
{
    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->isFinal();
}
