<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @mago-expect analysis:impure-static-variable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<object>
 */
function object(): TypeInterface
{
    /** @var Internal\ObjectType $instance */
    static $instance = new Internal\ObjectType();

    return $instance;
}
