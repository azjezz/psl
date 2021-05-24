<?php

declare(strict_types=1);

namespace Psl\Class;

use function class_exists;

/**
 * Checks if the class with the given name exists.
 *
 * @param string $class_name
 *
 * @psalm-assert-if-true class-string $class_name
 */
function exists(string $class_name): bool
{
    /** @var bool */
    return class_exists($class_name, true);
}
