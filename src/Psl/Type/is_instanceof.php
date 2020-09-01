<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Checks if the object is of this class or has this class as one of its parents.
 *
 * @psalm-template T
 *
 * @psalm-param class-string<T> $class
 *
 * @psalm-assert-if-true T $object
 *
 * @psalm-pure
 */
function is_instanceof(object $object, string $class): bool
{
    return \is_a($object, $class, false);
}
