<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_callable as php_is_callable;

/**
 * Finds whether a variable is a callable.
 *
 * @psalm-assert-if-true callable $var
 *
 * @pure
 */
function is_callable(mixed $var): bool
{
    return php_is_callable($var, false);
}
