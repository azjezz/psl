<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return TypeInterface<uint>
 *
 * @return TypeInterface<int<0, max>>
 */
function uint(): TypeInterface
{
    /** @var Internal\UIntType $instance */
    static $instance = new Internal\UIntType();

    return $instance;
}
