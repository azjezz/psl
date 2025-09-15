<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int<0, 65535>>
 */
function u16(): TypeInterface
{
    /** @var Internal\U16Type $instance */
    static $instance = new Internal\U16Type();

    return $instance;
}
