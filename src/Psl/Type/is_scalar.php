<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_scalar as php_is_scalar;

/**
 * Finds whether a variable is a scalar.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true scalar $var
 *
 * @psalm-pure
 *
 * @deprecated use `Type\scalar()->matches($value)` instead.
 */
function is_scalar($var): bool
{
    return php_is_scalar($var);
}
