<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return TypeInterface<i8>
 *
 * @return TypeInterface<int<-128, 127>>
 */
function i8(): TypeInterface
{
    /** @var Internal\I8Type $instance */
    static $instance = new Internal\I8Type();

    return $instance;
}
