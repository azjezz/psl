<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_string as php_is_string;

/**
 * Finds whether a variable is a string.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true string $var
 *
 * @psalm-pure
 */
function is_string($var): bool
{
    return php_is_string($var);
}
