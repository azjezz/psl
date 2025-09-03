<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int>
 */
function int(): TypeInterface
{
    /** @var Internal\IntType $instance */
    static $instance = new Internal\IntType();

    return $instance;
}
