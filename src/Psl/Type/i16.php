<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int<-32768, 32767>>
 */
function i16(): TypeInterface
{
    static $instance = new Internal\I16Type();

    return $instance;
}
