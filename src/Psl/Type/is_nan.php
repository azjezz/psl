<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a float is NaN ( not a number ).
 *
 * @psalm-param float $var
 *
 * @psalm-pure
 */
function is_nan(float $var): bool
{
    return \is_nan($var);
}
