<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int<-128, 127>>
 */
function i8(): TypeInterface
{
    static $instance = new Internal\I8Type();

    return $instance;
}
