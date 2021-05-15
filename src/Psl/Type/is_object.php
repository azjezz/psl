<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_object as php_is_object;

/**
 * Finds whether a variable is an object.
 *
 * @psalm-assert-if-true object $var
 *
 * @pure
 *
 * @deprecated use `Type\object($classname)->matches($var)` instead.
 */
function is_object(mixed $var): bool
{
    return php_is_object($var);
}
