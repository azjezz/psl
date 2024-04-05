<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return TypeInterface<f64>
 *
 * @return TypeInterface<float>
 */
function f64(): TypeInterface
{
    /** @var Internal\F64Type $instance */
    static $instance = new Internal\F64Type();

    return $instance;
}
