<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is NaN ( not a number ).
 *
 * @psalm-param mixed $var
 *
 * @psalm-assert-if-true float(NAN) $var
 *
 * @psalm-pure
 */
function is_nan($var): bool
{
    return \is_nan($var);
}
