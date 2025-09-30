<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int>
 */
function i64(): TypeInterface
{
    static $instance = new Internal\I64Type();

    return $instance;
}
