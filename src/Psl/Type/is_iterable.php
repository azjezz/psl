<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_iterable as php_is_iterable;

/**
 * Finds whether a variable is an iterable.
 *
 * @psalm-assert-if-true iterable $var
 *
 * @pure
 *
 * @deprecated use `Type\iterable($kt, $vt)->matches($value)` instead.
 */
function is_iterable(mixed $var): bool
{
    return php_is_iterable($var);
}
