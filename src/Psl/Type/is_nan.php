<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_nan as php_is_nan;

/**
 * Finds whether a float is NaN ( not a number ).
 *
 * @pure
 */
function is_nan(float $var): bool
{
    return php_is_nan($var);
}
