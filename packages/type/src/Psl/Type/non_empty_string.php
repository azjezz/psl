<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<non-empty-string>
 */
function non_empty_string(): TypeInterface
{
    static $instance = new Internal\NonEmptyStringType();

    return $instance;
}
