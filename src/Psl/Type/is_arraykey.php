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
 *
 * @deprecated use `Type\array_key()->matches($value)` instead.
 */
function is_arraykey($key): bool
{
    /**
     * @psalm-suppress ImpureFunctionCall
     * @psalm-suppress ImpureMethodCall
     */
    return array_key()->matches($key);
}
