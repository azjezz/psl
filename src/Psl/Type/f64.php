<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<float>
 */
function f64(): TypeInterface
{
    /** @var Internal\F64Type $instance */
    static $instance = new Internal\F64Type();

    return $instance;
}
