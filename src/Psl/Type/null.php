<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<null>
 */
function null(): TypeInterface
{
    /** @var Internal\NullType $instance */
    static $instance = new Internal\NullType();

    return $instance;
}
