<?php

declare(strict_types=1);

namespace Psl\Class;

use Psl;
use ReflectionClass;

/**
 * Checks if method is defined in the given class.
 *
 * @param class-string $class_name
 *
 * @throws Psl\Exception\InvariantViolationException If $class_name does not exist.
 */
function has_method(string $class_name, string $method_name): bool
{
    Psl\invariant(namespace\exists($class_name), 'Classname "%s" does not exist.', $class_name);

    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->hasMethod($method_name);
}
