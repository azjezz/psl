<?php

declare(strict_types=1);

namespace Psl\Type;

use Psl\Type\Internal\NonNullType;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return TypeInterface<nonnull>
 *
 * @return NonNullType
 */
function nonnull(): TypeInterface
{
    /** @var Internal\NonNullType $instance */
    static $instance = new Internal\NonNullType();

    return $instance;
}
