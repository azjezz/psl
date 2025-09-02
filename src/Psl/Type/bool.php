<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<bool>
 */
function bool(): TypeInterface
{
    /** @var Internal\BoolType $instance */
    static $instance = new Internal\BoolType();

    return $instance;
}
