<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is an array key.
 *
 * @psalm-param mixed $key
 *
 * @psalm-assert-if-true array-key $key
 *
 * @psalm-pure
 */
function is_arraykey($key): bool
{
    return namespace\is_string($key) || namespace\is_int($key);
}
