<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<positive-int>
 */
function positive_int(): TypeInterface
{
    /** @var Internal\PositiveIntType $instance */
    static $instance = new Internal\PositiveIntType();

    return $instance;
}
