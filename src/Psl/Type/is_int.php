<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is an integer.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true int $var
 *
 * @psalm-pure
 */
function is_int($var): bool
{
    return \is_int($var);
}
