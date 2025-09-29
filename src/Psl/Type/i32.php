<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int<-2147483648, 2147483647>>
 */
function i32(): TypeInterface
{
    static $instance = new Internal\I32Type();

    return $instance;
}
