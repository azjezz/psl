<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return TypeInterface<u32>
 *
 * @return TypeInterface<int<0, 4294967295>>
 */
function u32(): TypeInterface
{
    /** @var Internal\U32Type $instance */
    static $instance = new Internal\U32Type();

    return $instance;
}
