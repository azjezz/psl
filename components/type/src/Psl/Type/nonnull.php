<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return NonNullType
 */
function nonnull(): NonNullType
{
    static $instance = new NonNullType();

    return $instance;
}
