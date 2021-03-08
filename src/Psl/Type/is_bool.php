<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_bool as php_is_bool;

/**
 * Finds whether a variable is a boolean.
 *
 * @param mixed $var
 *
 * @psalm-assert-if-true bool $var
 *
 * @psalm-pure
 *
 * @deprecated use `Type\bool()->matches($value)` instead.
 */
function is_bool($var): bool
{
    return php_is_bool($var);
}
