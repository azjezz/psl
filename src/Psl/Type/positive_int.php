<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @return TypeInterface<positive-int>
 *
 * @ara-return TypeInterface<0|uint>
 */
function positive_int(): TypeInterface
{
    /** @var Internal\PositiveIntType $instance */
    static $instance = new Internal\PositiveIntType();

    return $instance;
}
