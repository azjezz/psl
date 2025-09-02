<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<int|float>
 */
function num(): TypeInterface
{
    /** @var Internal\NumType $instance */
    static $instance = new Internal\NumType();

    return $instance;
}
