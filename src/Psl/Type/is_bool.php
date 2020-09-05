<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_bool as php_is_bool;

/**
 * Finds whether a variable is a boolean.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true bool $var
 *
 * @psalm-pure
 */
function is_bool($var): bool
{
    return php_is_bool($var);
}
