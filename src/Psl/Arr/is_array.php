<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Finds whether a variable is an array.
 *
 * @psalm-param mixed   $var
 *
 * @psalm-assert-if-true array<array-key,mixed> $var
 *
 * @psalm-pure
 */
function is_array($var): bool
{
    return \is_array($var);
}
