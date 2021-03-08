<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_callable as php_is_callable;

/**
 * Finds whether a variable is a callable.
 *
 * @param mixed $var
 *
 * @psalm-assert-if-true callable $var
 *
 * @psalm-pure
 */
function is_callable($var): bool
{
    return php_is_callable($var, false);
}
