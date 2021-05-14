<?php

declare(strict_types=1);

namespace Psl\Class;

use Psl;
use ReflectionClass;

/**
 * Checks if class is final.
 *
 * @param class-string $class_name
 *
 * @throws Psl\Exception\InvariantViolationException If $class_name does not exist.
 */
function is_final(string $class_name): bool
{
    Psl\invariant(namespace\exists($class_name), 'Classname "%s" does not exist.', $class_name);

    /** @psalm-suppress MissingThrowsDocblock */
    return (new ReflectionClass($class_name))->isFinal();
}
