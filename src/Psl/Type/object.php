<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<object>
 */
function object(): TypeInterface
{
    /** @var Internal\ObjectType $instance */
    static $instance = new Internal\ObjectType();

    return $instance;
}
