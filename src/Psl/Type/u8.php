<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int<0, 255>>
 */
function u8(): TypeInterface
{
    static $instance = new Internal\U8Type();

    return $instance;
}
