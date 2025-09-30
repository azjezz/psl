<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<array-key>
 */
function array_key(): TypeInterface
{
    static $instance = new Internal\ArrayKeyType();

    return $instance;
}
