<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_object as php_is_object;

/**
 * Finds whether a variable is an object.
 *
 * @param mixed $var
 *
 * @psalm-assert-if-true object $var
 *
 * @psalm-pure
 */
function is_object($var): bool
{
    return php_is_object($var);
}
