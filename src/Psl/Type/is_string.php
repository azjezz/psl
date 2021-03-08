<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_string as php_is_string;

/**
 * Finds whether a variable is a string.
 *
 * @param mixed $var
 *
 * @psalm-assert-if-true string $var
 *
 * @psalm-pure
 *
 * @deprecated use `Type\string()->matches($value)` instead.
 */
function is_string($var): bool
{
    return php_is_string($var);
}
