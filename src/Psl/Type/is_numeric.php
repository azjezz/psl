<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_numeric as php_is_numeric;

/**
 * Finds whether a variable is numeric.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true numeric $var
 *
 * @psalm-pure
 */
function is_numeric($var): bool
{
    return php_is_numeric($var);
}
