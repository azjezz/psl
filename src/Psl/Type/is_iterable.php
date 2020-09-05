<?php

declare(strict_types=1);

namespace Psl\Type;

use function is_iterable as php_is_iterable;

/**
 * Finds whether a variable is an iterable.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true iterable $var
 *
 * @psalm-pure
 */
function is_iterable($var): bool
{
    return php_is_iterable($var);
}
