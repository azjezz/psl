<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_a;

/**
 * Checks if the object is of this class or has this class as one of its parents.
 *
 * @template T
 *
 * @param class-string<T> $class
 *
 * @psalm-assert-if-true T $object
 *
 * @pure
 *
 * @deprecated use `Type\object($class)->matches($object)` instead.
 */
function is_instanceof(object $object, string $class): bool
{
    return is_a($object, $class, false);
}
