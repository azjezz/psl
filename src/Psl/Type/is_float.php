<?php

declare(strict_types=1);

namespace Psl\Type;

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
    return \is_float($var);
}
