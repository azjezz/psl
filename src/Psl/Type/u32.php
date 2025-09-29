<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int<0, 4294967295>>
 */
function u32(): TypeInterface
{
    static $instance = new Internal\U32Type();

    return $instance;
}
