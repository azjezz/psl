<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<numeric-string>
 */
function numeric_string(): TypeInterface
{
    /** @var Internal\NumericStringType $instance */
    static $instance = new Internal\NumericStringType();

    return $instance;
}
