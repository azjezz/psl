<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return TypeInterface<u8>
 *
 * @return TypeInterface<int<0, 255>>
 */
function u8(): TypeInterface
{
    /** @var Internal\U8Type $instance */
    static $instance = new Internal\U8Type();

    return $instance;
}
