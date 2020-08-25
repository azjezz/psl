<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Str;

/**
 * Finds whether a variable is an array key.
 *
 * @psalm-param mixed   $key
 *
 * @psalm-assert-if-true array-key $key
 *
 * @psalm-pure
 */
function is_arraykey($key): bool
{
    return Str\is_string($key) || is_int($key);
}
