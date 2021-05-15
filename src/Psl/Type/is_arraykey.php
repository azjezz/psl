<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * Finds whether a variable is an array key.
 *
 * @psalm-assert-if-true array-key $key
 *
 * @pure
 *
 * @deprecated use `Type\array_key()->matches($value)` instead.
 */
function is_arraykey(mixed $key): bool
{
    /**
     * @psalm-suppress ImpureFunctionCall
     * @psalm-suppress ImpureMethodCall
     */
    return array_key()->matches($key);
}
