<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_array as php_is_array;

/**
 * Finds whether a variable is an array.
 *
 * @psalm-assert-if-true array<array-key,mixed> $var
 *
 * @pure
 *
 * @deprecated use `Type\dict($kt, $vt)->matches($value)` instead.
 */
function is_array(mixed $var): bool
{
    return php_is_array($var);
}
