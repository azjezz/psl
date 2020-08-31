<?php

declare(strict_types=1);

namespace Psl\Type;

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
    return \is_numeric($var);
}
