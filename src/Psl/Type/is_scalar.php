<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is a scalar.
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true scalar $var
 *
 * @psalm-pure
 */
function is_scalar($var): bool
{
    return \is_scalar($var);
}
