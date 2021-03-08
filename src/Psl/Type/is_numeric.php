<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_numeric as php_is_numeric;

/**
 * Finds whether a variable is numeric.
 *
 * @param mixed $var
 *
 * @psalm-assert-if-true numeric $var
 *
 * @psalm-pure
 *
 * @deprecated use `Type\num()->matches($value)` instead.
 */
function is_numeric($var): bool
{
    return php_is_numeric($var);
}
