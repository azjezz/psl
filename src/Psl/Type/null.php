<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<null>
 */
function null(): TypeInterface
{
    static $instance = new Internal\NullType();

    return $instance;
}
