<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_float as php_is_float;

/**
 * Finds whether a variable is a float.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true float $var
 *
 * @psalm-pure
 */
function is_float($var): bool
{
    return php_is_float($var);
}
