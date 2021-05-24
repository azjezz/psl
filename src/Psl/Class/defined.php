<?php

declare(strict_types=1);

namespace Psl\Class;

use function class_exists;

/**
 * Checks if the class with the given name has already been defined.
 *
 * @param string $class_name
 *
 * @psalm-assert-if-true class-string $class_name
 *
 * @pure
 */
function defined(string $class_name): bool
{
    /**
     * @psalm-suppress ImpureFunctionCall - call is pure.
     *
     * @var bool
     */
    return class_exists($class_name, false);
}
