<?php

declare(strict_types=1);

namespace Psl\Type;

/**
 * @psalm-pure
 *
 * @psalm-suppress ImpureStaticVariable - The $instance is always the same and is considered pure.
 *
 * @ara-return NonNullType
 *
 * @return NonNullType
 */
function nonnull(): NonNullType
{
    /** @var NonNullType $instance */
    static $instance = new NonNullType();

    return $instance;
}
